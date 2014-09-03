<?php
//
// Class for the workgroup table.
//

include_once "site.php";

$WORKGROUP_COLUMNS = array(
  "status" => PDO::PARAM_INT,
  "name" => PDO::PARAM_STR,
  "wwwdir" => PDO::PARAM_STR,
  "ftpdir" => PDO::PARAM_STR,
  "list" => PDO::PARAM_STR,
  "contents" => PDO::PARAM_STR,
  "chair_id" => PDO::PARAM_INT,
  "vicechair_id" => PDO::PARAM_INT,
  "secretary_id" => PDO::PARAM_INT,
  "create_date" => PDO::PARAM_STR,
  "create_id" => PDO::PARAM_INT,
  "modify_date" => PDO::PARAM_STR,
  "modify_id" => PDO::PARAM_INT
);

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
    db_delete("workgroup", $this->id);
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
    global $WORKGROUP_COLUMNS;

    $this->clear();

    if (!db_load($this, "workgroup", $id, $WORKGROUP_COLUMNS))
      return (FALSE);

    $this->id = $id;

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
    global $LOGIN_ID, $WORKGROUP_COLUMNS;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
      return (db_save($this, "workgroup", $this->id, $WORKGROUP_COLUMNS));

    $this->create_date = $this->modify_date;
    $this->create_id   = $this->modify_id;

    if (($id = db_create($this, "workgroup", $WORKGROUP_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

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

  $result = db_query("SELECT name FROM workgroup WHERE id=?", array($id));
  if ($row = db_next($result))
    $name = htmlspecialchars($row["name"], ENT_QUOTES);
  else
    $name = "";

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
  global $WORKGROUP_COLUMNS;

  return (db_search("workgroup", $WORKGROUP_COLUMNS, null, $search, $order));
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

  print("</select>");
}

?>
