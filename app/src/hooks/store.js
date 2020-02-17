import { useState, ueEffect, useEffect } from "react";
import PropTypes from "prop-types";
/**
 * Guiding principle: Similar to redux, we want to share logic and data
 * 1) A global state
 * 2) Listeners should be full of functions that we can call to update all components that are using this hook
 */
let globalState = {};
let listeners = [];
let actions = {};

export const useStore = () => {
  //Every component that uses this hook gets its own setState function
  const [, setState] = useState(globalState);

  //Every component that uses this hook gets the dispatch function
  //Like in redux, when dispatching an action, we expect a type and any payload we might also forward
  //unlike redux, instead of expecting an object like this ({type: 'some action type', ...payload})
  // we pass the action type as the first parameter instead
  const dispatch = (actionType, payload) => {
    //similar to an action in a reducer, we expect that action to return the new state when called
    const newState = actions[actionType](globalState, payload);
    globalState = { ...globalState, ...newState };

    for (const listener of listeners) {
      //Each listener (a setState function for every component that uses this hook)
      //will now update using the new globalState causing a rerender for those components
      listener(globalState);
    }
  };

  //When a component that uses this hook mounts (componentDidMount), register it's own setState to our listeners
  useEffect(() => {
    listeners.push(setState);

    // Cleanup: When a component that uses this hook unmounts, remove that component's setState from listeners
    return () => {
      listeners = listeners.filter(li => li !== setState);
    };
  }, [setState]);

  return [globalState, dispatch];
};

//In redux, this is basically the reducer. Instead of exporting this, we call it instead.
export const initStore = (initialState, userActions) => {
  if (initialState) {
    globalState = { ...globalState, ...initialState };
  }

  actions = { ...actions, ...userActions };
};

initStore.propTypes = {
  initialState: PropTypes.object,
  userActions: PropTypes.func
};
