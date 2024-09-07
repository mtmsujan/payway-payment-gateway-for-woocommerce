(function ($) {
  $(document).ready(function () {
    $(".jalal-vai").click(function (e) {
      e.preventDefault();

      // retrieve form data as array
      let form = $(this).closest("form");
      let formDataArray = form.serializeArray();

      // convert the form data array into a clean JSON object
      let data = {};
      $.each(formDataArray, function (index, field) {
        data[field.name] = field.value;
      });

      // Make a ajax call to php to add to cart
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        data: {
          action: "add_to_cart",
          data: JSON.stringify(data),
          security: ajax_object.nonce, // Pass the nonce for security
        },
        success: function (response) {
          console.log(response);
          let data = response.data;
          let redirect = data.redirect;

          if (redirect) {
            window.location.href = redirect;
          }
        },
      });
    });
  });
})(jQuery);
