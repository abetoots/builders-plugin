import React, { useState, useEffect } from "react";
import Paper from "@material-ui/core/Paper";

//Apollo
import { useLazyQuery } from "@apollo/react-hooks";
import { gql } from "apollo-boost";

//Components
import Layout from "../layout";
//TODO maybe format the data server side to prevent doing everything here
import {
  StudentTypeProvider,
  GymRoleProvider,
  GenderProvider,
  BirthdateProvider,
  BranchProvider,
  RegistrationProvider
} from "../DataFormatters/providers";

//Dx React Grid Components
import {
  Grid,
  Table,
  TableHeaderRow,
  TableSelection,
  PagingPanel
} from "@devexpress/dx-react-grid-material-ui";

//Plugins
import {
  SelectionState,
  PagingState,
  SortingState,
  IntegratedPaging,
  IntegratedSelection,
  IntegratedSorting
} from "@devexpress/dx-react-grid";

//Shared
import { tokenCache } from "../../hooks/wp-graphql-token";
import { devLinkList } from "../../util/link-list";

const DxGrid = () => {
  const FULLNAME = "full_name";
  const GENDER = "gender";
  const BIRTHDATE = "birthdate";
  const ISSTUDENT = "is_student";
  const BRANCH = "branch";
  const GYMROLE = "gym_role";
  const REGISTERED = "registeredDate";
  //Columns
  const [columns] = useState([
    { name: FULLNAME, title: "Full Name" },
    { name: GENDER, title: "Gender" },
    { name: BIRTHDATE, title: "Birthdate" },
    { name: ISSTUDENT, title: "Student" },
    { name: BRANCH, title: "Branch" },
    { name: GYMROLE, title: "Role" },
    { name: REGISTERED, title: "Registered" }
  ]);

  const [rows, setRows] = useState([]);

  //The actual query - used as rows for our table
  const query = gql`
    {
      users(where: { roleIn: GYM_MEMBER }, first: 9999) {
        nodes {
          full_name
          is_student
          birthdate
          gender
          id
          gym_role
          branch
          registeredDate
        }
      }
    }
  `;
  const [startQuery, { called, loading, error, data }] = useLazyQuery(query);

  //State management plugins  https://devexpress.github.io/devextreme-reactive/react/grid/docs/guides/plugin-overview/
  //Handle state changes in our table, provided by dx-react-grid
  const [sorting, getSorting] = useState([]);
  const [selection, setSelection] = useState([]);
  const [currentPage, setCurrentPage] = useState(0);
  const [pageSize, setPageSize] = useState(10);
  const [pageSizes] = useState([10, 20, 50, 100]);

  //For data formatting
  const [studentColumns] = useState([ISSTUDENT]);
  const [roleColumns] = useState([GYMROLE]);
  const [genderColumns] = useState([GENDER]);
  const [birthdateColumns] = useState([BIRTHDATE]);
  const [branchColumns] = useState([BRANCH]);
  const [registrationColumns] = useState([REGISTERED]);

  //Table UI
  const [tableColumnExtensions] = useState([
    { columnName: ISSTUDENT, width: "auto" },
    { columnName: GENDER, width: "auto" },
    { columnName: FULLNAME, wordWrapEnabled: true }
  ]);

  //We wait for the token to be set before querying. Remember to only call once by checking if previously called
  if (tokenCache.token && !called) {
    startQuery();
  }

  const changePageSize = val => {
    console.log(val);
  };

  useEffect(() => {
    if (data) {
      console.log("Apollo: Done!", [data]);
      setRows(data.users.nodes);
    }
    if (loading) {
      console.log("Apollo: Fetching");
    }
  }, [data, loading]);

  return (
    <Layout
      loading={loading}
      error={error}
      linklist={process.env.NODE_ENV === "development" ? devLinkList : false}
    >
      <div className={`DxGrid ${loading ? "-loading" : ""}`}>
        {called && data ? (
          <Paper>
            <Grid rows={rows} columns={columns}>
              <SortingState sorting={sorting} onSortingChange={getSorting} />
              <PagingState
                onCurrentPageChange={setCurrentPage}
                pageSize={pageSize}
                onPageSizeChange={changePageSize}
              />
              <SelectionState
                selection={selection}
                onSelectionChange={setSelection}
              />

              <IntegratedSorting />
              <IntegratedSelection />
              <IntegratedPaging />

              <StudentTypeProvider for={studentColumns} />
              <GymRoleProvider for={roleColumns} />
              <GenderProvider for={genderColumns} />
              <BirthdateProvider for={birthdateColumns} />
              <BranchProvider for={branchColumns} />
              <RegistrationProvider for={registrationColumns} />

              <Table columnExtensions={tableColumnExtensions} />
              <TableHeaderRow showSortingControls />
              <TableSelection showSelectAll />
              <PagingPanel pageSizes={pageSizes} />
            </Grid>
          </Paper>
        ) : (
          ""
        )}
      </div>
    </Layout>
  );
};

export default DxGrid;
