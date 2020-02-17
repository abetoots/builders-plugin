import { initStore } from "./store";

/**
 * Think of this as a reducer similar to redux
 * How to use:
 * In redux, instead of initializing the store by a Provider, we just call the configure function to set up our initial state and actions
 * After calling the configure, we can now expect our globalState to be merged with the initialState.
 * We can also now expect the actions to be defined, we can now dispatch them by getting the dispatch from useStore.
 */

const initialState = {
  token: ""
};

const configureTokenStore = () => {
  const actions = {};
  initStore(initialState, actions);
};

export default configureTokenStore;
