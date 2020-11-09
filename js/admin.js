jQuery(function ($) {
  var msp_admin = {
    init: function () {
      //    $('.color-field').wpColorPicker();
      $("#resource_tab").on(
        "click",
        "button.add_input_line",
        msp_admin.add_line_item
      );
      $("#msp-product-video").on(
        "click",
        "button.add",
        msp_admin.add_video_line
      );

      // Page builder
      $("#msp-specifications").on(
        "click",
        "button.add",
        msp_admin.add_spec_row
      );
      $("#msp-specifications").on(
        "click",
        "button.remove",
        msp_admin.delete_spec_row
      );

      $("#menu-to-edit").on(
        "click",
        "input.upload-btn",
        msp_admin.upload_media
      );
    },

    upload_media(e) {
      /**
       * Enables use of the WP Media button in WP-Admin menu item
       */
      e.preventDefault();
      let input = e.target.previousElementSibling;

      image = wp
        .media({
          title: "Upload Image",
          multiple: false,
        })
        .open()
        .on("select", function (e) {
          var uploaded_image = image.state().get("selection").first();

          console.log(uploaded_image);
          var image_url = uploaded_image.toJSON().url;

          input.value = image_url;
        });
    },

    delete_spec_row(e) {
      let tr = $(e.target.parentElement.parentElement);
      let label = tr.find("td:first-child input").val();
      let post_id = $("#post_ID").val();

      let data = {
        action: "msp_delete_specification",
        label: label,
        post_id: post_id,
      };

      $.post(ajaxurl, data);

      tr.remove();
    },

    submit_promo_option(e) {
      // get inputs
      inputs = $(e.delegateTarget).find("input");
      var max = inputs.length / 2 - 1;

      // setup for ajax
      var data = {
        action: "msp_create_promo_line",
        options: {},
      };

      for (var i = 0; i <= max; i++) {
        var id = $('input[name="msp_promo[' + i + '][image_id]"]').val();
        var link = $('input[name="msp_promo[' + i + '][permalink]"]').val();
        if (id.length > 0 && link.length > 0) data.options[id] = link;
      }

      $.post(ajaxurl, data, function (response) {
        msp_admin.add_promo_line(e, max + 1);
      });
    },

    add_spec_row(e) {
      /**
       * Find the class of the row (index) and increment. This should avoid specs getting out of order.
       */
      let $table = $(e.delegateTarget).find("table");
      let i = Number($table.find("tr").last().attr("class")) + 1;

      if (isNaN(i)) {
        i = 0;
      }

      $table.append(
        $("<tr/>", { class: i }).append(
          "<td>" +
            '<input style="width: 100%" type="text" name="specification[' +
            i +
            '][label]">' +
            "</td>",
          "<td>" +
            '<input style="width: 100%" type="text" name="specification[' +
            i +
            '][value]">' +
            "</td>",
          '<td><button class="remove" type="button" role="button">&times;</button></td>'
        )
      );
    },

    add_video_line: function (e) {
      let button = $(e.target);
      let $table = $("#msp_product_video_input_table");

      count = !isNaN(button.attr("data-count"))
        ? +button.attr("data-count") + 1
        : 0;

      $table.append($("<input />", { name: "product_video[" + count + "]" }));

      button.attr("data-count", count++);
    },

    add_line_item: function (e) {
      let button = $(e.target);
      count = +button.attr("data-count") + 1;

      let $parent = $("#resource_input_wrapper");

      let fields = {
        label: $("#resource_label").clone(),
        url: $("#resource_url").clone(),
      };

      Object.keys(fields).forEach(function (field) {
        fields[field].attr({
          id: "",
          name: "resource_" + field + "[" + count + "]",
        });
        $parent.append(fields[field].val(""));
      });

      $parent.append("<br>");

      $(e.target).attr("data-count", count++);
    },
  };

  msp_admin.init();

  if (!$("#title-length").length) return;
  $("#title-length").html($("#gsf_title").val().length);

  $("#gsf_title").keyup(function () {
    $("#title-length").html($(this).val().length);
  });
});
