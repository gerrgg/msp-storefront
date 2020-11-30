jQuery(document).ready(function ($) {
  require("./mobile-menu.js");
  require("./slider.js");
  var msp = {
    $modal: $("#msp_modal"),
    $header: $("#mobile-menu"),
    $checkout_fields: $("#customer_details > div:first-child"),
    bulk_order_list: {},

    init: function () {
      // $(document.body).on( 'click', 'i.msp-star-rating', msp.bind_create_review_star_buttons )
      // $('#msp_review').on( 'click', '.remove-product-image-from-review', msp.delete_user_product_image )

      $("#msp_submit_question").on(
        "blur",
        'input[name="question"]',
        msp.customer_faq_validate_question
      );
      $("#msp_submit_question").on(
        "click",
        "button",
        msp.customer_submit_question
      );

      $("#msp_customer_faq").on(
        "click",
        ".msp-submit-answer",
        msp.customer_submit_awnser
      );

      $(".woocommerce-variation-add-to-cart").on(
        "change",
        'input[name="variation_id"]',
        msp.replace_single_product_price_range
      );

      $("#filter-button").click(function () {
        $(".widget-area").slideToggle();
      });

      // makes the update shipping options more consistant.
      this.$checkout_fields.on("focusout", "input", function () {
        $(document.body).trigger("update_checkout");
      });

      $("#msp-contact").on("click", "button.submit", msp.submit_contact_form);

      $("#bulk-tab-content").on(
        "change",
        ".var-bulk-update",
        msp.add_to_bulk_list
      );

      this.$modal.on("show.bs.modal", this.route);
      this.$modal.on("submit", "form", this.submit);

      this.$header.on(
        "click",
        "li.menu-item-has-children",
        this.open_nav_child_list
      );

      this.promo_pop_up();
    },

    append_nav_layers: function () {
      $(".woocommerce-widget-layered-nav").each(function (i, e) {
        e.insertAdjacentHTML(
          "beforeend",
          '<a href="javascript:void(0)" class="see_more" >See more</a>'
        );
      });
    },

    see_more_nav_layers: function (e) {
      let list = e.delegateTarget.getElementsByTagName("ul")[0];
      if (typeof list !== "undefined") {
        console.log(list);
      }
    },

    promo_pop_up: function () {
      let cookie = getCookie("msp_promo_seen");

      if ($(".promo_pop_up_btn").length && cookie != wp_ajax.cookie_version) {
        setTimeout(function () {
          $(".promo_pop_up_btn").click();
        }, 2000);
      }
    },

    replace_single_product_price_range: async function (e) {
      const main_price = $("#order-tab-content p.price");
      const availability = $(".woocommerce-variation-availability").html();

      // Setup for ajax request
      let data = {
        action: "msp_get_variation_price_html",
        id: $("input.variation_id").val(),
      };

      // Make sure every option is selected before replacing price range
      var ready = [];
      let options = $("table.variations select");

      options.each(function () {
        ready.push(this.value.length);
      });

      if (ready.every(readyCheck)) {
        main_price.html(
          '<div class="spinner-border text-danger" role="status"><span class="sr-only">Loading...</span></div>'
        );

        await $.post(wp_ajax.url, data, function (response) {
          if (response) main_price.html(response + availability);
        });

        const newPrice = main_price
          .find("span.amount")
          .last()
          .text()
          .replace("$", "")
          .replace(",", "");

        if (newPrice) {
          msp.update_discount_table(newPrice);
        }
      }
    },

    update_discount_table: function (newPrice) {
      console.log(newPrice);
      $("#msp-bulk-pricing tbody > tr > td").each((i, td) => {
        if (i !== 0) {
          const updatedDiscount =
            "$" + (parseFloat(td.className) * newPrice).toFixed(2);

          td.innerText = updatedDiscount;
        }
      });
    },

    add_to_bulk_list: function (e) {
      //https://dsgnwrks.pro/snippets/woocommerce-allow-adding-multiple-products-to-the-cart-via-the-add-to-cart-query-string/
      let order_list = [];
      let $input = $(e.target);

      if (+$input.val() > +$input.attr("max")) $input.val(+$(this).attr("max"));

      msp.bulk_order_list[$input.attr("id")] = $input.val();

      for (var key in msp.bulk_order_list) {
        // validation
        if (msp.bulk_order_list.hasOwnProperty(key)) {
          // do not add if qty is 0; for accidental clicks.
          if (msp.bulk_order_list[key] != 0) {
            // formatting; if qty is 1, only push the key, not the value for -> woocommerce_maybe_add_multiple_products_to_cart()
            msp.bulk_order_list[key] > 1
              ? order_list.push(key + ":" + msp.bulk_order_list[key])
              : order_list.push(key);
          }
        }
      }

      order_list = order_list.join(",");

      let url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/cart/?add-to-cart=" +
        order_list +
        ",";
      let add_to_cart_str = !order_list.length ? "#" : url;

      $("#iww_bulk_form_submit").attr("href", add_to_cart_str);

      console.log(add_to_cart_str, order_list);
    },

    open_nav_child_list: function (e) {
      $child = $(e.target.children[1]);
      console.log(e, $child);
    },

    get_json_from_url: function (url) {
      // https://stackoverflow.com/questions/8486099/how-do-i-parse-a-url-query-parameters-in-javascript

      if (!url) url = location.href;
      var question = url.indexOf("?");
      var hash = url.indexOf("#");
      if (hash == -1 && question == -1) return {};
      if (hash == -1) hash = url.length;
      var query =
        question == -1 || hash == question + 1
          ? url.substring(hash)
          : url.substring(question + 1, hash);
      var result = {};
      query.split("&").forEach(function (part) {
        if (!part) return;
        part = part.split("+").join(" "); // replace every + with space, regexp-free version
        var eq = part.indexOf("=");
        var key = eq > -1 ? part.substr(0, eq) : part;
        var val = eq > -1 ? decodeURIComponent(part.substr(eq + 1)) : "";
        var from = key.indexOf("[");
        if (from == -1) result[decodeURIComponent(key)] = val;
        else {
          var to = key.indexOf("]", from);
          var index = decodeURIComponent(key.substring(from + 1, to));
          key = decodeURIComponent(key.substring(0, from));
          if (!result[key]) result[key] = [];
          if (!index) result[key].push(val);
          else result[key][index] = val;
        }
      });
      return result;
    },

    route: function (e) {
      let $button = $(e.relatedTarget);

      var path = {
        title: $button.attr("data-title"),
        model: $button.attr("data-model"),
        action: $button.attr("data-action"),
        id: $button.attr("data-id"),
      };

      msp.$modal.find(".modal-title").text(path.title);
      msp[path.model](path.action, path.id);
    },

    submit: function (e) {
      // this obviously wont work for other modal submissions.
      e.preventDefault();
      console.log(e);
      let body = msp.$modal.find(".modal-body");
      let action = $(e.target).find('input[name="action"]').val();
      let model = $(e.target).find('input[name="model"]').val();
      let data = {
        action: action,
        form_data: $(e.target).serialize(),
      };

      $.post(wp_ajax.url, data, function (response) {
        msp[model]("post", "", response);
      });
    },

    close: function () {
      msp.$modal.modal("toggle");
    },

    size_guide: function (action, id) {
      $.post(
        wp_ajax.url,
        { action: "msp_get_product_size_guide_src", id: id },
        function (response) {
          msp.$modal
            .find(".modal-body")
            .html($("<img/>", { src: response, class: "mx-auto" }));
        }
      );
    },

    promo: function (action, id) {
      $.post(
        wp_ajax.url,
        { action: "msp_get_promo_pop_up_link_and_image", id: id },
        function (response) {
          console.log(response);
          let html = $("<a/>", { href: response.link }).append(
            $("<img/>", { src: response.src, class: "mx-auto" })
          );

          msp.$modal.find(".modal-body").html(html);

          document.cookie =
            "msp_promo_seen=" +
            wp_ajax.cookie_version +
            "; path=/; max-age=2592000;";
        }
      );
    },

    leave_feedback: function (action, id, response) {
      let body = msp.$modal.find(".modal-body");

      switch (action) {
        case "get":
          $.post(
            wp_ajax.url,
            { action: "msp_get_leave_feedback_form", id: id },
            function (response) {
              body.html(response);
            }
          );
          break;
        case "post":
          console.log(response);
          if (!response) {
            body
              .find(".feedback")
              .text("Feedback requires atleast a star rating; thanks!");
          } else {
            body.html(
              '<div class="text-center"><i class="fas fa-check-circle fa-2x text-success"></i><h1>Thank you for your feedback!</h1></div>'
            );
            setTimeout(function () {
              msp.close();
            }, 3000);
          }
          break;
      }
    },

    submit_contact_form: function (e) {
      var data = $(e.delegateTarget.children[1]);
      var errors = data.find("#errors");
      console.log(data, errors);
      $.post(wp_ajax.url, data.serialize(), function (response) {
        if (response) {
          $(e.delegateTarget).html(
            '<p class="lead">Thank you, expect a response same or next business day.</p>'
          );
        } else {
          data
            .find("#errors")
            .text("Error, please try again and fill everything out!");
        }
      });
    },
  };

  msp.init();

  if ($("#msp_select2_products").length != 0) {
    $("#msp_select2_products").select2({
      ajax: {
        url: wp_ajax.url, // AJAX URL is predefined in WordPress admin
        dataType: "json",
        delay: 250, // delay in ms while typing when to perform a AJAX search
        data: function (params) {
          return {
            q: params.term, // search query
            action: "msp_get_products", // AJAX action for admin-ajax.php
          };
        },
        processResults: function (data) {
          var options = [];
          if (data) {
            // data is the array of arrays, and each of them contains ID and the Label of the option
            $.each(data, function (index, text) {
              // do not forget that "index" is just auto incremented value
              options.push({ id: text[0], text: text[1] });
            });
          }
          return {
            results: options,
          };
        },
        cache: true,
      },
      minimumInputLength: 3, // the minimum of symbols to input before perform a search
    });
  }

  var max_chars = 5;

  $("#billing_postcode").keydown(function (e) {
    if ($(this).val().length >= max_chars) {
      $(this).val($(this).val().substr(0, max_chars));
    }
  });

  $("#billing_postcode").keyup(function (e) {
    if ($(this).val().length >= max_chars) {
      $(this).val($(this).val().substr(0, max_chars));
    }
  });

  $("#billing_po").val("");

  function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
    var expires = "max-age=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }

  function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(";");
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == " ") {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }

  function readyCheck(length) {
    return length > 0;
  }
});
