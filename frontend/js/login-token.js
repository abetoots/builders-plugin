(function($) {
  $(document).ready(function() {
    /**
     * TODO do not abstract away getting of token, instead make sure the app only loads
     * TODO when logged in and w/ right capabilities on the SERVER SIDE
     */
    const TOKEN = "/wp-json/simple-jwt-authentication/v1/token";
    const VALIDATE = "/wp-json/simple-jwt-authentication/v1/token/validate";
    const REVOKE = "/wp-json/simple-jwt-authentication/v1/token/revoke";
    const REFRESH = "/wp-json/simple-jwt-authentication/v1/token/refresh";
    const RESETPASS =
      "/wp-json/simple-jwt-authentication/v1/token/resetpassword";

    $("#login-form").submit(function(event) {
      let form = this;
      event.preventDefault();
      $(".Login__submitBtn").attr("disabled", true);
      $(document.body).css("cursor", "wait");

      const login = $("#user_login").val();
      const password = $("#user_pass").val();

      /**
       * If token exists, we want to validate it first
       */
      let request;
      //Expected to be stored in local storage
      const token = localStorage.getItem("token");
      if (token) {
        request = new Request(window.location.href + VALIDATE, {
          method: "POST",
          headers: new Headers({
            "Content-Type": "application/json",
            authorization: "Bearer " + token
          })
        });
        /**
         * From here, there are two cases:
         */
        fetch(request)
          .then(async res => {
            if (res.ok) {
              //1) If it's valid, then let's try to refresh, IF NECESSARY, in checkTokenTimeout
              const result = checkTokenTimeout(
                localStorage.getItem("expirationDate"),
                token
              );

              if (result.constructor === Error) {
                //We tried to refresh but an error occured. We prompt to login again
                $(".Login__notifications").html(
                  "<span role='img' alt='error'>⚠️</span> Something went wrong. Try logging in again. If error persists, contact developer."
                );
              } else if (result !== "no refresh") {
                //Refresh is successful, set the new values
                localStorage.setItem("token", result.token);
                localStorage.setItem("userId", result.user_id);
                //by default simple jwt authentication is 7 days in UNIX timestamp(seconds)
                //Note that Javascript dates reason in milliseconds, hence * 1000
                localStorage.setItem(
                  "expirationDate",
                  new Date(result.token_expires * 1000)
                );
                localStorage.setItem("userName", result.username);
                localStorage.setItem("userRole", result.role);
                //Now we can go ahead and login
                form.submit();
              }

              $(".Login__submitBtn").attr("disabled", false);
              $(document.body).css("cursor", "auto");
            } else {
              //Response code !== 200, we handle it in catch statement
              throw "token invalid";
            }
          })
          .catch(async err => {
            // 2) If it's invalid, we need to REVOKE it as expired tokens(as of time of writing)
            //still remain in the database then get a NEW one
            console.log("Something went wrong: ", [err]);
            const result = revokeToken(token);

            //If revoking is a success
            if (result.code === "jwt_auth_revoked_token") {
              //then we can go ahead and fetch a new one
              fetchNewToken(login, password, form);
            } else {
              //if not, show an error in notification
              $(".Login__notifications").html(
                "<span role='img' alt='error'>⚠️</span> Something went wrong. Try logging in again. If error persists, contact developer."
              );
            }
            $(".Login__submitBtn").attr("disabled", false);
            $(document.body).css("cursor", "auto");
          });
      } else {
        //No token, we go ahead and fetch one
        fetchNewToken(login, password, form);
      }
    });

    /**
     * Checks if our token needs refreshing
     * *SHOULD ONLY BE INVOKED WHEN TOKEN IS VALID
     * @param {string} date Date in string format
     * @param {string} token Simple JWT Token
     * @returns {Response} The response.json() when successful
     * @returns {Error}
     */
    const checkTokenTimeout = async (date, token) => {
      //If < 1 day remaining, we refresh token
      //86400000 is 1 day in ms
      const REFRESH_BEFORE_DATE = 86400000;
      //convert string to date object first
      const expDate = new Date(date);
      //remaining time in milliseconds
      //we always assume that expDate is in the future since this should
      //only be invoked when the token is still valid
      const remainingTime = expDate.getTime() - new Date().getTime();
      if (remainingTime < REFRESH_BEFORE_DATE) {
        try {
          fetch(window.location.href + REFRESH, {
            method: "POST",
            headers: {
              Authorization: "Bearer " + token
            }
          }).then(res => {
            if (res.ok) {
              return res.json();
            } else {
              throw "could not refresh";
            }
          });
        } catch (err) {
          return err;
        }
      }
      return "no refresh";
    };

    /**
     * Function for revoking to simple-jwt-authentication/v1/token/revoke route
     * @returns {Response} The response.json() when successful
     * @returns {Error}
     * @param {string} token Simple JWT token
     */

    const revokeToken = async token => {
      try {
        fetch(window.location.href + REVOKE, {
          headers: {
            Authorization: "Bearer " + token
          }
        }).then(res => {
          if (res.ok) {
            return res.json();
          } else {
            throw res.json().code;
          }
        });
      } catch (err) {
        console.log("Error revoking:", [err]);
        return err;
      }
    };

    const fetchNewToken = (login, password, form) => {
      request = new Request(
        window.location.href +
          TOKEN +
          `?username=${login}&password=${password}`,
        {
          method: "POST"
        }
      );
      fetch(request)
        .then(res => {
          if (res.ok) {
            return res.json();
          } else {
            throw res.json().code;
          }
        })
        .then(data => {
          localStorage.setItem("token", data.token);
          localStorage.setItem("userId", data.user_id);
          //by default simple jwt authentication is 7 days in UNIX timestamp(seconds)
          //Note that Javascript dates reason in milliseconds, hence * 1000
          localStorage.setItem(
            "expirationDate",
            new Date(data.token_expires * 1000)
          );
          localStorage.setItem("userName", data.username);
          localStorage.setItem("userRole", data.role);
          //Now we can go ahead and login
          if (form) {
            form.submit();
          }
        })
        .catch(err => {
          console.log("Something went wrong with fetching token: ", [err]);
          $(".Login__submitBtn").attr("disabled", false);
          $(document.body).css("cursor", "auto");
          $(".Login__notifications").html(
            "<span role='img' alt='error'>⚠️</span> Something went wrong. Try logging in again. If error persists, contact developer."
          );
        });
    };
  });
})(jQuery);
