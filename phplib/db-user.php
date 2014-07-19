<?php
//
// "$Id: db-user.php 122 2013-10-17 12:45:58Z msweet $"
//
// Class for the user table.
//
// Contents:
//
//   user::user()     - Create a user object.
//   user::clear()    - Initialize a new a user object.
//   user::delete()   - Delete (set status to deleted) a user object.
//   user::form()     - Display a form for a user object.
//   user::load()     - Load a user object.
//   user::loadform() - Load a user object from form data.
//   user::password() - Set the hash field using a password.
//   user::save()     - Save a user object.
//   user::validate() - Validate the current user object values.
//   user_email()     - Return the email address of a user.
//   user_name()      - Return the (HTML safe) display name of a user.
//   user_search()    - Get a list of user objects.
//   user_select()    - Show the user/developer selection control.
//

include_once "db.php";
include_once "site.php";


// Status values in user table...
define("USER_STATUS_BANNED", 0);
define("USER_STATUS_PENDING", 1);
define("USER_STATUS_ENABLED", 2);
define("USER_STATUS_DELETED", 3);


// Cache of emails and names
$USER_EMAILS = array("u0" => "webmaster@msweet.org");
$USER_NAMES = array("u0" => "Webmaster");


class user
{
  //
  // Instance variables...
  //

  var $id;
  var $status;
  var $email, $email_valid;
  var $name, $name_valid;
  var $oldhash, $oldhash_valid;
  var $hash, $hash_valid;
  var $is_admin;
  var $karma;
  var $timezone, $timezone_valid;
  var $itemsperpage, $itemsperpage_valid;
  var $question1, $question1_valid;
  var $answer1, $answer1_valid;
  var $question2, $question2_valid;
  var $answer2, $answer2_valid;
  var $question3, $question3_valid;
  var $answer3, $answer3_valid;
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

    $this->id           = 0;
    $this->status       = USER_STATUS_PENDING;
    $this->email        = "";
    $this->name         = "";
    $this->oldhash      = "";
    $this->hash         = "";
    $this->is_admin     = 0;
    $this->karma        = 0;
    $this->timezone     = $SITE_TIMEZONE;
    $this->itemsperpage = 10;
    $this->create_date  = "";
    $this->create_id    = 0;
    $this->modify_date  = "";
    $this->modify_id    = 0;
  }


  //
  // 'user::delete()' - Delete (set status to deleted) a user object.
  //

  function
  delete()
  {
    db_query("UPDATE user SET status = 3 WHERE id=$this->id");
  }


  //
  // 'user::form()' - Display a form for a user object.
  //

  function
  form($options = "")			// I - Page options
  {
    global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_NAME, $PHP_SELF;


    if ($this->id <= 0)
      $action = "Create User";
    else
      $action = "Save Changes";

    html_form_start("$PHP_SELF?U$this->id$options");

    html_form_field_start("name", "Display Name", $this->name_valid);
    html_form_text("name", "John Doe", $this->name);
    html_form_field_end();

    html_form_field_start("email", "EMail", $this->email_valid);
    html_form_email("email", "name@example.com", $this->email);
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

/*
    // questionN, answerN
    html_form_field_start("", "Security Questions",
                          $this->question1_valid && $this->answer1_valid &&
			  $this->question2_valid && $this->answer2_valid &&
			  $this->question3_valid && $this->answer3_valid);
    print("Question 1: ");
    html_form_text("question1", "", $this->question1);
    print("<br>&nbsp;&nbsp;&nbsp;&nbsp;Answer: ");
    html_form_text("answer1", "", $this->answer1);

    print("<br>Question 2: ");
    html_form_text("question2", "", $this->question2);
    print("<br>&nbsp;&nbsp;&nbsp;&nbsp;Answer: ");
    html_form_text("answer2", "", $this->answer2);

    print("<br>Question 3: ");
    html_form_text("question3", "", $this->question3);
    print("<br>&nbsp;&nbsp;&nbsp;&nbsp;Answer: ");
    html_form_text("answer3", "", $this->answer3);
    html_form_field_end();
*/

    // is_admin, karma, itemsperpage
    html_form_field_start("", "Miscellaneous");

    if ($LOGIN_IS_ADMIN)
    {
      html_form_checkbox("is_admin", "Admin User", $this->is_admin);
      print("<br>\nKarma: ");
      html_form_number("karma", "", $this->karma);
    }
    else
    {
      if ($this->is_admin)
        print("Admin User<br>\n");

      print("Karma: " . htmlspecialchars($this->karma));
    }

    print("<br>Items/Page:&nbsp;");
    html_form_select("itemsperpage", array(10, 20, 50, 100, 1000), "",
                     $this->itemsperpage, "", "value");
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
    html_form_end(array("SUBMIT" => $action));
  }


  //
  // 'user::load()' - Load a user object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    $this->clear();

    $result = db_query("SELECT * FROM user WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);
    $this->id           = $row["id"];
    $this->status       = $row["status"];
    $this->email        = $row["email"];
    $this->name         = $row["name"];
    $this->oldhash      = $row["hash"];
    $this->hash         = $row["hash"];
    $this->is_admin     = $row["is_admin"];
    $this->karma        = $row["karma"];
    $this->timezone     = $row["timezone"];
    $this->itemsperpage = $row["itemsperpage"];
    $this->question1    = $row["question1"];
    $this->answer1      = $row["answer1"];
    $this->question2    = $row["question2"];
    $this->answer2      = $row["answer2"];
    $this->question3    = $row["question3"];
    $this->answer3      = $row["answer3"];
    $this->create_date  = $row["create_date"];
    $this->create_id    = $row["create_id"];
    $this->modify_date  = $row["modify_date"];
    $this->modify_id    = $row["modify_id"];

    db_free($result);

    return ($this->validate());
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

      if (array_key_exists("karma", $_POST))
	$this->karma = (int)$_POST["karma"];
    }

    if (array_key_exists("name", $_POST))
      $this->name = trim($_POST["name"]);

    if (array_key_exists("email", $_POST))
      $this->email = trim($_POST["email"]);

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

    if (array_key_exists("question1", $_POST))
      $this->question1 = trim($_POST["question1"]);

    if (array_key_exists("answer1", $_POST))
      $this->answer1 = trim($_POST["answer1"]);

    if (array_key_exists("question2", $_POST))
      $this->question2 = trim($_POST["question2"]);

    if (array_key_exists("answer2", $_POST))
      $this->answer2 = trim($_POST["answer2"]);

    if (array_key_exists("question3", $_POST))
      $this->question3 = trim($_POST["question3"]);

    if (array_key_exists("answer3", $_POST))
      $this->answer3 = trim($_POST["answer3"]);

    $valid = $this->validate();

    if ($LOGIN_IS_ADMIN && $LOGIN_ID != $this->id)
    {
      $results    = db_query("SELECT hash FROM user WHERE id = $LOGIN_ID");
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
    global $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->hash == "")
      $this->hash = $this->oldhash;

    if ($this->id> 0)
    {
      if (db_query("UPDATE user "
                  ." SET status = $this->status"
                  .", email = '" . db_escape($this->email) . "'"
                  .", name = '" . db_escape($this->name) . "'"
                  .", hash = '" . db_escape($this->hash) . "'"
                  .", is_admin = $this->is_admin"
                  .", karma = $this->karma"
                  .", timezone = '" . db_escape($this->timezone) . "'"
                  .", itemsperpage = $this->itemsperpage"
                  .", question1 = '" . db_escape($this->question1) . "'"
                  .", answer1 = '" . db_escape($this->answer1) . "'"
                  .", question2 = '" . db_escape($this->question2) . "'"
                  .", answer2 = '" . db_escape($this->answer2) . "'"
                  .", question3 = '" . db_escape($this->question3) . "'"
                  .", answer3 = '" . db_escape($this->answer3) . "'"
                  .", modify_date = '" . db_escape($this->modify_date) . "'"
                  .", modify_id = $this->modify_id"
                  ." WHERE id = $this->id") === FALSE)
        return (FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if (db_query("INSERT INTO user VALUES"
                  ."(NULL"
                  .", $this->status"
                  .", '" . db_escape($this->email) . "'"
                  .", '" . db_escape($this->name) . "'"
                  .", '" . db_escape($this->hash) . "'"
                  .", $this->is_admin"
                  .", $this->karma"
                  .", '" . db_escape($this->timezone) . "'"
                  .", $this->itemsperpage"
                  .", '" . db_escape($this->question1) . "'"
                  .", '" . db_escape($this->answer1) . "'"
                  .", '" . db_escape($this->question2) . "'"
                  .", '" . db_escape($this->answer2) . "'"
                  .", '" . db_escape($this->question3) . "'"
                  .", '" . db_escape($this->answer3) . "'"
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

    if (($this->question1 != "") != ($this->answer1 != ""))
    {
      $this->question1_valid = FALSE;
      $this->answer1_valid   = FALSE;
      $valid = FALSE;
    }
    else
    {
      $this->question1_valid = TRUE;
      $this->answer1_valid   = TRUE;
    }

    if (($this->question2 != "") != ($this->answer2 != ""))
    {
      $this->question2_valid = FALSE;
      $this->answer2_valid   = FALSE;
      $valid = FALSE;
    }
    else
    {
      $this->question2_valid = TRUE;
      $this->answer2_valid   = TRUE;
    }

    if (($this->question3 != "") != ($this->answer3 != ""))
    {
      $this->question3_valid = FALSE;
      $this->answer3_valid   = FALSE;
      $valid = FALSE;
    }
    else
    {
      $this->question3_valid = TRUE;
      $this->answer3_valid   = TRUE;
    }

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

  $result = db_query("SELECT email FROM user WHERE id=$id;");
  if (db_count($result) == 1)
  {
    $row   = db_next($result);
    $email = $row["email"];
  }
  else
    $email = "";

  db_free($result);

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

  $result = db_query("SELECT name FROM user WHERE id=$id;");
  if (db_count($result) == 1)
  {
    $row  = db_next($result);
    $name = htmlspecialchars($row["name"], ENT_QUOTES);
  }
  else
    $name = "";

  db_free($result);

  $USER_NAMES["u$id"] = $name;

  return ($name);
}


//
// 'user_search()' - Get a list of user objects.
//

function				// O - Array of user objects
user_search($search = "",		// I - Search string
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
	$query .= "${subpre}email LIKE \"%$word%\"";

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
  $result  = db_query("SELECT id FROM user$query");
  $matches = array();

  while ($row = db_next($result))
    $matches[sizeof($matches)] = $row["id"];

  // Free the query result and return the array...
  db_free($result);

  return ($matches);
}


//
// 'user_select()' - Show the user/developer selection control.
//

function
user_select(
    $formname = "developer_id",		// I - Form name to use
    $id = 0,				// I - Currently selected user, if any
    $is_admin = TRUE,			// I - Require admin user?
    $any_id = "",			// I - Allow "any user"?
    $prefix = "")			// I - Prefix on values
{
  global $USER_NAMES;


  print("<select name=\"$formname\">");

  if ($any_id)
    print("<option value=\"0\">$prefix$any_id</option>");

  if ($is_admin)
    $where = "WHERE is_admin = 1 AND";
  else
    $where = "WHERE";

  $results = db_query("SELECT id, name FROM user $where status = 2 "
		     ."ORDER BY name");
  while ($row = db_next($results))
  {
    $uid  = $row["id"];
    $name = htmlspecialchars($row["name"]);

    if ($uid == $id)
      print("<option value=\"$uid\" selected>$prefix$name</option>");
    else
      print("<option value=\"$uid\">$prefix$name</option>");

    $USER_NAMES["u$row[id]"] = $name;
  }

  db_free($results);

  print("</select>");
}


//
// End of "$Id: db-user.php 122 2013-10-17 12:45:58Z msweet $".
//
?>
