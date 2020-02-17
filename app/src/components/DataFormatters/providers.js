import React from "react";
import { DataTypeProvider } from "@devexpress/dx-react-grid";
import {
  intToEmoji,
  dataToStartCase,
  dataCapitalize,
  extractDateFromACFDate,
  formatDateToDmy
} from "./formatters";

export const StudentTypeProvider = props => (
  <DataTypeProvider formatterComponent={intToEmoji} {...props} />
);

export const GymRoleProvider = props => (
  <DataTypeProvider formatterComponent={dataToStartCase} {...props} />
);

export const BranchProvider = props => (
  <DataTypeProvider formatterComponent={dataToStartCase} {...props} />
);

export const GenderProvider = props => (
  <DataTypeProvider formatterComponent={dataCapitalize} {...props} />
);

export const BirthdateProvider = props => (
  <DataTypeProvider formatterComponent={extractDateFromACFDate} {...props} />
);

export const RegistrationProvider = props => (
  <DataTypeProvider formatterComponent={formatDateToDmy} {...props} />
);
