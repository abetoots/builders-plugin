import React from "react";
import "./BurgerMenu.scss";

import PropTypes from "prop-types";

const BurgerMenu = props => (
  <button
    className={`BurgerMenu ${props.toggled ? "-toggled" : ""}`}
    aria-expanded={props.toggled}
    onClick={props.handleClick}
  >
    <div className="BurgerMenu__bar -one"></div>
    <div className="BurgerMenu__bar -two"></div>
    <div className="BurgerMenu__bar -three"></div>
    <span className={`BurgerMenu__label ${props.hideLabel ? "-hidden" : ""}`}>
      Menu
    </span>
  </button>
);

BurgerMenu.propTypes = {
  handleClick: PropTypes.func.isRequired,
  toggled: PropTypes.bool
};

export default BurgerMenu;
