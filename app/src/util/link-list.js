import DxGrid from "../components/DXGrid/DXGrid";

import asyncComponent from "../components/asyncComponent/asyncComponent";

//Lazy load all components except the component for root or home
const AsyncGraphiQlWrap = asyncComponent(() => {
  return import("../components/GraphiQlWrap/GraphiQlWrap");
});

/**
 * Adding a menu here automatically adds them to our RoutesList component(see App.js)
 * You can pass them to a Menu component knowing that the routes are taken care of.
 */

export const defaultLinkList = [
  {
    path: "/",
    exact: true,
    component: DxGrid,
    label: "Dashboard"
  },
  {
    path: "/logout",
    exact: true,
    component: null,
    label: "Logout"
  }
];

export const devLinkList = [
  {
    path: "/dashboard",
    exact: true,
    component: DxGrid,
    label: "Dashboard"
  },
  {
    path: "/__graphiql",
    exact: true,
    component: AsyncGraphiQlWrap,
    label: "GraphiQlIDE"
  }
];
