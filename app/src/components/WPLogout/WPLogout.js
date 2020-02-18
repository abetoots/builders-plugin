import React, { useEffect, useState } from "react";
import PropTypes from "prop-types";
import { Redirect } from "react-router-dom";

import Boundary from "../Boundary/Boundary";

import { tokenCache } from "../../hooks/wp-graphql-token";

const Logout = props => {
  const [done, setDone] = useState(false);

  useEffect(() => {
    console.log("logout mounted");
    tokenCache.token = null;
    //WPReact is given to us through wp_localize_script
    if (WPReact) {
      fetch(
        BASE_URL +
          `wp-admin/admin-ajax.php?action=react_app_logout_hook&react_query_wpnonce=${WPReact.logout_nonce}`,
        {
          method: "POST"
        }
      )
        .then(res => {
          if (res.ok) {
            return res.json();
          } else {
            throw res.json();
          }
        })
        .then(data => {
          console.log(data);
          setDone(true);
        })
        .catch(err => {
          console.log(err);
          setDone(true);
        });
    }
  }, []);

  //Redirects are handled by the server but just to be safe
  return <Boundary loading={!done}>{done ? <Redirect to="/" /> : ""}</Boundary>;
};

export default Logout;
