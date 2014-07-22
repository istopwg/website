<?php
//
// "$Id: db.php 112 2013-09-23 14:08:25Z msweet $"
//
// Common database include file for PHP web pages.
//
// This file should be included using "include_once"...
//
// Contents:
//
//   db_close()     - Close the database.
//   db_count()     - Return the number of rows in a query result.
//   db_datetime()  - Convert a UNIX timestamp to a DATETIME value.
//   db_escape()    - Escape special chars in string for query.
//   db_free()      - Free a database query result...
//   db_insert_id() - Return the ID of the last inserted record.
//   db_next()      - Fetch the next row of a result set and return it as
//                    an object.
//   db_query()     - Run a SQL query and return the result or 0 on error.
//   db_seconds()   - Convert a DATETIME value to a UNIX timestamp.
//   db_seek()      - Seek to a specific row within a result.
//

//
// Connect to the MySQL server using DB_HOST, DB_USER, DB_PASSWORD
// that are set above...
//

$DB_CONN = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);

if ($DB_CONN)
{
  // Connected to server; select the database...
  mysql_select_db($DB_NAME, $DB_CONN);
  mysql_set_charset("utf8", $DB_CONN);
}
else
{
  // Unable to connect; display an error message...
  $sqlerrno = mysql_errno();
  $sqlerr   = mysql_error();

  print("<p>Database error $sqlerrno: $sqlerr</p>\n");
  print("<p>Please report the problem to <a href='mailto:$DB_ADMIN'>"
       ."$DB_ADMIN</a>.</p>\n");
}


//
// 'db_count()' - Return the number of rows in a query result.
//

function				// O - Number of rows in result
db_count($result)			// I - Result of query
{
  if ($result)
    return (mysql_num_rows($result));
  else
    return (0);
}


//
// 'db_datetime()' - Convert a UNIX timestamp to a DATETIME value.
//

function db_datetime($seconds = 0)
{
  if ($seconds == 0)
    $seconds = time();

  return (gmdate("Y-m-d H:i:s", $seconds));
}

//
// 'db_escape()' - Escape special chars in string for query.
//

function				// O - Quoted string
db_escape($str)				// I - String
{
  global $DB_CONN;

  return (mysql_real_escape_string($str, $DB_CONN));
}


//
// 'db_free()' - Free a database query result...
//

function
db_free($result)			// I - Result of query
{
  if ($result)
    mysql_free_result($result);
}


//
// 'db_insert_id()' - Return the ID of the last inserted record.
//

function				// O - ID number
db_insert_id()
{
  global $DB_CONN;

  return (mysql_insert_id($DB_CONN));
}


//
// 'db_next()' - Fetch the next row of a result set and return it as an object.
//

function				// O - Row object or NULL at end
db_next($result)			// I - Result of query
{
  if ($result)
    return (mysql_fetch_array($result));
  else
    return (NULL);
}


//
// 'db_query()' - Run a SQL query and return the result or 0 on error.
//

function				// O - Result of query or NULL
db_query($SQL_QUERY)			// I - SQL query string
{
  global $DB_CONN, $DB_DEBUG;


  $result = mysql_query($SQL_QUERY, $DB_CONN);

  if ($result === FALSE && $DB_DEBUG > 0)
    print("<p>SQL query \"" . htmlspecialchars($SQL_QUERY) . "\" failed: " .
          htmlspecialchars(mysql_error()) . "</p>\n");
  else if ($result !== FALSE && $DB_DEBUG > 1)
  {
    $count = db_count($result);
    print("<p>SQL query \"" . htmlspecialchars($SQL_QUERY) . "\" returned "
         ."$count row(s).</p>\n");
  }

  return ($result);
}


//
// 'db_seconds()' - Convert a DATETIME value to a UNIX timestamp.
//

function db_seconds($datetime = "")
{
  global $LOGIN_TIMEZONE;

  if ($datetime == "")
    return (time());

  date_default_timezone_set("UTC");
  $seconds = strtotime($datetime);
  date_default_timezone_set($LOGIN_TIMEZONE);

  return ($seconds);
}



//
// 'db_seek()' - Seek to a specific row within a result.
//

function				// O - TRUE on success, FALSE otherwise
db_seek($result,			// I - Result of query
        $index = 0)			// I - Row number (0 = first row)
{
  if ($result)
    return (mysql_data_seek($result, $index));
  else
    return (FALSE);
}


//
// End of "$Id: db.php 112 2013-09-23 14:08:25Z msweet $".
//
?>
