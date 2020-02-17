import React from "react";
import PropTypes from "prop-types";
import toStartCase from "lodash.startcase";
import capitalize from "lodash.capitalize";

// Serves as formatter for boolean integers into check or x marks
export const intToEmoji = ({ value }) => {
  if (value === 1) {
    return (
      <span style={{ margin: "0 auto" }} role="img" alt="true">
        ✔️
      </span>
    );
  } else {
    return (
      <span style={{ margin: "0 auto" }} role="img" alt="false">
        ❌
      </span>
    );
  }
};
intToEmoji.propTypes = {
  value: PropTypes.number
};

//Converts any string to start case
export const dataToStartCase = ({ value }) => {
  let newVal = toStartCase(value);
  return newVal;
};
dataToStartCase.propTypes = {
  value: PropTypes.string
};

//Capitalize a string
export const dataCapitalize = ({ value }) => {
  return capitalize(value);
};
dataCapitalize.propTypes = {
  value: PropTypes.string
};

//Converts ACF's `Ymd` date format into JavaScript date
export const extractDateFromACFDate = ({ value }) => {
  if (!value) {
    return "";
  }
  const pattern = /(\d{4})(\d{2})(\d{2})/;
  const parts = value.match(pattern);
  // months start with 0 in js, that's why you need to substract 1
  // -----------------------v----------v
  const date = new Date(parts[1], parts[2] - 1, parts[3]);
  const formatter = new Intl.DateTimeFormat(undefined, {
    year: "numeric",
    month: "long",
    day: "2-digit"
  });
  return formatter.format(date);
};

export const formatDateToDmy = ({ value }) => {
  const date = new Date(value);
  const formatter = new Intl.DateTimeFormat(undefined, {
    year: "numeric",
    month: "long",
    day: "2-digit"
  });
  return formatter.format(date);
};
