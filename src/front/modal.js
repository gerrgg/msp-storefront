const MSP_Modal = ($) => {
  const modal = $("#msp_modal");

  const route = (e) => {
    let $button = $(e.relatedTarget);
    const path = {
      title: $button.attr("data-title"),
      model: $button.attr("data-model"),
      action: $button.attr("data-action"),
      id: $button.attr("data-id"),
    };
    modal.find(".modal-title").text(path.title);

    path.model(path.action, path.id);
  };

  const submit = (e) => {
    // // this obviously wont work for other modal submissions.
    // e.preventDefault();
    // console.log(e);
    // let body = msp.$modal.find(".modal-body");
    // let action = $(e.target).find('input[name="action"]').val();
    // let model = $(e.target).find('input[name="model"]').val();
    // let data = {
    //   action: action,
    //   form_data: $(e.target).serialize(),
    // };
    // $.post(wp_ajax.url, data, function (response) {
    //   msp[model]("post", "", response);
    // });
  };

  const size_guide = (action, id) => {
    $.post(
      wp_ajax.url,
      { action: "msp_get_product_size_guide_src", id: id },
      function (response) {
        msp.$modal
          .find(".modal-body")
          .html($("<img/>", { src: response, class: "mx-auto" }));
      }
    );
  };

  modal.on("show.bs.modal", route);
  modal.on("submit", "form", submit);

  return { size_guide };
};

export default MSP_Modal;
