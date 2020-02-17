import React from "react";
import GraphiQL from "graphiql";
import fetch from "isomorphic-fetch";
import "graphiql/graphiql.css";

import Layout from "../layout";
import { devLinkList } from "../../util/link-list";

//TODO switch to WP GraphiQL just because it's tightly woven with WP GraphQL , this was a nice learning experience though
const GraphiQlWrap = props => {
  const fetcher = async graphQlParams => {
    const response = await fetch(BASE_URL + "/graphql", {
      method: "post",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(graphQLParams),
      credentials: "same-origin"
    });
    return await response.json();
  };

  return (
    <Layout
      linklist={process.env.NODE_ENV === "development" ? devLinkList : false}
    >
      <GraphiQL style={{ minHeight: "100vh" }} fetcher={fetcher} />
    </Layout>
  );
};

export default GraphiQlWrap;
