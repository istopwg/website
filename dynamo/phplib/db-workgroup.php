<?php
//
// Class for the workgroup table.
//

include_once "site.php";

define("WORKGROUP_STATUS_INACTIVE", 0);
define("WORKGROUP_STATUS_ACTIVE_BOF", 1);
define("WORKGROUP_STATUS_ACTIVE_WG", 2);

$WORKGROUP_STATUSES = array(
  WORKGROUP_STATUS_INACTIVE => "Inactive",
  WORKGROUP_STATUS_ACTIVE_BOF => "Active BOF",
  WORKGROUP_STATUS_ACTIVE_WG => "Active Workgroup"
);

$WORKGROUP_NAMES = array("w0" => "");


class workgroup
{
  //
  // Instance variables...
  //

  var $id;
  var $status;
  var $name, $name_valid;
  var $wwwdir, $wwwdir_valid;
  var $ftpdir, $ftpdir_valid;
  var $list, $list_valid;
  var $contents, $contents_valid;
  var $chair_id, $vicechair_id, $secretary_id;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'workgroup::workgroup()' - Create a workgroup object.
  //

  function				// O - New workgroup object
  workgroup($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'workgroup::clear()' - Initialize a new a workgroup object.
  //

  function
  clear()
  {
    global $LOGIN_ID;

    $this->id           = 0;
    $this->status       = WORKGROUP_STATUS_INACTIVE;
    $this->name         = "";
    $this->wwwdir       = "";
    $this->ftpdir       = "";
    $this->list         = "";
    $this->contents     = "";
    $this->chair_id     = 0;
    $this->vicechair_id = 0;
    $this->secretary_id = 0;
    $this->create_date  = "";
    $this->create_id    = $LOGIN_ID;
    $this->modify_date  = "";
    $this->modify_id    = $LOGIN_ID;
  }


  //
  // 'workgroup::delete()' - Delete a workgroup object.
  //

  function
  delete()
  {
    db_query("DELETE FROM workgroup WHERE id=$this->id");
    $this->clear();
  }


  //
  // 'workgroup::form()' - Display a form for a workgroup object.
  //

  function
  form($options = "")			// I - Page options
  {
    global $WORKGROUP_STATUSES, $PHP_SELF;


    if ($this->id <= 0)
      $action = "Create Workgroup";
    else
      $action = "Save Changes";

    html_form_start("$PHP_SELF?U$this->id$options");

    html_form_field_start("name", "Display Name", $this->name_valid);
    html_form_text("name", "Example Model", $this->name);
    html_form_field_end();

    html_form_field_start("wwwdir", "Web Site Directory", $this->wwwdir_valid);
    html_form_text("wwwdir", "example", $this->wwwdir, "", 1, "www.pwg.org/");
    html_form_field_end();

    html_form_field_start("ftpdir", "FTP Directory", $this->ftpdir_valid);
    html_form_text("ftpdir", "example", $this->ftpdir, "", 1, "ftp.pwg.org/pub/pwg/");
    html_form_field_end();

    html_form_field_start("list", "Mailing List", $this->list_valid);
    html_form_email("list", "example@pwg.org", $this->list);
    html_form_field_end();

    html_form_field_start("contents", "Description", $this->contents_valid);
    html_form_text("contents", "A short description of the workgroup's activities.", $this->contents,
                   "Formatting/markup rules:\n\n"
                  ."! Header\n"
                  ."!! Sub-header\n"
                  ."- Unordered list\n"
                  ."* Unordered list\n"
                  ."1. Numbered list\n"
                  ."\" Blockquote\n"
                  ."SPACE preformatted text\n"
                  ."[[link||text label]]\n", 10);
    html_form_field_end();

    // status
    html_form_field_start("status", "Status");
    html_form_select("status", $WORKGROUP_STATUSES, "", $this->status);
    html_form_field_end();

    // chair_id, vicechair_id, secretary_id
    html_form_field_start("chair_id", "Chair");
    user_select("chair_id", $this->chair_id, USER_SELECT_MEMBER, "None");
    html_form_field_end();

    html_form_field_start("vicechair_id", "Vice Chair");
    user_select("vicechair_id", $this->vicechair_id, USER_SELECT_MEMBER, "None");
    html_form_field_end();

    html_form_field_start("secretary_id", "Secretary");
    user_select("secretary_id", $this->secretary_id, USER_SELECT_MEMBER, "None");
    html_form_field_end();

    // Submit
    html_form_end(array("SUBMIT" => "+$action"));
  }


  //
  // 'workgroup::load()' - Load a workgroup object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    $this->clear();

    $result = db_query("SELECT * FROM workgroup WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);
    $this->id           = $row["id"];
    $this->status       = $row["status"];
    $this->name         = $row["name"];
    $this->wwwdir       = $row["wwwdir"];
    $this->ftpdir       = $row["ftpdir"];
    $this->list         = $row["list"];
    $this->contents     = $row["contents"];
    $this->chair_id     = $row["chair_id"];
    $this->vicechair_id = $row["vicechair_id"];
    $this->secretary_id = $row["secretary_id"];
    $this->create_date  = $row["create_date"];
    $this->create_id    = $row["create_id"];
    $this->modify_date  = $row["modify_date"];
    $this->modify_id    = $row["modify_id"];

    db_free($result);

    return (TRUE);
  }


  //
  // 'workgroup::loadform()' - Load a workgroup object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST;


    if (!html_form_validate())
      return (FALSE);

    if (array_key_exists("status", $_POST))
      $this->status = (int)$_POST["status"];

    if (array_key_exists("name", $_POST))
      $this->name = trim($_POST["name"]);

    if (array_key_exists("wwwdir", $_POST))
      $this->wwwdir = trim($_POST["wwwdir"]);

    if (array_key_exists("ftpdir", $_POST))
      $this->ftpdir = trim($_POST["ftpdir"]);

    if (array_key_exists("list", $_POST))
      $this->list = trim($_POST["list"]);

    if (array_key_exists("contents", $_POST))
      $this->contents = trim($_POST["contents"]);

    if (array_key_exists("chair_id", $_POST))
      $this->chair_id = (int)$_POST["chair_id"];

    if (array_key_exists("vicechair_id", $_POST))
      $this->vicechair_id = (int)$_POST["vicechair_id"];

    if (array_key_exists("secretary_id", $_POST))
      $this->secretary_id = (int)$_POST["secretary_id"];

    return ($this->validate());
  }


  //
  // 'workgroup::save()' - Save a workgroup object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
    {
      return (db_query("UPDATE workgroup "
                      ." SET status = $this->status"
                      .", name = '" . db_escape($this->name) . "'"
                      .", wwwdir = '" . db_escape($this->wwwdir) . "'"
                      .", ftpdir = '" . db_escape($this->ftpdir) . "'"
                      .", list = '" . db_escape($this->list) . "'"
                      .", contents = '" . db_escape($this->contents) . "'"
                      .", chair_id = $this->chair_id"
                      .", vicechair_id = $this->vicechair_id"
                      .", secretary_id = $this->secretary_id"
                      .", modify_date = '" . db_escape($this->modify_date) . "'"
                      .", modify_id = $this->modify_id"
                      ." WHERE id = $this->id") !== FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if (db_query("INSERT INTO workgroup VALUES"
                  ."(NULL"
                  .", $this->status"
                  .", '" . db_escape($this->name) . "'"
                  .", '" . db_escape($this->wwwdir) . "'"
                  .", '" . db_escape($this->ftpdir) . "'"
                  .", '" . db_escape($this->list) . "'"
                  .", '" . db_escape($this->contents) . "'"
                  .", $this->chair_id"
                  .", $this->vicechair_id"
                  .", $this->secretary_id"
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
  // 'workgroup::validate()' - Validate the current workgroup object values.
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

    if (!preg_match("/^[-._0-9a-z]+\$/", $this->wwwdir))
    {
      $this->wwwdir_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->wwwdir_valid = TRUE;

    if (!preg_match("/^[-._0-9a-z]+\$/", $this->ftpdir))
    {
      $this->ftpdir_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->ftpdir_valid = TRUE;

    if (!validate_email($this->list))
    {
      $this->list_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->list_valid = TRUE;

    return ($valid);
  }
}


//
// 'workgroup_name()' - Return the name of a workgroup.
//

function				// O - Name
workgroup_name($id)			// I - Organization ID
{
  global $WORKGROUP_NAMES;


  $id = (int)$id;

  if (array_key_exists("w$id", $WORKGROUP_NAMES))
    return ($WORKGROUP_NAMES["w$id"]);

  $result = db_query("SELECT name FROM workgroup WHERE id=$id;");
  if (db_count($result) == 1)
  {
    $row  = db_next($result);
    $name = htmlspecialchars($row["name"], ENT_QUOTES);
  }
  else
    $name = "";

  db_free($result);

  $WORKGROUP_NAMES["w$id"] = $name;

  return ($name);
}


//
// 'workgroup_search()' - Get a list of workgroup objects.
//

function				// O - Array of workgroup IDs
workgroup_search($search = "",		// I - Search string
                 $order = "")		// I - Order fields
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
	$query .= "${subpre}wwwdir LIKE \"%$word%\"";
	$query .= "${subpre}ftpdir LIKE \"%$word%\"";
	$query .= "${subpre}list LIKE \"%$word%\"";

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
  $result  = db_query("SELECT id FROM workgroup$query");
  $matches = array();

  while ($row = db_next($result))
    $matches[sizeof($matches)] = $row["id"];

  // Free the query result and return the array...
  db_free($result);

  return ($matches);
}


//
// 'workgroup_select()' - Show the workgroup selection control.
//

function
workgroup_select(
    $formname = "workgroup_id",	// I - Form name to use
    $id = 0,				// I - Currently selected workgroup, if any
    $any_id = "",			// I - Allow "any workgroup"?
    $prefix = "")			// I - Prefix on values
{
  global $WORKGROUP_NAMES, $_POST;


  print("<select name=\"$formname\">");

  if ($any_id != "")
    print("<option value=\"0\">$prefix$any_id</option>");

  $results = db_query("SELECT id, name FROM workgroup WHERE status >= " . WORKGROUP_STATUS_ACTIVE_BOF . " ORDER BY name");
  while ($row = db_next($results))
  {
    $wid  = $row["id"];
    $name = htmlspecialchars($row["name"]);

    if ($wid == $id)
      print("<option value=\"$wid\" selected>$prefix$name</option>");
    else
      print("<option value=\"$wid\">$prefix$name</option>");

    $WORKGROUP_NAMES["w$row[id]"] = $name;
  }

  db_free($results);

  print("</select>");
}

?>
