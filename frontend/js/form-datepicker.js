(function($) {
  var DPinstance = $("#birthdate").flatpickr({
    onChange: function(selectedDates, dateStr, instance) {
      console.log(dateStr);
      $('[data-hidden-val="datepicker"]').val(dateStr);
    },
    dateFormat: "d-m-Y"
  });
})(jQuery);
