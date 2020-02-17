import React from "react";
import PropTypes from "prop-types";

import { Route, Switch } from "react-router-dom";
//Components
import asyncComponent from "../asyncComponent/asyncComponent";

const AsyncNotFound = asyncComponent(() => {
  return import("../NotFound404/NotFound404");
});

const RoutesList = props => {
  return (
    <>
      {/* For a given set of routes, load only one of each */}
      <Switch>
        {props.routes.map(route => (
          <Route
            key={route.path}
            exact={route.exact}
            path={route.path}
            component={route.component ? route.component : AsyncNotFound}
          />
        ))}
        {/* Handle our 404 case. Must be defined last */}
        <Route component={AsyncNotFound} />
      </Switch>
    </>
  );
};

RoutesList.propTypes = {
  routes: PropTypes.arrayOf(
    PropTypes.exact({
      path: PropTypes.string,
      exact: PropTypes.bool,
      component: PropTypes.elementType,
      label: PropTypes.string
    }).isRequired
  )
};

export default RoutesList;
