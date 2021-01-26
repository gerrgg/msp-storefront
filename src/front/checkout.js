/**
 * checkout
 * All frontend checkout functions
 */
const checkout = (() => {
  /**
   * wrap functions in a init function to use with jquery
   * while still passing as a module.
   */
  const runAll = ($) => {
    // needs jquery to use woocommerce jquery body hook
    syncCheckoutFields($);

    // does not need jquery
    limitPostcode();
    defaultPurchaseOrder();
  };

  /**
   * Sync customer billing information with woocommerce
   * update_checkout hook
   */
  const syncCheckoutFields = ($) => {
    // get all the inputs from customer details
    const checkoutFields = document.querySelectorAll(
      "#customer_details > div:first-child input"
    );

    // use woocommerce jquery hook to update checkout
    const update_checkout = () => $(document.body).trigger("update_checkout");

    // loop elements and attach event trigger on focusout
    for (let input of checkoutFields) {
      input.addEventListener("focusout", update_checkout);
    }
  };

  /**
   * Merchant-e (our payment processor) wants only the first 5
   * characters of the billing zip code.
   * @important
   */
  const limitPostcode = () => {
    const fields = {
      postcode: document.querySelector("#billing_postcode"),
      calcPostCode: document.querySelector("#calc_shipping_postcode"),
    };

    const firstFiveCharacters = ({ target }) => {
      if (target.value.length > 5) {
        target.value = target.value.substr(0, 5);
      }
    };

    Object.keys(fields).forEach((key) => {
      const field = fields[key];

      if (field) {
        field.addEventListener("keyup", firstFiveCharacters);
        field.addEventListener("keydown", firstFiveCharacters);
      }
    });
  };

  /**
   * Automatically default purchase order to "" and prevents
   * field from being autocompleted.
   */
  const defaultPurchaseOrder = () => {
    const purchase_order = document.querySelector("#billing_po");
    if (purchase_order) purchase_order.value = "";
  };

  // allows us to pass as module to main functions page
  return { runAll };
})();

export default checkout;
