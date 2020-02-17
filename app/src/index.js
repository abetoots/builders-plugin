import React from "react";
import ReactDOM from "react-dom";
import "./index.scss";

//Start components
import App from "./App";
import ApolloClient from "apollo-boost";
import { ApolloProvider } from "@apollo/react-hooks";

//shared
import { tokenCache } from "./hooks/wp-graphql-token";

export const client = new ApolloClient({
  uri: BASE_URL + "graphql",
  request: operation => {
    if (tokenCache.token) {
      console.log("Client operation setting context...");
      operation.setContext({
        headers: {
          authorization: `Bearer ${tokenCache.token}`
        }
      });
    }
  }
});

const target = document.querySelector("#root");
console.log("target", [target]);
if (target) {
  ReactDOM.render(
    <ApolloProvider client={client}>
      <App />
    </ApolloProvider>,
    target
  );
}
