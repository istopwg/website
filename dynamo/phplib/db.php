<?php
//
// Common database include file for PHP web pages.
//
// This file implements a PDO interface to database objects.  All tables must
// have an "id INTEGER PRIMARY KEY" field which is used as the row selector.
//
// The interface is implemented as a series of "helper" functions operating on
// a developer-supplied class object rather than as a common base class that the
// developer extends to avoid subclassing issues:
//
//   db_create() - Add a new object to the named table.
//   db_delete() - Delete an existing object from the named table.
//   db_load()   - Load an existing object from the named table.
//   db_save()   - Save an existing object to the named table.
//   db_search() - Search for objects in the named table.
//
// Table columns are passed as associative arrays using PDO::PARAM_xxx
// constants, for example:
//
//   $columns = array(
//     "id" => PDO::PARAM_INT,
//     "name" => PDO::PARAM_STR
//   );
//
// The following legacy functions are provided for transitioning to the new
// interface:
//
//   db_count()     - Return the number of rows in the response.
//   db_insert_id() - Return the ID of the last inserted row.
//   db_next()      - Return the next row in the result as an associative array.
//   db_query()     - Perform a query.
//   db_seek()      - Seek to a specific result row.
//
// There are also two helper functions for converting to/from the database
// DATETIME format:
//
//   db_datetime() - Convert a UNIX timestamp to a SQL DATETIME value.
//   db_seconds()  - Convert a SQL DATETIME value to a UNIX timestamp.
//
// This file depends on a separate include to define the following variables
// for accessing the database:
//
//   DB_ADMIN    - Email address of database administrator.
//   DB_HOST     - Server hosting the database.
//   DB_NAME     - Database name.
//   DB_USER     - Database user.
//   DB_PASSWORD - Database password.
//
// The global variable _DB is managed here, which is the connection to the
// database.

// Connect to the MySQL server using the DB_HOST, DB_USER, DB_PASSWORD, and
// DB_NAME variables that are set in config/site.cfg...
try
{
  $_DB = new PDO("mysql:dbname=$DB_NAME;host=$DB_HOST;charset=UTF8", $DB_USER, $DB_PASSWORD);
}
catch (PDOException $e)
{
  // Unable to connect; display an error message...
  print("<p>Database error: " . htmlspecialchars($e->getMessage()) . "</p>\n");
  print("<p>Please report the problem to <a href='mailto:$DB_ADMIN'>"
       ."$DB_ADMIN</a>.</p>\n");
  exit();
}


//
// 'db_count()' - Return the number of rows in a statement.
//

function				// O - Number of rows in result
db_count($stmt)				// I - Statement
{
  if ($stmt)
    return ($stmt->rowCount());
  else
    return (0);
}


//
// 'db_create()' - Create a new object in the database table.
//

function				// O - New ID or FALSE on error
db_create($obj,				// I - Object to create
          $table,			// I - Table name
          $columns)			// I - Associative columns array (name => type)
{
  global $_DB;

  $query  = "INSERT INTO $table VALUES(NULL";
  foreach ($columns as $name => $type)
    $query .= ",:$name";
  $query .= ");";

  $stmt = $_DB->prepare($query);
  foreach ($columns as $name => $type)
    $stmt->bindValue(":$name", $obj->$name, $type);

  $stmt->execute();

  return ($_DB->lastInsertId());
}


//
// 'db_datetime()' - Convert a UNIX timestamp to a DATETIME value.
//

function				// O - DATETIME value
db_datetime($seconds = 0)		// I - UNIX timestamp
{
  if ($seconds == 0)
    $seconds = time();

  return (gmdate("Y-m-d H:i:s", $seconds));
}


//
// 'db_delete()' - Delete an existing object in the database table.

function				// O - TRUE on success, FALSE otherwise
db_delete($table,			// I - Table name
          $id)				// I - Object ID
{
  global $_DB;

  $stmt = $_DB->prepare("DELETE FROM $table WHERE id = :id");
  $stmt->bindValue(":id", $id, PDO::PARAM_INT);
  $stmt->execute();

  return ($stmt->rowCount() == 1);
}


//
// 'db_insert_id()' - Return the ID of the last inserted record.
//

function				// O - ID number
db_insert_id()
{
  global $_DB;

  return ($_DB->lastInsertId());
}


//
// 'db_load()' - Load an existing object from the database table.
//

function				// O - TRUE on success, FALSE otherwise
db_load(&$obj,				// I - Object
        $table,				// I - Table name
        $id,				// I - Object ID
        $columns)			// I - Associative columns array (name => type)
{
  global $_DB;

  $query  = "";
  $prefix = "SELECT ";
  foreach ($columns as $name => $type)
  {
    $query .= "$prefix$name";
    $prefix = ",";
  }
  $query .= " FROM $table WHERE id=?";

  if ($stmt = db_query($query, array($id)))
  {
    $stmt->setFetchMode(PDO::FETCH_INTO, $obj);
    return ($stmt->fetch(PDO::FETCH_INTO));
  }
  else
    return (FALSE);
}


//
// 'db_next()' - Fetch the next row from a statement.
//

function				// O - Row object or NULL at end
db_next($stmt)				// I - Statement
{
  if ($stmt)
    return ($stmt->fetch(PDO::FETCH_ASSOC));
  else
    return (null);
}


// Convert params array to a string for debug output...
function _db_param_string($params)
{
  if ($params)
  {
    $prefix = " {";
    $text   = "";
    foreach ($params as $value)
    {
      if (is_string($value))
        $text .= "$prefix'" . htmlspecialchars($value, ENT_QUOTES) . "'";
      else
        $text .= "$prefix$value";
      $prefix = ",";
    }
    return ("$text}");
  }
  else
    return ("");
}


//
// 'db_query()' - Run a SQL query and return the executed statement.
//

function				// O - Statement or null
db_query($sql,				// I - SQL query string
	 $params = null)		// I - Array of parameters
{
  global $_DB, $DB_DEBUG;


  $stmt = $_DB->prepare($sql);
  if ($stmt->execute($params))
  {
    if ($DB_DEBUG > 1)
    {
      $count = $stmt->rowCount();
      print("<p>SQL query \"" . htmlspecialchars($sql) . _db_param_string($params) . "\" returned "
	   ."$count row(s).</p>\n");
    }

    return ($stmt);
  }
  else if ($DB_DEBUG > 0)
  {
    $error = $stmt->errorInfo();
    print("<p>SQL query \"" . htmlspecialchars($sql) . _db_param_string($params) . "\" failed: " .
          htmlspecialchars($error[0]) . ":" .
          htmlspecialchars($error[2]) . "</p>\n");
  }

  return (null);
}


//
// 'db_save()' - Save an existing object to the database table.
//

function				// O - TRUE on success, FALSE otherwise
db_save($obj,				// I - Object
        $table,				// I - Table name
        $id,				// I - Object ID
        $columns)			// I - Associative columns array (name => type)
{
  global $_DB, $DB_DEBUG;

  $query  = "UPDATE $table";
  $prefix = " SET ";
  foreach ($columns as $name => $type)
  {
    $query .= "$prefix$name=:$name";
    $prefix = ",";
  }
  $query .= " WHERE id=:id;";

  $stmt = $_DB->prepare($query);
  if ($DB_DEBUG > 1)
    print("<p>SQL query \"" . htmlspecialchars($query) . "\"</p>\n");
  $stmt->bindValue(":id", $obj->id, PDO::PARAM_INT);
  foreach ($columns as $name => $type)
    $stmt->bindValue(":$name", $obj->$name, $type);

  $stmt->execute();

  return ($stmt->rowCount() == 1);
}


//
// 'db_search()' - Search for objects in a database table.
//
// "columns" contains the columns that participate in the search.
//
// "keyvals" is an associative array of column names and values that must match,
// for example:
//
//   array("key1" => "value1", "key2" => "value2", "key3" => array("value3a", "value3b"))
//
// would map to:
//
//   key1 = value1 AND key2 = value2 AND (key3 = value3a OR key3 = value3b)
//
// "words" contains free-form query strings like "(word AND word) OR word".
//
// If both are specified the "keyvals" search is ANDed with the "words" search.
//
// "order_by" is a list of comma-delimited field names to control the order of
// the search results.  Prefix the field name with a "-" for a descending order.
//

function				// O - Array of IDs
db_search($table,			// I - Table name
          $columns,			// I - Associative columns array (name => type)
          $keyvals = null,		// I - Associative array of key/value(s) pairs
          $search = "",			// I - Free-form query string
          $order_by = "id")		// I - Comma-delimited ordering columns
{
  global $_DB, $DB_DEBUG;

  $params = array();

  if ($search != "")
  {
    // Convert the search string to an array of words...
    $words = db_search_words($search);

    // Loop through the array of words, adding them to the query...
    $query  = "WHERE (";
    $prefix = "";
    $next   = " OR";
    $logic  = "";

    foreach ($words as $word)
    {
      if ($word == "or")
      {
	$next = " OR";
	if ($prefix != "")
	  $prefix = " OR";
      }
      else if ($word == "and")
      {
	$next = " AND";
	if ($prefix != "")
	  $prefix = " AND";
      }
      else if ($word == "not")
	$logic = " NOT";
      else
      {
	$query .= "$prefix$logic (";
	$subpre = "";

	if (preg_match("/^[0-9]+\$/", $word))
	{
	  $query .= "${subpre}id=?";
	  $subpre = " OR ";

          array_push($params, (int)$word);
	}

        foreach ($columns as $name => $type)
        {
          if ($type != PDO::PARAM_STR || preg_match("/(hash|_id|_date)\$/", $name))
            continue;

	  $query .= "${subpre}$name LIKE ?";
	  $subpre = " OR ";

	  array_push($params, "%$word%");
	}

	$query .= ")";
	$prefix = $next;
	$logic  = "";
      }
    }

    $query .= ")";
  }
  else
    $query = "";

  if ($keyvals)
  {
    if ($query == "")
      $prefix = "WHERE";
    else
      $prefix = " AND";

    foreach ($keyvals as $name => $value)
    {
      if (preg_match("/^[a-z_A-Z0-9]+[<>=]+\$/", $name))
        $query .= "$prefix $name?";
      else
        $query .= "$prefix $name=?";
      $prefix = " AND";

      array_push($params, $value);
    }
  }

  if ($order_by != "")
  {
    // Separate order into array...
    $ocolumns = explode(" ", $order_by);
    $prefix = " ORDER BY";

    // Add ORDER BY stuff...
    foreach ($ocolumns as $field)
    {
      if ($field[0] == '+')
	$query .= "$prefix " . substr($field, 1);
      else if ($field[0] == '-')
	$query .= "$prefix " . substr($field, 1) . " DESC";
      else
	$query .= "$prefix $field";

      $prefix = ",";
    }
  }

  // Do the query and store the matching ids...
  $sql  = "SELECT id FROM $table $query";
  $stmt = db_query($sql, $params);

  if ($stmt !== FALSE)
  {
    $matches = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if ($DB_DEBUG > 1)
    {
      print("<pre>matches=");
      print_r($matches);
      print("</pre>\n");
    }

    return ($matches);
  }

  return (null);
}


//
// 'db_search_words()' - Generate an array of search words.
//

function				// O - Array of words
db_search_words($search = "")		// I - Search string
{
  $words = array();
  $temp  = "";
  $len   = strlen($search);

  for ($i = 0; $i < $len; $i ++)
  {
    switch ($search[$i])
    {
      case "\"" :
          if ($temp != "")
	  {
	    $words[sizeof($words)] = strtolower($temp);
	    $temp = "";
	  }

	  $i ++;

	  while ($i < $len && $search[$i] != "\"")
	  {
	    $temp .= $search[$i];
	    $i ++;
	  }

	  $words[sizeof($words)] = strtolower($temp);
	  $temp = "";
          break;

      case " " :
      case "\t" :
      case "\n" :
          if ($temp != "")
	  {
	    $words[sizeof($words)] = strtolower($temp);
	    $temp = "";
	  }
	  break;

      default :
          $temp .= $search[$i];
	  break;
    }
  }

  if ($temp != "")
    $words[sizeof($words)] = strtolower($temp);

  return ($words);
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
?>
