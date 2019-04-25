<?php
defined( 'ABSPATH' ) || exit;
class UPS{
  private $api;
  private $username;
  private $password;
  private $account;
  
  public $access_request;
  public $api_path;
  public $end_of_day;
  public $from = array();
  public $service_code_mappings = array(
    '1DM' => 14,
    '1DA' => 01,
    '1DP' => 13,
    'GND' => 03,
  );
// TODO: use base address instead. unless of course we are going to include dropship logic
// https://woocommerce.wp-a2z.org/oik_api/wc_countriesget_base_address/
  public function __construct(){
    $this->api = get_option( 'ups_api_key' );
    $this->username = get_option( 'ups_api_username' );
    $this->password = get_option( 'ups_api_password' );
    $this->account = get_option( 'ups_api_account' );
    $this->api_path = get_option( 'ups_api_mode' );
    $this->end_of_day = get_option( 'ups_api_end_of_day' ) - 1;
    $this->access_request = $this->get_access_request();
    $this->from = $this->get_base_shop_address();
  }
  public function get_base_shop_address(){
    $address = wc_get_base_location();
    $address['address_1'] = get_option( 'woocommerce_store_address', '' );
    $address['address_2'] = get_option( 'woocommerce_store_address_2', '' );
    $address['city'] = get_option( 'woocommerce_store_city', '' );
    $address['postal'] = get_option( 'woocommerce_store_postcode', '' );
    return $address;
  }

  public function create_xml( $xml, $array, $key = '', $parent_key = '' ){

    if ( ! is_array( $array ) ) {
      return $this->create_xml_child_node( $xml, $key, $array, $parent_key );
    } else {
      if( ! empty( $parent_key ) ){
        $key = $key .', '. $parent_key;
      }
    }
    
    foreach($array as $arr_key => $arr) {
      $this->create_xml($xml, $arr, $arr_key, $key);
    }
      
    return $xml;
  }

  public function create_xml_child_node( $xml, $key, $value, $parent_key = '' ){
    $depth = array_reverse( explode( ', ', $parent_key ) );
    foreach( $depth as $path ){
      if( ! isset( $xml->$path ) ){
        $xml->addChild( $path );
      }

      $xml = $xml->$path;
      $xml->addChild( $key, $value );
    }
    echo '<br>';
  }

  public function create_request_xml( $wrapper, $reference, $action, $option = '' ){
    $xml = new SimpleXMLElement("<$wrapper></$wrapper>");
    $xml = $this->create_xml( $xml, array(
      'Request' => array(
        'TransactionReference' => $reference,
        'RequestAction' => $action
      )
    ) );

    if( ! empty( $option ) ){
      $xml->Request->addChild( 'RequestOption', $option );
    }

    return $xml;
  }

  public function create_xml_with_wrapper( $wrapper, $args ){
    $xml = new SimpleXMLElement("<$wrapper></$wrapper>");
    $xml = $this->create_xml( $xml, $args );
    return $xml;
  }

  public function time_in_transit( $ship_to ){
    $time_in_transit_request = $this->create_request_xml( 'TimeInTransitRequest', 'Reference string', 'TimeInTransit' );

    $from = $this->create_xml_with_wrapper( 'TransitFrom', array(
      'AddressArtifactFormat' => array(
        'StreetName' => $this->from['address_1'],
        'PostcodePrimaryLow' => $this->from['postal'],
        'CountryCode' => $this->from['country'],
      ) )
    );

    $to = $this->create_xml_with_wrapper( 'TransitTo', array(
      'AddressArtifactFormat' => array(
        'StreetName' => $ship_to['street'],
        'PostcodePrimaryLow' => $ship_to['postal'],
        'CountryCode' => $ship_to['country'],
      )
    ) );

    $this->append( $time_in_transit_request, $from );
    $this->append( $time_in_transit_request, $to );

    $time_in_transit_request->addChild( 'PickupDate', $this->get_pickup_date() );
    $requestXML = $this->access_request->asXML() . $time_in_transit_request->asXML();
    $response = $this->send( $this->api_path . 'TimeInTransit', $requestXML );
    return $response;
  }


  public function track ( $tracking ){
    $track_request = new SimpleXMLElement( '<TrackRequest></TrackRequest>' );
    $track_request->addChild( 'Request' );
    $track_request->Request->addChild( 'TransactionReference' );
    $track_request->Request->TransactionReference->addChild( 'CustomerContext', 'Tracking Update Cron Job' );
    $track_request->Request->addChild( 'RequestAction', 'Track' );
    $track_request->Request->addChild( 'RequestOption', 'activity' );
    $track_request->addChild( 'TrackingNumber', $tracking );
    $requestXML = $this->access_request->asXML() . $track_request->asXML();
    $response = $this->send( $this->api_path . 'Track', $requestXML );
    if( $response['Response']['ResponseStatusCode'] ){
      return date( 'l, F jS', strtotime( $response['Shipment']['ScheduledDeliveryDate'] ) );
    }
  }
  public function get_access_request(){
    $accessRequest = new SimpleXMLElement('<AccessRequest></AccessRequest>');
    $accessRequest->addChild( 'AccessLicenseNumber', $this->api );
    $accessRequest->addChild( 'UserId', $this->username );
    $accessRequest->addChild( 'Password', $this->password );
    return $accessRequest;
  }
  function append(SimpleXMLElement $to, SimpleXMLElement $from) {
  	// https://stackoverflow.com/questions/4778865/php-simplexml-addchild-with-another-simplexmlelement
  	// LIFESAVER ^^^
      $toDom = dom_import_simplexml($to);
      $fromDom = dom_import_simplexml($from);
      $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
  }
  public function is_end_of_day(){
      date_default_timezone_set('EST');
      return ( date('G:i') > $this->end_of_day );
  }
  public function get_pickup_date(){
      return ( $this->is_end_of_day() ) ? date( 'Ymd', strtotime('+1 day') ) : date('Ymd');
  }
  
  public function send( $url, $xml = '', $convert = true ){
    try{
        $ch = curl_init();
        if ($ch === false) {
          throw new Exception('failed to initialize');
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        // uncomment the next line if you get curl error 60: error setting certificate verify locations
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // uncommenting the next line is most likely not necessary in case of error 60
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
        $content = curl_exec($ch);
        // Check the return value of curl_exec(), too
        if ($content === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        if( $convert == true ){
          /* Process $content here */
          $xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);
          $json = json_encode($xml);
          $content = json_decode($json,TRUE);
        }
        return $content;
        // Close curl handle
        curl_close($ch);
      } catch(Exception $e) {
      trigger_error(sprintf(
          'Curl failed with error #%d: %s',
          $e->getCode(), $e->getMessage()),
          E_USER_ERROR);
    }
  }
}
$ups = new UPS();
global $ups;