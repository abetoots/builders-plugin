import { useState, useEffect } from "react";

// *Sample graphql query using Fetch API
export const useFetchQuery = (url, token, theQuery) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [data, setData] = useState("");

  if (!url) {
    return;
  }

  useEffect(() => {
    console.log("Fetch API: fetching...");
    setLoading(true);
    fetch(url + "graphql", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify({
        query: theQuery
      })
    })
      .then(res => {
        if (res.ok) {
          return res.json();
        } else {
          throw res.json();
        }
      })
      .then(data => {
        console.log("FetchAPI: Done!", [data]);
        setData(data);
        setLoading(false);
      })
      .catch(err => {
        console.log("FetchAPI: Error!", [err]);
        setError(err);
        setLoading(false);
      });
  }, []);

  return [loading, error, data];
};
