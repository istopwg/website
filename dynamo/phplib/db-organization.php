<?php
//
// Class for the organization table.
//

include_once "site.php";

$ORGANIZATION_COLUMNS = array(
  "status" => PDO::PARAM_INT,
  "name" => PDO::PARAM_STR,
  "domain" => PDO::PARAM_STR,
  "create_date" => PDO::PARAM_INT,
  "create_id" => PDO::PARAM_STR,
  "modify_date" => PDO::PARAM_INT,
  "modify_id" => PDO::PARAM_STR
);

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
    db_query("UPDATE user SET status = 3 WHERE id=?", array($this->id));
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
    html_form_text("domain", "example.com", $this->domain);
    html_form_field_end();

    // status
    html_form_field_start("", "Status");
    html_form_select("status", $ORGANIZATION_STATUSES, "", $this->status);
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
    global $ORGANIZATION_COLUMNS;

    $this->clear();

    if (db_load($this, "organization", $id, $ORGANIZATION_COLUMNS))
    {
      $this->id = $id;
      return (TRUE);
    }

    return (FALSE);
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
    global $LOGIN_ID, $ORGANIZATION_COLUMNS, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
      return (db_save($this, "organization", $this->id, $ORGANIZATION_COLUMNS));

    $this->create_date = $this->modify_date;
    $this->create_id   = $this->modify_id;

    if (($id = db_create($this, "organization", $ORGANIZATION_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

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
  $result = db_query("SELECT id FROM organization WHERE name LIKE ? OR domain LIKE ?;", array($name, $name));
  if (db_count($result) == 1)
  {
    $row = db_next($result);
    $id  = $row["id"];
  }
  else
    $id = 0;

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

  $result = db_query("SELECT name FROM organization WHERE id=?", array($id));
  if ($row = db_next($result))
    $name = htmlspecialchars($row["name"], ENT_QUOTES);
  else
    $name = "";

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
  global $ORGANIZATION_COLUMNS;

  return (db_search("organization", $ORGANIZATION_COLUMNS, null, $search, $order));
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

  $results = db_query("SELECT id, name FROM organization WHERE status < " . ORGANIZATION_STATUS_DELETED . " ORDER BY name");
  while ($row = db_next($results))
  {
    $oid  = $row["id"];
    $name = htmlspecialchars($row["name"]);

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
