<?php

function msp_get_time_in_transit(){
    /**
     * Packages data and makes api call to UPS time n transit
     */
    $address = msp_get_customer_shipping();

    $data = array(
        'from' => array(
            'politicaldivision3' => get_option( 'woocommerce_store_city' ),
            'postcode'           => get_option( 'woocommerce_store_postcode' ),
            'countryCode'        => 'US'
        ),
        'to' => $address,
        'cart_weight' => WC()->cart->get_cart_contents_weight(),
        'total' => WC()->cart->get_subtotal()
    );

    $time_in_transit = get_time_in_transit($data);
    
    return $time_in_transit;
}

function msp_get_customer_shipping(){
    /**
     * Package data for UPS time in transit call
     */
    $user_id = get_current_user_id();
    $address = array();

    
    $address = array(
        'politicaldivision3' => ( null !== WC()->customer->get_shipping_city() ) ? WC()->customer->get_shipping_city() : $_POST['city'],
        'postcode' =>  ( null !== WC()->customer->get_shipping_postcode() ) ? WC()->customer->get_shipping_postcode() : $_POST['postcode'],
        'countryCode' => ( null !== WC()->customer->get_shipping_country() ) ?WC()->customer->get_shipping_country() :  $_POST['country']
    );
    
    return $address;
}

function msp_get_pickup_date( $cart_leadtime ){
    /**
     * Determine the pickup date for UPS
     */
    date_default_timezone_set('EST');
    $date = new DateTime();
    $day = $date->format('w');
    $time = $date->modify('+1 hour')->format('G');

    $leadtime = ($cart_leadtime != '') ? $cart_leadtime : get_option( 'woo_default_leadtime' );

    // if weekend add number of days until monday
    if( $day === 6 || $day === 7 ){
        $leadtime += abs( 8 - $day );
    // if its friday and after 1pm add 3 day leadtime
    } else if( $day === 5 & $time >= 13 ){
        $leadtime += 3;
    // if its not the weekend and after 2pm add a single day of leadtime
    } else if( $day < 5 && $time >= 14 ){
        $leadtime += 1;
    }


    return ( $leadtime > 0 ) ? $date->modify('+' . $leadtime . ' day') : $date;
}

function get_time_in_transit( $data ){
    /**
     * Make API call to UPS time in transit
     */
    $timeInTransit = new Ups\TimeInTransit(
        get_option( 'integration_ups_api_key'), 
        get_option( 'integration_ups_username'), 
        get_option( 'integration_ups_password'));
    try {
        $request = new \Ups\Entity\TimeInTransitRequest;

        // Addresses
        $from = new \Ups\Entity\AddressArtifactFormat;
        $from->setPoliticalDivision3( $data['from']['politicaldivision3'] );
        $from->setPostcodePrimaryLow( $data['from']['postcode'] );
        $from->setCountryCode( $data['from']['countryCode'] );
        $request->setTransitFrom($from);
        
        if( ! isset($data['to']['politicaldivision3'] ) ) return;

        $to = new \Ups\Entity\AddressArtifactFormat;
        $to->setPoliticalDivision3( $data['to']['politicaldivision3'] );
        $to->setPostcodePrimaryLow( $data['to']['postcode'] );
        $to->setCountryCode( $data['to']['countryCode'] );
        $request->setTransitTo($to);

        // Weight
        $shipmentWeight = new \Ups\Entity\ShipmentWeight;
        $shipmentWeight->setWeight( $data['cart_weight'] );
        $unit = new \Ups\Entity\UnitOfMeasurement;
        $unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_LBS);
        $shipmentWeight->setUnitOfMeasurement($unit);
        $request->setShipmentWeight($shipmentWeight);

        // Packages
        $request->setTotalPackagesInShipment(1);

        // InvoiceLines
        $invoiceLineTotal = new \Ups\Entity\InvoiceLineTotal;
        $invoiceLineTotal->setMonetaryValue( $data['cart_weight'] );
        $invoiceLineTotal->setCurrencyCode('USD');
        $request->setInvoiceLineTotal($invoiceLineTotal);

        $cart_leadtime = msp_get_cart_maxiumum_leadtime();

        // Pickup date
        $pickup_date = msp_get_pickup_date( $cart_leadtime );

        $request->setPickupDate( $pickup_date );

        // Get data
        $times = $timeInTransit->getTimeInTransit($request);

        $packaged_data = array();

        foreach($times->ServiceSummary as $serviceSummary) {
            // convert method code to shipping method id
            $shipping_method = msp_ups_service_codes_to_shipping_methods( $serviceSummary->Service->getCode() );


            // convert API response to formatted date
            $date = date_create_from_format( 'Y-m-d', $serviceSummary->EstimatedArrival->getDate() );

            // if we get a date add to packed data array for easy access from cart
            // Doing it this way allows us to make a single API call instead of one for each method
            if( ! is_bool( $date ) ) $packaged_data[$shipping_method] = date_format( $date, 'l, F jS' );
        }
        
        return $packaged_data;

    } catch (Exception $e) {
        // var_dump($e);
    }

}

function msp_ups_service_codes_to_shipping_methods( $service_code ){
    /**
     * Convert service codes from API to ups shipping methods created by the UPS Shipping plugin from Woocommerce
     */
    $service_to_methods = array(
        '1DM' => 'ups:3:14',
        '1DA' => 'ups:3:01',
        '2DM' => 'ups:3:59',
        '2DA' => 'ups:3:02',
        '3DS' => 'ups:3:12',
        'GND' => 'ups:3:03',
    );



    return isset($service_to_methods[$service_code]) ? $service_to_methods[$service_code] : '';
}

function msp_ups_service_codes_to_flatrate_methods( $service_code ){
    $service_to_flatrates = array(
        'GND' => 'flatrate:' . get_option( 'woo_standard_shipping_method_id' ),
        '3DS' => 'flatrate:' . get_option( 'woo_three_day_shipping_method_id' ),
        '2DA' => 'flatrate:' . get_option( 'woo_two_day_shipping_method_id' )
    );

    return isset($service_to_flatrates[$service_code]) ? $service_to_flatrates[$service_code] : '';
}
