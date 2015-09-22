<?php
//
// Class for the user table.
//

include_once "db-organization.php";
include_once "site.php";

// DB columns...
$USER_COLUMNS = array(
  "status" => PDO::PARAM_INT,
  "email" => PDO::PARAM_STR,
  "name" => PDO::PARAM_STR,
  "organization_id" => PDO::PARAM_INT,
  "hash" => PDO::PARAM_STR,
  "is_admin" => PDO::PARAM_BOOL,
  "is_editor" => PDO::PARAM_BOOL,
  "is_member" => PDO::PARAM_BOOL,
  "is_reviewer" => PDO::PARAM_BOOL,
  "is_submitter" => PDO::PARAM_BOOL,
  "timezone" => PDO::PARAM_STR,
  "itemsperpage" => PDO::PARAM_INT,
  "create_date" => PDO::PARAM_STR,
  "create_id" => PDO::PARAM_INT,
  "modify_date" => PDO::PARAM_STR,
  "modify_id" => PDO::PARAM_INT
);

// Type of user to select...
define("USER_SELECT_ANY", 0);
define("USER_SELECT_ADMIN", 1);
define("USER_SELECT_EDITOR", 2);
define("USER_SELECT_MEMBER", 4);
define("USER_SELECT_OFFICER", 8);
define("USER_SELECT_REVIEWER", 16);

// Status values in user table...
define("USER_STATUS_BANNED", 0);
define("USER_STATUS_PENDING", 1);
define("USER_STATUS_ENABLED", 2);
define("USER_STATUS_DELETED", 3);

$USER_STATUSES = array(
  USER_STATUS_BANNED => "Banned",
  USER_STATUS_PENDING => "Pending",
  USER_STATUS_ENABLED => "Enabled",
  USER_STATUS_DELETED => "Deleted"
);

// Cache of emails and names
$USER_EMAILS = array("u0" => "webmaster@pwg.org");
$USER_NAMES = array("u0" => "Webmaster");
$USER_ORGS = array("u0" => "Printer Working Group");

class user
{
  //
  // Instance variables...
  //

  var $id;
  var $status;
  var $email, $email_valid;
  var $name, $name_valid;
  var $organization_id, $organization_id_valid;
  var $oldhash, $oldhash_valid;
  var $hash, $hash_valid;
  var $is_admin, $is_editor, $is_member, $is_reviewer, $is_submitter;
  var $timezone, $timezone_valid;
  var $itemsperpage, $itemsperpage_valid;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'user::user()' - Create a user object.
  //

  function				// O - New user object
  user($id = 0)				// I - ID, if any
  {
    if ($id> 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'user::clear()' - Initialize a new a user object.
  //

  function
  clear()
  {
    global $SITE_TIMEZONE;

    $this->id              = 0;
    $this->status          = USER_STATUS_PENDING;
    $this->email           = "";
    $this->name            = "";
    $this->organization_id = 0;
    $this->oldhash         = "";
    $this->hash            = "";
    $this->is_admin        = 0;
    $this->is_editor       = 0;
    $this->is_member       = 0;
    $this->is_reviewer     = 0;
    $this->is_submitter    = 0;
    $this->timezone        = $SITE_TIMEZONE;
    $this->itemsperpage    = 10;
    $this->create_date     = "";
    $this->create_id       = 0;
    $this->modify_date     = "";
    $this->modify_id       = 0;
  }


  //
  // 'user::delete()' - Delete (set status to deleted) a user object.
  //

  function
  delete()
  {
    db_query("UPDATE user SET status = 3 WHERE id=?", array($this->id));
  }


  //
  // 'user::form()' - Display a form for a user object.
  //

  function
  form($options = "")			// I - Page options
  {
    global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_NAME, $PHP_SELF, $html_path;


    if ($this->id <= 0)
      $action = "Create User";
    else
      $action = "Save Changes";

    html_form_start("$PHP_SELF?U$this->id$options");

    html_form_field_start("name", "Display Name", $this->name_valid);
    html_form_text("name", "John Doe", $this->name);
    html_form_field_end();

    html_form_field_start("organization_id", "Organization Name", $this->organization_id_valid);
    organization_select("organization_id", $this->organization_id, "None", "", "Other...", "other_organization");
    html_form_field_end();

    html_form_field_start("account_email", "EMail", $this->email_valid);
    html_form_email("account_email", "name@example.com", $this->email);
    html_form_field_end();

    if ($LOGIN_IS_ADMIN && $LOGIN_ID != $this->id)
      $label = htmlspecialchars($LOGIN_NAME) . "'s Password";
    else
      $label = "Current Password";

    html_form_field_start("oldpassword", $label, $this->oldhash_valid);
    html_form_password("oldpassword");
    html_form_field_end();

    html_form_field_start("newpassword", "New Password", $this->hash_valid);
    html_form_password("newpassword");
    html_form_field_end();

    html_form_field_start("newpassword2", "New Password Again",
                          $this->hash_valid);
    html_form_password("newpassword2");
    html_form_field_end();

    // is_xxx
    html_form_field_start("", "Roles");
    if ($LOGIN_IS_ADMIN)
    {
      html_form_checkbox("is_admin", "Administrator", $this->is_admin);
      html_form_checkbox("is_editor", "Document Editor", $this->is_editor);
      html_form_checkbox("is_member", "PWG Member", $this->is_member);
      html_form_checkbox("is_reviewer", "IPP Everywhere Reviewer", $this->is_reviewer);
      html_form_checkbox("is_submitter", "IPP Everywhere Submitter", $this->is_submitter);
    }
    else
    {
      if ($this->is_admin)
        print("Administrator<br>\n");
      if ($this->is_editor)
        print("Document Editor<br>\n");
      if ($this->is_member)
        print("PWG Member<br>\n");
      if ($this->is_reviewer)
        print("IPP Everywhere Reviewer<br>\n");
      if ($this->is_submitter)
        print("IPP Everywhere Submitter<br>\n");

      if (!$this->is_admin && !$this->is_editor && !$this->is_member && !$this->is_reviewer && !$this->is_submitter)
        print("None");

      if (!$this->is_editor || !$this->is_member || !$this->is_reviewer || !$this->is_submitter)
	print("\n<a class=\"btn btn-default btn-xs\" href=\"${html_path}dynamo/request.php\">Request Additional Roles</a>");
    }
    html_form_field_end();

    // itemsperpage
    html_form_field_start("", "Items per Page");
    html_form_select("itemsperpage", array(10, 20, 50, 100, 1000), "", $this->itemsperpage, "", "value");
    html_form_field_end();

    // timezone
    html_form_field_start("timezone", "Timezone");
    print("<select name=\"timezone\">");

    $all       = timezone_identifiers_list();
    $continent = "";

    foreach ($all as $zone)
    {
      $temp = explode("/", $zone);
      if ($temp[0] != $continent)
      {
        if ($continent != "")
          print("</optgroup>");

        if ($temp[0] == "UTC")
        {
          $continent = "";
        }
        else
        {
	  $continent = htmlspecialchars($temp[0], ENT_QUOTES);
	  print("<optgroup label=\"$continent\">");
	}
      }

      $value = htmlspecialchars($zone, ENT_QUOTES);
      $label = htmlspecialchars(str_replace("_", " ", $zone), ENT_QUOTES);
      if ($zone == $this->timezone)
        print("<option value=\"$value\" selected>$label</option>");
      else
        print("<option value=\"$value\">$label</option>");
    }
    print("</select>");
    html_form_field_end();

    // Submit
    html_form_end(array("SUBMIT" => "+$action"));
  }


  //
  // 'user::load()' - Load a user object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    global $USER_COLUMNS;

    $this->clear();

    if (db_load($this, "user", $id, $USER_COLUMNS))
    {
      $this->id      = $id;
      $this->oldhash = $this->hash;

      return ($this->validate());
    }

    return (FALSE);
  }


  //
  // 'user::loadform()' - Load a user object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST, $LOGIN_ID, $LOGIN_IS_ADMIN, $REQUEST_METHOD;


    if (!html_form_validate())
      return (FALSE);

    if ($LOGIN_IS_ADMIN)
    {
      if (array_key_exists("status", $_POST))
	$this->status = (int)$_POST["status"];

      if (array_key_exists("is_admin", $_POST))
        $this->is_admin = 1;
      else
        $this->is_admin = 0;

      if (array_key_exists("is_editor", $_POST))
        $this->is_editor = 1;
      else
        $this->is_editor= 0;

      if (array_key_exists("is_member", $_POST))
        $this->is_member = 1;
      else
        $this->is_member= 0;

      if (array_key_exists("is_reviewer", $_POST))
        $this->is_reviewer = 1;
      else
        $this->is_reviewer = 0;

      if (array_key_exists("is_submitter", $_POST))
        $this->is_submitter = 1;
      else
        $this->is_submitter = 0;
    }

    if (array_key_exists("name", $_POST))
      $this->name = trim($_POST["name"]);

    if (array_key_exists("organization_id", $_POST))
      $this->organization_id = (int)$_POST["organization_id"];

    if (array_key_exists("other_organization", $_POST))
      $other_organization = trim($_POST["other_organization"]);
    else
      $other_organization = "";

    if ($this->organization_id < 0 && $other_organization != "")
    {
      if ($org_id = organization_lookup($other_organization))
	$this->organization_id = $org_id;
      else
      {
	$org = new organization();
	$org->name = $other_organization;
	if ($org->save())
	  $this->organization_id = $org->id;
      }
    }

    if (array_key_exists("account_email", $_POST))
      $this->email = trim($_POST["account_email"]);

    if (array_key_exists("oldpassword", $_POST))
      $oldpassword = trim($_POST["oldpassword"]);
    else
      $oldpassword = "";

    if (array_key_exists("newpassword", $_POST))
      $newpassword = trim($_POST["newpassword"]);
    else
      $newpassword = "";

    if (array_key_exists("newpassword2", $_POST))
      $newpassword2 = trim($_POST["newpassword2"]);
    else
      $newpassword2 = "";

    if (array_key_exists("timezone", $_POST))
      $this->timezone = trim($_POST["timezone"]);

    if (array_key_exists("itemsperpage", $_POST))
      $this->itemsperpage = (int)$_POST["itemsperpage"];

    $valid = $this->validate();

    if ($LOGIN_IS_ADMIN && $LOGIN_ID != $this->id)
    {
      $results    = db_query("SELECT hash FROM user WHERE id=?", array($LOGIN_ID));
      $row        = db_next($results);
      $match_hash = $row["hash"];
    }
    else
      $match_hash = $this->oldhash;

    if ($REQUEST_METHOD == "POST" &&
        auth_hash($oldpassword, $match_hash) != $match_hash)
    {
      $this->oldhash_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->oldhash_valid = TRUE;

    if ($newpassword != "" && $newpassword == $newpassword2 &&
        validate_password($newpassword))
      $this->password($newpassword);
    else if ($newpassword != "")
    {
      $this->hash       = "";
      $this->hash_valid = FALSE;
      $valid = FALSE;
    }

    return ($valid);
  }


  //
  // 'user::password()' - Set the hash field using a password.
  //

  function
  password($pass = "")			// I - Password string
  {
    // Map blank passwords to 20 random characters...
    if ($pass == "")
      $pass = openssl_random_pseudo_bytes(20);

    // Create the hash string that is stored in the database...
    $this->hash = auth_hash($pass);
  }


  //
  // 'user::save()' - Save a user object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PHP_SELF, $USER_COLUMNS;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->hash == "")
      $this->hash = $this->oldhash;

    if ($this->id > 0)
      return (db_save($this, "user", $this->id, $USER_COLUMNS));

    $this->create_date = $this->modify_date;
    $this->create_id   = $this->modify_id;

    if (($id = db_create($this, "user", $USER_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }


  //
  // 'user::validate()' - Validate the current user object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    $valid = TRUE;

    $this->oldhash_valid = TRUE;

    if (!validate_email($this->email))
    {
      $this->email_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->email_valid = TRUE;

    if ($this->name == "")
    {
      $this->name_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->name_valid = TRUE;

    $this->organization_id_valid = TRUE;

    if ($this->hash == "")
    {
      $this->hash_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->hash_valid = TRUE;

    if ($this->itemsperpage < 1)
    {
      $this->itemsperpage_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->itemsperpage_valid = TRUE;

    if ($this->timezone == "")
    {
      $this->timezone_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->timezone_valid = TRUE;

    return ($valid);
  }
}


//
// 'user_email()' - Return the email address of a user.
//

function				// O - Email address
user_email($id)				// I - User ID
{
  global $USER_EMAILS;

  $id = (int)$id;

  if (array_key_exists("u$id", $USER_EMAILS))
    return ($USER_EMAILS["u$id"]);

  $result = db_query("SELECT email FROM user WHERE id=?", array($id));
  if ($row = db_next($result))
    $email = $row["email"];
  else
    $email = "";

  $USER_EMAILS["u$id"] = $email;

  return ($email);
}


//
// 'user_name()' - Return the (HTML safe) display name of a user.
//

function				// O - Display name
user_name($id)				// I - User ID
{
  global $USER_NAMES;


  $id = (int)$id;

  if (array_key_exists("u$id", $USER_NAMES))
    return ($USER_NAMES["u$id"]);

  $result = db_query("SELECT name FROM user WHERE id=?", array($id));
  if ($row = db_next($result))
    $name = htmlspecialchars($row["name"], ENT_QUOTES);
  else
    $name = "";

  $USER_NAMES["u$id"] = $name;

  return ($name);
}


//
// 'user_organization()' - Return the (HTML safe) organization of a user.
//

function				// O - Organization name
user_organization($id)			// I - User ID
{
  global $USER_ORGS;


  $id = (int)$id;

  if (!array_key_exists("u$id", $USER_ORGS))
  {
    $result = db_query("SELECT organization_id FROM user WHERE id=?", array($id));
    if ($row = db_next($result))
      $organization_id = (int)$row["organization_id"];
    else
      $organization = 0;

    $USER_ORGS["u$id"] = organization_name($organization_id);
  }

  return ($USER_ORGS["u$id"]);
}


//
// 'user_search()' - Get a list of user objects.
//

function				// O - Array of user IDs
user_search($search = "",		// I - Search string
            $organization_id = -1,	// I - Organization
            $order = "")		// I - Order fields
{
  global $USER_COLUMNS;

  if ($organization_id >= 0)
    $keyvals = array("organization_id" => $organization_id);
  else
    $keyvals = null;

  return (db_search("user", $USER_COLUMNS, $keyvals, $search, $order));
}


//
// 'user_select()' - Show the user/developer selection control.
//

function
user_select(
    $formname = "assigned_id",		// I - Form name to use
    $id = 0,				// I - Currently selected user, if any
    $which = USER_SELECT_MEMBER,	// I - Which kind of user to select?
    $any_id = "",			// I - Allow "any user"?
    $prefix = "",			// I - Prefix on values
    $except_organization_id = 0)	// I - Organization to exclude, if any
{
  global $USER_NAMES, $USER_ORGS;


  print("<select name=\"$formname\">");

  if ($any_id)
    print("<option value=\"0\">$prefix$any_id</option>");

  $where  = "WHERE";
  $qprefix = " (";
  if ($which & USER_SELECT_ADMIN)
  {
    $where  .= "$qprefix is_admin = 1";
    $qprefix = " OR";
  }
  if ($which & USER_SELECT_EDITOR)
  {
    $where  .= "$qprefix is_editor = 1";
    $qprefix = " OR";
  }
  if ($which & USER_SELECT_MEMBER)
  {
    $where  .= "$qprefix is_member = 1";
    $qprefix = " OR";
  }
  if ($which & USER_SELECT_REVIEWER)
  {
    $where  .= "$qprefix is_reviewer = 1";
    $qprefix = " OR";
  }
  if ($where != "WHERE")
    $where .= ") AND";
  if ($except_organization_id > 0)
    $where .= " organization_id <> $except_organization_id AND";

  $results = db_query("SELECT id, name, organization_id FROM user $where status = 2 "
		     ."ORDER BY name, organization_id");
  while ($row = db_next($results))
  {
    $uid          = $row["id"];
    $name         = htmlspecialchars($row["name"]);
    $organization = htmlspecialchars(organization_name($row["organization_id"]));

    if ($organization != "" && !preg_match("/^Individual:/i", $organization))
      $label = "$name ($organization)";
    else
      $label = $name;

    if ($uid == $id)
      print("<option value=\"$uid\" selected>$prefix$label</option>");
    else
      print("<option value=\"$uid\">$prefix$label</option>");

    $USER_NAMES["u$row[id]"] = $name;
    $USER_ORGS["u$row[id]"]  = $organization;
  }

  print("</select>");
}
?>
