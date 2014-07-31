<?php
//
// Class for the organization table.
//

include_once "site.php";

define("ORGANIZATION_STATUS_NON_MEMBER", 0);
define("ORGANIZATION_STATUS_NON_VOTING", 1);
define("ORGANIZATION_STATUS_SMALL_VOTING", 2);
define("ORGANIZATION_STATUS_LARGE_VOTING", 3);

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

  $results = db_query("SELECT id, name FROM organization ORDER BY name");
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
      $other_val = trim($_POST[$other_name]);
    else
      $other_val = "";

    print("<br>\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
    html_form_text($other_name, "Organization Name", $other_val, "", 1, "", $html_input_width - 5);
  }
}

?>
