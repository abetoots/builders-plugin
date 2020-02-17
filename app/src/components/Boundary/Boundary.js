import React from "react";
import PropTypes from "prop-types";

import Spinner2 from "../Spinner/Spinner2";

const Boundary = props => {
  if (props.loading) {
    return props.loadingComponent ? (
      props.loadingComponent
    ) : (
      <div
        style={{
          display: "flex",
          height: "100%",
          alignItems: "center"
        }}
      >
        <Spinner2 />;
      </div>
    );
  }

  if (props.error) {
    return <div>{`${props.error}`}</div>;
  }

  return props.children;
};

Boundary.propTypes = {
  loading: PropTypes.bool,
  error: PropTypes.string
};

export default Boundary;
