(function($) {
  var DPinstance = $("#birthdate").flatpickr({
    onChange: function(selectedDates, dateStr, instance) {
      $('[data-hidden-val="datepicker"]').val(dateStr);
    }
  });
})(jQuery);
