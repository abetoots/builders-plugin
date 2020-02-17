import { useState, useEffect } from "react";
import { useMutation } from "@apollo/react-hooks";
import { gql } from "apollo-boost";

//Follow this auth flow : https://hasura.io/blog/best-practices-of-using-jwt-with-graphql/#jwt_persist
// For WPGraphQl , simplified : https://github.com/NeverNull/gatsby-apollo-wpgraphql-jwt-starter/issues/1

export const tokenCache = {};

export const EXPDATE = "expirationDate";
export const REFTOKEN = "refreshToken";

let timerId;
let delay;

let password =
  process.env.NODE_ENV === "development"
    ? process.env.PASSWORD_DEV
    : process.env.PASSWORD_PROD;

const loginMutation = gql`
    mutation LoginUser{
      login(
        input: {
          clientMutationId: ""
          username: "${process.env.LOGIN}"
          password: "${password}"
        }
      ) {
        authToken
        refreshToken
        user {
          jwtAuthExpiration
        }
      }
    }
  `;
console.log("refresh token: ", localStorage.getItem(REFTOKEN));
const refreshMutation = `
    mutation RefreshToken {
      refreshJwtAuthToken(
        input: { clientMutationId: "", jwtRefreshToken: "${localStorage.getItem(
          REFTOKEN
        )}" }
      ) {
        authToken
      }
    }
  `;

export const useLoginMutation = () => {
  const [done, setDone] = useState(false);
  const [login, { called, errorLogin, data, loadingLogin }] = useMutation(
    loginMutation,
    {
      onCompleted: data => {
        console.log("Mutation done", [data]);
      },
      onError: err => {
        console.log("Mutation error:", [err]);
      }
    }
  );

  useEffect(() => {
    console.log("Use Effect login mutation");
    if (data) {
      tokenCache.token = data.login.authToken;
      localStorage.setItem(REFTOKEN, data.login.refreshToken);
      localStorage.setItem(EXPDATE, data.login.user.jwtAuthExpiration);
      silentlyRefresh();
      //Triggers a rerender so that tokenCache.token will be defined for our app
      setDone(true);
    } else {
      setDone(true);
    }
  }, [data]);

  return [login, { called, errorLogin, data, loadingLogin }];
};

const setNewExpirationDate = () => {
  //set the new expiry date as 4.5 minutes from now IN SECONDS since epoch time
  localStorage.setItem(
    EXPDATE,
    new Date(
      //date now in milliseconds since epoch time + 4.5 minutes in ms
      new Date().getTime() + 270000
    ).getTime() / 1000
  ); // converted to seconds since epoch time)
};

// A refresh without loading and error hints, simply for background task. We DONT
// interfere with the UX
const backgroundRefresh = async () => {
  console.log("Refreshing in the background ...");
  try {
    const res = await fetch(BASE_URL + "graphql", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        query: refreshMutation
      })
    }).then(res => {
      if (res.ok) {
        return res.json();
      } else {
        throw res.json();
      }
    });
    //We got a valid token, user can now have access UI
    //No more UI loading hint, only silent refreshes from here on out
    setNewExpirationDate();
    console.log(res);
    tokenCache.token = res.data.refreshJwtAuthToken.authToken;
    silentlyRefresh();
  } catch (err) {
    console.log(err);
    //TODO we dont want to interrupt UX when the background refresh fails
    //TODO maybe handle this idk
  }
};

const silentlyRefresh = () => {
  console.log("Silently refreshing ...");
  //we expect the date to be stored as seconds since epoch time
  //since settimeout talks in milliseconds, we get the remaining milliseconds (hence multiplying to 1000)
  //expiration date in ms since epoch - Date now in ms since epoch = remaining milliseconds
  delay = new Date(localStorage.getItem(EXPDATE) * 1000) - new Date().getTime();
  timerId = setTimeout(backgroundRefresh, delay);
};

/**
 * Refreshing always start by actively fetching a new auth token (since no token is stored in local storage)
 * Success: We start a silentl refresh in the background
 * Error: We try and fetch anyway using login mutation
 *
 * @param {String} refreshToken
 */
export const useRefreshToken = () => {
  const [loadingRefresh, setLoadingRefresh] = useState(false);
  const [errorRefresh, setErrorRefresh] = useState("");

  const [login, { called, errorLogin, loadingLogin }] = useLoginMutation();

  // Actively refresh meaning we need to get a token no matter what. We force the user to wait so
  // we give out loading hints.
  const activelyRefreshWithHints = async () => {
    setLoadingRefresh(true);
    console.log("Actively refreshing ...");
    try {
      const res = await fetch(BASE_URL + "graphql", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          query: refreshMutation
        })
      }).then(res => {
        if (res.ok) {
          return res.json();
        } else {
          throw res.json();
        }
      });

      //We got a valid token, user can now have access UI
      //No more UI loading hint, only silent refreshes from here on out
      setNewExpirationDate();
      tokenCache.token = res.data.refreshJwtAuthToken.authToken;
      silentlyRefresh();
      setLoadingRefresh(false);
    } catch (err) {
      console.log("Active refresh error: ", [err]);
      //Maybe invalid refresh token. Still, we are actively refreshing, so we get a new one through the logging in.
      if (!called) {
        login();
      }
      //All UI hints should now be handled by the login mutation
      if (loadingLogin) {
        setLoadingRefresh(loadingLogin);
      }
      if (errorLogin) {
        setErrorRefresh(error);
      }
    }
  };

  const startRefresh = () => {
    activelyRefreshWithHints();
  };

  return [loadingRefresh, errorRefresh, { startRefresh }];
};
