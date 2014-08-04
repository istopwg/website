<?php
//
// Class for the organization table.
//

include_once "site.php";

define("ORGANIZATION_STATUS_NON_MEMBER", 0);
define("ORGANIZATION_STATUS_NON_VOTING", 1);
define("ORGANIZATION_STATUS_SMALL_VOTING", 2);
define("ORGANIZATION_STATUS_LARGE_VOTING", 3);
define("ORGANIZATION_STATUS_DELETED", 4);

$ORGANIZATION_STATUSES = array(
  ORGANIZATION_STATUS_NON_MEMBER => "Non-Member",
  ORGANIZATION_STATUS_NON_VOTING => "Non-Voting Member",
  ORGANIZATION_STATUS_SMALL_VOTING => "Small Voting Member",
  ORGANIZATION_STATUS_LARGE_VOTING => "Large Voting Member",
  ORGANIZATION_STATUS_DELETED => "Deleted"
);

$ORGANIZATION_NAMES = array("o0" => "");

class organization
{
  //
  // Instance variables...
  //

  var $id;
  var $status;
  var $name, $name_valid;
  var $domain, $domain_valid;
  var $is_everywhere;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'organization::organization()' - Create a organization object.
  //

  function				// O - New organization object
  organization($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'organization::clear()' - Initialize a new a organization object.
  //

  function
  clear()
  {
    global $LOGIN_ID;

    $this->id            = 0;
    $this->status        = ORGANIZATION_STATUS_NON_MEMBER;
    $this->name          = "";
    $this->domain        = "";
    $this->is_everywhere = 0;
    $this->create_date   = "";
    $this->create_id     = $LOGIN_ID;
    $this->modify_date   = "";
    $this->modify_id     = $LOGIN_ID;
  }


  //
  // 'organization::delete()' - Delete (set status to deleted) an organization object.
  //

  function
  delete()
  {
    db_query("UPDATE user SET status = 3 WHERE id=$this->id");
  }


  //
  // 'organization::form()' - Display a form for an organization object.
  //

  function
  form($options = "")			// I - Page options
  {
    global $ORGANIZATION_STATUSES, $PHP_SELF;


    if ($this->id <= 0)
      $action = "Create Organization";
    else
      $action = "Save Changes";

    html_form_start("$PHP_SELF?U$this->id$options");

    html_form_field_start("name", "Display Name", $this->name_valid);
    html_form_text("name", "'Example Inc.' or 'Individual: Name'", $this->name);
    html_form_field_end();

    html_form_field_start("domain", "Domain", $this->domain_valid);
    html_form_email("domain", "example.com", $this->domain);
    html_form_field_end();

    // status
    html_form_field_start("", "Status");
    html_form_select("status", $ORGANIZATION_STATUSES, "", $this->status);
    html_form_checkbox("is_everywhere", "Signed IPP Everywhere Printer Self-Certification Agreement", $this->is_everywhere);
    html_form_field_end();

    // Submit
    html_form_end(array("SUBMIT" => "+$action"));
  }


  //
  // 'organization::load()' - Load a organization object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    $this->clear();

    $result = db_query("SELECT * FROM organization WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);
    $this->id            = $row["id"];
    $this->status        = $row["status"];
    $this->name          = $row["name"];
    $this->domain        = $row["domain"];
    $this->is_everywhere = $row["is_everywhere"];
    $this->create_date   = $row["create_date"];
    $this->create_id     = $row["create_id"];
    $this->modify_date   = $row["modify_date"];
    $this->modify_id     = $row["modify_id"];

    db_free($result);

    return (TRUE);
  }


  //
  // 'organization::loadform()' - Load an organization object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST, $LOGIN_ID, $LOGIN_IS_ADMIN, $REQUEST_METHOD;


    if (!html_form_validate())
      return (FALSE);

    if (array_key_exists("status", $_POST))
      $this->status = (int)$_POST["status"];

    if (array_key_exists("is_everywhere", $_POST))
      $this->is_everywhere = 1;
    else
      $this->is_everywhere = 0;

    if (array_key_exists("name", $_POST))
      $this->name = trim($_POST["name"]);

    if (array_key_exists("domain", $_POST))
      $this->domain = trim($_POST["domain"]);

    return ($this->validate());
  }


  //
  // 'organization::save()' - Save a organization object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
    {
      return (db_query("UPDATE organization "
                      ." SET status = $this->status"
                      .", name = '" . db_escape($this->name) . "'"
                      .", domain = '" . db_escape($this->domain) . "'"
                      .", is_everywhere = $this->is_everywhere"
                      .", modify_date = '" . db_escape($this->modify_date) . "'"
                      .", modify_id = $this->modify_id"
                      ." WHERE id = $this->id") !== FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if (db_query("INSERT INTO organization VALUES"
                  ."(NULL"
                  .", $this->status"
                  .", '" . db_escape($this->name) . "'"
                  .", '" . db_escape($this->domain) . "'"
                  .", $this->is_everywhere"
                  .", '" . db_escape($this->create_date) . "'"
                  .", $this->create_id"
                  .", '" . db_escape($this->modify_date) . "'"
                  .", $this->modify_id"
                  .")") === FALSE)
        return (FALSE);

      $this->id = db_insert_id();
    }

    return (TRUE);
  }


  //
  // 'organization::validate()' - Validate the current organization object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    $valid = TRUE;


    if ($this->name == "")
    {
      $this->name_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->name_valid = TRUE;

    if (!preg_match("/^[a-zA-Z0-9\\.-]+\\.[a-zA-Z]{2,}\$/", $this->domain))
    {
      $this->domain_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->domain_valid = TRUE;

    return ($valid);
  }
}


//
// 'organization_lookup()' - Lookup an organization by name or domain.
//

function				// O - ID or 0 if not found
organization_lookup($name)		// I - Organization or domain name
{
  $dname  = db_escape($name);
  $result = db_query("SELECT id FROM organization WHERE name LIKE '$dname' OR domain LIKE '$dname';");
  if (db_count($result) == 1)
  {
    $row = db_next($result);
    $id  = $row["id"];
  }
  else
    $id = 0;

  db_free($result);

  return ($id);
}


//
// 'organization_name()' - Return the name of an organization.
//

function				// O - Name
organization_name($id)			// I - Organization ID
{
  global $ORGANIZATION_NAMES;


  $id = (int)$id;

  if (array_key_exists("o$id", $ORGANIZATION_NAMES))
    return ($ORGANIZATION_NAMES["o$id"]);

  $result = db_query("SELECT name FROM organization WHERE id=$id;");
  if (db_count($result) == 1)
  {
    $row  = db_next($result);
    $name = htmlspecialchars($row["name"], ENT_QUOTES);
  }
  else
    $name = "";

  db_free($result);

  $ORGANIZATION_NAMES["o$id"] = $name;

  return ($name);
}


//
// 'organization_search()' - Get a list of organization objects.
//

function				// O - Array of organization IDs
organization_search($search = "",	// I - Search string
                    $order = "")	// I - Order fields
{
  if ($search != "")
  {
    // Convert the search string to an array of words...
    $words = html_search_words($search);

    // Loop through the array of words, adding them to the query...
    $query  = " WHERE (";
    $prefix = "";
    $next   = " OR";
    $logic  = "";

    reset($words);
    foreach ($words as $word)
    {
      if ($word == "or")
      {
	$next = ' OR';
	if ($prefix != '')
	  $prefix = ' OR';
      }
      else if ($word == "and")
      {
	$next = ' AND';
	if ($prefix != '')
	  $prefix = ' AND';
      }
      else if ($word == "not")
	$logic = ' NOT';
      else
      {
	$query .= "$prefix$logic (";
	$subpre = "";

	if (preg_match("/^[0-9]+\$/", $word))
	{
	  $query .= "${subpre}id = $word";
	  $subpre = " OR ";
	}

	$query .= "${subpre}name LIKE \"%$word%\"";
	$subpre = " OR ";
	$query .= "${subpre}domain LIKE \"%$word%\"";

	$query .= ")";
	$prefix = $next;
	$logic  = '';
      }
    }

    $query .= ")";
  }
  else
    $query = "";

  if ($order != "")
  {
    // Separate order into array...
    $fields = explode(" ", $order);
    $prefix = " ORDER BY ";

    // Add ORDER BY stuff...
    foreach ($fields as $field)
    {
      if ($field[0] == '+')
	$query .= "${prefix}" . substr($field, 1);
      else if ($field[0] == '-')
	$query .= "${prefix}" . substr($field, 1) . " DESC";
      else
	$query .= "${prefix}$field";

      $prefix = ", ";
    }
  }

  // Do the query and convert the result to an array of objects...
  $result  = db_query("SELECT id FROM organization$query");
  $matches = array();

  while ($row = db_next($result))
    $matches[sizeof($matches)] = $row["id"];

  // Free the query result and return the array...
  db_free($result);

  return ($matches);
}


//
// 'organization_select()' - Show the organization selection control.
//

function
organization_select(
    $formname = "organization_id",	// I - Form name to use
    $id = 0,				// I - Currently selected organization, if any
    $any_id = "",			// I - Allow "any organization"?
    $prefix = "",			// I - Prefix on values
    $other_id = "",			// I - Allow "other organization"?
    $other_name = "")			// I - Form name for other field
{
  global $ORGANIZATION_NAMES, $_POST;


  print("<select name=\"$formname\">");

  if ($any_id != "")
    print("<option value=\"0\">$prefix$any_id</option>");

  $results = db_query("SELECT id, name FROM organization ORDER BY name WHERE status < " . ORGANIZATION_STATUS_DELETED);
  while ($row = db_next($results))
  {
    $oid          = $row["id"];
    $name         = htmlspecialchars($row["name"]);

    if ($oid == $id)
      print("<option value=\"$oid\" selected>$prefix$name</option>");
    else
      print("<option value=\"$oid\">$prefix$name</option>");

    $ORGANIZATION_NAMES["o$row[id]"] = $name;
  }

  if ($other_id != "")
  {
    if ($id == -1)
      print("<option value=\"-1\" selected>$prefix$other_id</option>");
    else
      print("<option value=\"-1\">$prefix$other_id</option>");
  }

  db_free($results);

  print("</select>");
  if ($other_name != "")
  {
    if (array_key_exists($other_name, $_POST))
      $other_val = htmlspecialchars(trim($_POST[$other_name]), ENT_QUOTES);
    else
      $other_val = "";

    print(" <input type=\"text\" name=\"$other_name\" value=\"$other_val\" maxlength=\"255\" placeholder=\"Other Organization\" size=\"20\">");
  }
}

?>
