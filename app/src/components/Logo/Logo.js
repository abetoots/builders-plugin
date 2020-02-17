import React from "react";
import "./Logo.scss";

const Logo = props => (
  <div className="Logo">
    <img
      className="Logo__img"
      src={props.src}
      alt={props.alt ? `${props.alt}` : "Site Logo"}
    />
  </div>
);

export default Logo;
