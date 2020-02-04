(function($) {
  //Removes the registration form notification after 3 seconds
  let notif = document.querySelector(".RegForm__notification.-success");
  if (notif !== null) {
    setTimeout(() => {
      notif.remove();
    }, 3000);
  }
})(jQuery);
