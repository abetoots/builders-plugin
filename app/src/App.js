import React, { useState, useEffect } from "react";

//Routing
import { BrowserRouter } from "react-router-dom";

//Components
import RoutesList from "./components/RoutesList/RoutesList";
import Boundary from "./components/Boundary/Boundary";

//Shared, globals, utils
import * as linklists from "./util/link-list";
import {
  useRefreshToken,
  REFTOKEN,
  useLoginMutation
} from "./hooks/wp-graphql-token";
import { uniqArray } from "./util/util";

const App = () => {
  //Extracts all unique routes from all our linklists to be registered on our Routes List
  const routes = uniqArray(Object.values(linklists).flat());
  const [loadingRefresh, errorRefresh, { startRefresh }] = useRefreshToken();
  const [
    login,
    { called, errorLogin, data, loadingLogin }
  ] = useLoginMutation();
  //ComponentDidMoount
  useEffect(() => {
    //if we found a refresh token
    if (localStorage.getItem(REFTOKEN)) {
      startRefresh();
    } else {
      if (!called) {
        //try getting an auth token by logging in
        login();
      }
    }
  }, []);

  return (
    <BrowserRouter basename="/builders">
      <Boundary
        loading={loadingRefresh || loadingLogin}
        error={errorRefresh || errorLogin}
      >
        <RoutesList routes={routes} />
      </Boundary>
    </BrowserRouter>
  );
};

export default App;
