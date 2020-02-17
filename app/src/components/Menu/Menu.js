import React from "react";
import { NavLink } from "react-router-dom";

import PropTypes from "prop-types";
import "./Menu.scss";

const Menu = props => {
  const classes = props.toggled ? ["Menu"] : ["Menu -hidden"];

  if (props.desktopOnly) {
    classes.push("-desktopOnly");
  }

  return (
    <nav className={classes.join(" ")}>
      {props.label ? <h4 className="Menu__label">{props.label}</h4> : ""}
      <ul
        className={`Menu__ul ${props.horizontal ? " -horizontal" : ""}`}
        aria-expanded={props.toggled}
      >
        {props.linklist.map(item => (
          <li className="Menu__li" key={item.path}>
            <NavLink to={item.path} exact={item.exact} className="Menu__link">
              <div className="Menu__touchTarget">
                <span>{item.label}</span>
              </div>
            </NavLink>
          </li>
        ))}
      </ul>
    </nav>
  );
};
Menu.defaultProps = {
  visible: true
};
Menu.propTypes = {
  routes: PropTypes.arrayOf(
    PropTypes.exact({
      path: PropTypes.string,
      exact: PropTypes.bool,
      component: PropTypes.elementType,
      label: PropTypes.string
    }).isRequired
  ),
  desktopOnly: PropTypes.bool,
  label: PropTypes.string,
  horizontal: PropTypes.bool,
  linklist: PropTypes.array,
  visible: PropTypes.bool
};

export default Menu;
