import { useState, useEffect } from "react";
//*MAKES USE OF SIMPLE-JWT-AUTHENTICATION plugin
const TOKEN = "/wp-json/simple-jwt-authentication/v1/token";
const VALIDATE = "/wp-json/simple-jwt-authentication/v1/token/validate";
const REVOKE = "/wp-json/simple-jwt-authentication/v1/token/revoke";
const REFRESH = "/wp-json/simple-jwt-authentication/v1/token/refresh";
const RESETPASS = "/wp-json/simple-jwt-authentication/v1/token/resetpassword";

export const useNewTokenSimpleJwt = () => {
  const [loading, setLoading] = useState(false);
  const [token, setToken] = useState("");
  const [error, setError] = useState("");
  //Fetch an authentication token
  useEffect(() => {
    setLoading(true);
    //!DEVELOPMENT ONLY
    if (process.env.NODE_ENV === "development") {
      //If a token is found,
      if (localStorage.getItem("token")) {
        console.log("DEV:Token found on init. Validating...");
        let request = new Request(BASE_URL + VALIDATE, {
          method: "POST",
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`
          }
        });

        //Validate it first
        fetch(request)
          .then(res => {
            if (res.ok) {
              return res.json();
            } else {
              throw res.json().code;
            }
          })
          //Validation success, set it as the token
          .then(() => {
            console.log("DEV:Validation success. Token set");
            setToken(localStorage.getItem("token"));
            setLoading(false);
          })
          .catch(err => {
            console.log("DEV:Token not valid. Fetching new one... ", [err]);
            //Token is invalid, fetch for a new one
            fetch(
              BASE_URL +
                TOKEN +
                `?username=${process.env.LOGIN}&password=${process.env.PASSWORD}`
            )
              .then(res => {
                if (res.ok) {
                  return res.json();
                } else {
                  throw res.json().code;
                }
              })
              .then(data => {
                console.log("DEV: Fetched a new one!");
                //Fetch is a success , set the new values
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
                setToken(data.token);
                setLoading(false);
              })
              .catch(err => {
                console.log("DEV: Couldn't fetch a new one.", [err]);
                setError(err);
                setLoading(false);
              });
          });
      } else {
        console.log("DEV: No token found on init. Fetching ...");
        // if no token is found, we go ahead and fetch one !DEV ONLY
        let request = new Request(
          BASE_URL +
            TOKEN +
            `?username=${process.env.LOGIN}&password=${process.env.PASSWORD}`,
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
            console.log("DEV:Fetched a new one!");
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
            setToken(data.token);
            setLoading(false);
          })
          .catch(err => {
            console.log("DEV: Couldn't fetch a new one.", [err]);
            setError(err);
            setLoading(false);
          });
      }
    } else {
      //in prod, we expect to find it
      if (localStorage.getItem("token")) {
        setToken(localStorage.getItem("token"));
        setLoading(false);
      } else {
        //if no token is found, we trigger the error
        setError("No token found");
        setLoading(false);
      }
    }
  }, []);

  return [loading, error, token];
};
