<?php
//
// Class for the issue table.
//

include_once "db-comment.php";
include_once "db-document.php";
include_once "db-workgroup.php";


//
// Issue constants...
//

define("ISSUE_STATUS_ALL_WILDCARD", 0);
define("ISSUE_STATUS_CLOSED_WILDCARD", -1);
define("ISSUE_STATUS_OPEN_WILDCARD", -2);

define("ISSUE_STATUS_NEW", 1); // New/Unconfirmed
define("ISSUE_STATUS_PENDING", 2);
define("ISSUE_STATUS_ACTIVE", 3);
define("ISSUE_STATUS_RESOLVED", 4);
define("ISSUE_STATUS_UNRESOLVED", 5);

define("ISSUE_PRIORITY_ANY_WILDCARD", -1);

define("ISSUE_PRIORITY_UNASSIGNED", 0);
define("ISSUE_PRIORITY_CRITICAL", 1);
define("ISSUE_PRIORITY_HIGH", 2);
define("ISSUE_PRIORITY_MODERATE", 3);
define("ISSUE_PRIORITY_LOW", 4);
define("ISSUE_PRIORITY_RFE", 5);


//
// String definitions for Issue constants...
//

$ISSUE_STATUS_LIST = array(
  ISSUE_STATUS_ALL_WILDCARD => "Status: All",
  ISSUE_STATUS_CLOSED_WILDCARD => "Status: Closed",
  ISSUE_STATUS_OPEN_WILDCARD => "Status: Open",
  ISSUE_STATUS_RESOLVED => "Status: Resolved",
  ISSUE_STATUS_UNRESOLVED => "Status: Unresolved",
  ISSUE_STATUS_ACTIVE => "Status: Active",
  ISSUE_STATUS_PENDING => "Status: Pending",
  ISSUE_STATUS_NEW => "Status: Unconfirmed"
);

$ISSUE_STATUS_SHORT = array(
  ISSUE_STATUS_RESOLVED => "Resolved",
  ISSUE_STATUS_UNRESOLVED => "Unresolved",
  ISSUE_STATUS_ACTIVE => "Active",
  ISSUE_STATUS_PENDING => "Pending",
  ISSUE_STATUS_NEW => "Unconfirmed"
);

$ISSUE_PRIORITY_LIST = array(
  ISSUE_PRIORITY_ANY_WILDCARD => "Priority: Any",
  ISSUE_PRIORITY_UNASSIGNED => "Priority: UNKN",
  ISSUE_PRIORITY_CRITICAL => "Priority: CRIT",
  ISSUE_PRIORITY_HIGH => "Priority: HIGH",
  ISSUE_PRIORITY_MODERATE => "Priority: MOD",
  ISSUE_PRIORITY_LOW => "Priority: LOW",
  ISSUE_PRIORITY_RFE => "Priority: ENH",
);

$ISSUE_PRIORITY_SHORT = array(
  ISSUE_PRIORITY_UNASSIGNED => "UNKN",
  ISSUE_PRIORITY_CRITICAL => "CRIT",
  ISSUE_PRIORITY_HIGH => "HIGH",
  ISSUE_PRIORITY_MODERATE => "MOD",
  ISSUE_PRIORITY_LOW => "LOW",
  ISSUE_PRIORITY_RFE => "ENH"
);

$ISSUE_PRIORITY_LONG = array(
  ISSUE_PRIORITY_UNASSIGNED => "0 - Unassigned",
  ISSUE_PRIORITY_CRITICAL => "1 - Critical",
  ISSUE_PRIORITY_HIGH => "2 - High",
  ISSUE_PRIORITY_MODERATE => "3 - Moderate",
  ISSUE_PRIORITY_LOW => "4 - Low",
  ISSUE_PRIORITY_RFE => "5 - Enhancement"
);

class issue
{
  //
  // Instance variables...
  //

  var $id;
  var $parent_id, $parent_id_valid;
  var $workgroup_id, $workgroup_id_valid;
  var $document_id, $document_id_valid;
  var $status, $status_valid;
  var $priority, $priority_valid;
  var $title, $title_valid;
  var $assigned_id, $assigned_id_valid;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'issue::add_comment()' - Add a comment to an issue.
  //

  function				// O - FALSE on failure, text on success
  add_comment()
  {
    global $_POST, $LOGIN_EMAIL;


    // Get form data...
    if (array_key_exists("contents", $_POST))
      $contents = trim(str_replace("\r\n", "\n", $_POST["contents"]));
    else
      $contents = "";

    if ($contents == "")
      return ("");

    // Create a new text record...
    $comment           = new comment();
    $comment->ref_id   = "issue_$this->id";
    $comment->contents = $contents;

    // Save it...
    if (!$comment->save())
      return (FALSE);

    return ($contents);
  }


  //
  // 'issue::issue()' - Create an issue object.
  //

  function				// O - New Issue object
  issue($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'issue::clear()' - Initialize a new issue object.
  //

  function
  clear()
  {
    $this->id           = 0;
    $this->parent_id    = 0;
    $this->workgroup_id = 0;
    $this->document_id  = 0;
    $this->status       = ISSUE_STATUS_NEW;
    $this->priority     = ISSUE_PRIORITY_UNASSIGNED;
    $this->title        = "";
    $this->assigned_id  = 0;
    $this->create_date  = "";
    $this->create_id    = 0;
    $this->modify_date  = "";
    $this->modify_id    = 0;
  }


  //
  // 'issue::form()' - Display the form for an issue object.
  //

  function
  form($action,				// I - Action text
       $options = "")			// I - URL options
  {
    global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_NAME, $PHP_SELF, $REQUEST_METHOD;
    global $ISSUE_STATUS_SHORT, $ISSUE_PRIORITY_LONG, $ISSUE_MESSAGES;
    global $_POST, $html_path, $html_login_url;


    print("<h2>Information</h2>\n");

    if ($LOGIN_ID != 0)
      html_form_start("$PHP_SELF?U$this->id$options");
    else
      print("<div class=\"container\">\n");

    // parent_id
    html_form_field_start("parent_id", "Duplicate Of", $this->parent_id_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $this->assigned_id)
      html_form_number("parent_id", "Issue #", $this->parent_id, "", 4);
    else if ($this->parent_id > 0)
      print("<a href=\"{$html_path}dynamo/issues.php?L$this->parent_id$options\">Issue&nbsp;#$this->parent_id</a>");
    else
      print("None");
    html_form_field_end();

    // workgroup_id
    html_form_field_start("workgroup_id", "Workgroup");
    if ($this->workgroup_id != 0)
      print(workgroup_name($this->workgroup_id));
    else
      print("Unassigned");
    html_form_field_end();

    // document_id
    html_form_field_start("document_id", "Document", $this->document_id_valid);
    document_select("document_id", $this->document_id, "-- Choose --");
    html_form_field_end();

    // status
    html_form_field_start("status", "Status", $this->status_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $this->assigned_id)
      html_form_select("status", $ISSUE_STATUS_SHORT, "", $this->status);
    else
      print($ISSUE_STATUS_LONG[$this->status]);
    html_form_field_end();

    // priority
    html_form_field_start("priority", "Priority", $this->priority_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $this->assigned_id)
      html_form_select("priority", $ISSUE_PRIORITY_LONG, "Choose Priority",
                       $this->priority);
    else
      print($ISSUE_PRIORITY_LONG[$this->priority]);
    html_form_field_end();

    // create_id/date
    $date = html_date($this->create_date);
    $name = user_name($this->create_id);

    html_form_field_start("create_id", "Created By");
    print("$name ($date)");
    html_form_field_end();

    // title
    html_form_field_start("title", "Summary", $this->title_valid);
    html_form_text("title", "Short description of issue.", $this->title);
    html_form_field_end();

    if ($this->id == 0)
    {
      // contents
      if (array_key_exists("contents", $_POST))
	$contents = trim($_POST["contents"]);
      else
	$contents = "";

      html_form_field_start("comments", "Description", $this->contents_valid);
      html_form_text("contents", "Detailed description of issue.", $contents, "", 12);
      html_form_field_end();
    }

    // assigned_id
    html_form_field_start("assigned_id", "Assigned To");
    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $this->assigned_id)
      user_select("assigned_id", $this->assigned_id, USER_SELECT_MEMBER | USER_SELECT_EDITOR, "Nobody");
    else if ($this->assigned_id > 0)
      print(user_name($this->assigned_id));
    else
      print("<em>Unassigned</em>");
    html_form_field_end();

    // Submit
    html_form_buttons(array("SUBMIT" => "+$action"));

    // Attachements and discussion...
    if ($this->id > 0)
    {
      print("<h2>Discussion</h2>\n");

      $matches = comment_search("issue_$this->id");
      foreach ($matches as $id)
      {
	$comment  = new comment($id);
	$name     = user_name($comment->create_id);
	$contents = html_text($comment->contents);
	$date     = html_date($comment->create_date);

	print("<h3><a name=\"C$id\">$name <small>$date</small></a></h3>\n"
	     ."<p>$contents</p>\n");
      }

      if ($LOGIN_ID != 0)
      {
	if (array_key_exists("contents", $_POST))
	  $contents = trim($_POST["contents"]);
	else
	  $contents = "";

	print("<h3><a name=\"POST\">$LOGIN_NAME <small>Today</small></a></h3>\n"
	     ."<p>");

	html_form_text("contents", "Comment text.", $contents, "", 12);
	print("<br>\n");
	html_form_button("SUBMIT", "Post Comment");
	print("</p>\n");
	html_form_end();
      }
      else
	print("<p><a class=\"btn btn-default\" href=\"$html_login_url\">Login to Post Comment</a></p>\n"
	     ."</div>\n");
    }
  }


  //
  // 'issue::load()' - Load an issue object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    $this->clear();

    $result = db_query("SELECT * FROM issue WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);

    $this->id           = $row["id"];
    $this->parent_id    = $row["parent_id"];
    $this->workgroup_id = $row["workgroup_id"];
    $this->document_id  = $row["document_id"];
    $this->status       = $row["status"];
    $this->priority     = $row["priority"];
    $this->title        = $row["title"];
    $this->assigned_id  = $row["assigned_id"];
    $this->create_date  = $row["create_date"];
    $this->create_id    = $row["create_id"];
    $this->modify_date  = $row["modify_date"];
    $this->modify_id    = $row["modify_id"];

    db_free($result);

    return ($this->validate());
  }


  //
  // 'issue::loadform()' - Load an issue object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST, $LOGIN_ID, $LOGIN_IS_ADMIN;


    if (!html_form_validate())
      return (FALSE);

    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $this->assigned_id)
    {
      if (array_key_exists("parent_id", $_POST))
	$this->parent_id = (int)trim($_POST["parent_id"]);
    }

    if (array_key_exists("document_id", $_POST) &&
	preg_match("/^[0-9]+\$/", $_POST["document_id"]))
    {
      $this->document_id = (int)$_POST["document_id"];
      $document = new document($this->document_id);
      if ($document->id == $this->document_id)
        $this->workgroup_id = $document->workgroup_id;
    }

    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $this->assigned_id)
    {
      if (array_key_exists("status", $_POST))
	$this->status = (int)$_POST["status"];

      if (array_key_exists("priority", $_POST))
	$this->priority = (int)$_POST["priority"];
    }

    if (array_key_exists("title", $_POST))
      $this->title = trim($_POST["title"]);

    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $this->assigned_id)
    {
      if (array_key_exists("assigned_id", $_POST))
	$this->assigned_id = (int)$_POST["assigned_id"];
    }

    return ($this->validate());
  }


  //
  // 'issue::notify_users()' - Notify users of issue changes.
  //

  function
  notify_users($contents = "",		// I - Notification message, if any
	       $what = "Re: ")		// I - Reply or new message
  {
    global $ISSUE_PRIORITY_SHORT, $ISSUE_STATUS_LONG;
    global $SITE_EMAIL, $SITE_HOSTNAME, $SITE_URL;


    // Send the email to either the assigned user or create user, depending
    // on who modified the Issue...
    if ($this->assigned_id == 0)
      $to = $SITE_EMAIL;
    else if ($this->modify_id != $this->assigned_id)
      $to = user_email($this->assigned_id);
    else
      $to = user_email($this->create_id);

    $from = user_email($this->modify_id);

    if ($this->status >= ISSUE_STATUS_ACTIVE)
      $replyto = "noreply@$SITE_HOSTNAME";
    else
      $replyto = $SITE_EMAIL;

    // Setup the message and headers...
    $subject  = "${what}[" . $ISSUE_PRIORITY_SHORT["$this->priority"]
	       ."] Issue #$this->id: $this->title";
    $headers  = "From: $from\n"
	       ."Reply-To: $replyto\n";

    if ($this->status >= ISSUE_STATUS_ACTIVE)
      $message  = "DO NOT REPLY TO THIS MESSAGE.  INSTEAD, POST ANY RESPONSES TO "
		 ."THE LINK BELOW.\n\n";
    else
      $message = "";

    $message .= "[Issue " . substr($ISSUE_STATUS_LONG[$this->status], 4) . "]\n"
	       ."\n"
	       . wordwrap(trim($contents)) . "\n"
	       ."\n"
	       ."Link: ${SITE_URL}dynamo/issues.php?U$this->id\n";

    // Set message ID to track this bug...
    if ($this->create_date == $this->modify_date)
      $headers .= "Message-Id: <issue-$this->id@$SITE_HOSTNAME>\n";
    else
      $headers .= "In-Reply-To: <issue-$this->id@$SITE_HOSTNAME>\n";

    // Carbon copy create user, devel/bug lists, and interested addressees...
    if ($this->modify_id != $this->create_id)
      $headers .= "Cc: " . user_email($this->create_id) . "\n";

    if ($this->assigned_id != 0 && $this->status <= ISSUE_STATUS_UNRESOLVED)
    {
      // Carbon copy the email to the site address...
      $headers .= "Cc: $SITE_EMAIL\n";
    }

    $headers .= "Mime-Version: 1.0\n"
	       ."Content-Type: text/plain\n";

    // Send the email notification...
    mail($to, $subject, $message, $headers);
  }


  //
  // 'issue::save()' - Save an issue object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
    {
      return (db_query("UPDATE issue "
                      ." SET parent_id = $this->parent_id"
                      .", workgroup_id = $this->workgroup_id"
                      .", document_id = $this->document_id"
                      .", status = $this->status"
                      .", priority = $this->priority"
                      .", title = '" . db_escape($this->title) . "'"
                      .", assigned_id = $this->assigned_id"
                      .", modify_date = '" . db_escape($this->modify_date) . "'"
                      .", modify_id = $this->modify_id"
                      ." WHERE id = $this->id") !== FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if (db_query("INSERT INTO issue VALUES"
                  ."(NULL"
                  .", $this->parent_id"
                  .", $this->workgroup_id"
                  .", $this->document_id"
                  .", $this->status"
                  .", $this->priority"
                  .", '" . db_escape($this->title) . "'"
                  .", $this->assigned_id"
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
  // 'issue::validate()' - Validate the current Issue object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    $valid = TRUE;

    $document = new document($this->document_id);

    if ($document->id != $this->document_id)
    {
      $this->document_id_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->document_id_valid  = TRUE;

    if ($this->parent_id > 0)
    {
      $temp = new issue($this->parent_id);

      if ($temp->id != $this->parent_id)
      {
	$this->parent_id_valid = FALSE;
	$valid = FALSE;
      }
      else
	$this->parent_id_valid = TRUE;
    }
    else if ($this->parent_id < 0)
    {
      $this->parent_id_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->parent_id_valid = TRUE;

    if ($this->status < ISSUE_STATUS_NEW ||
        $this->status > ISSUE_STATUS_UNRESOLVED)
    {
      $this->status_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->status_valid = TRUE;

    if ($this->priority < ISSUE_PRIORITY_UNASSIGNED ||
        $this->priority > ISSUE_PRIORITY_RFE)
    {
      $this->priority_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->priority_valid = TRUE;

    if ($this->title == "")
    {
      $this->title_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->title_valid = TRUE;

    if ($this->assigned_id <= 0 && $this->status != ISSUE_STATUS_NEW)
    {
      $this->assigned_id_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->assigned_id_valid = TRUE;

    return ($valid);
  }
}


//
// 'issue_search()' - Get a list of issue IDs.
//

function				// O - Array of bug IDs
issue_search($search = "",		// I - Search buging
	     $order = "",		// I - Order fields
	     $priority = 0,		// I - Priority
	     $status = 0,		// I - Status
	     $whose = 0)		// I - Whose
{
  global $LOGIN_EMAIL, $LOGIN_IS_ADMIN, $LOGIN_ID;


  $query  = "";
  $prefix = " WHERE ";

  if ($priority > 0)
  {
    $query .= "${prefix}priority = $priority";
    $prefix = " AND ";
  }

  if ($status > ISSUE_STATUS_ALL_WILDCARD)
  {
    $query .= "${prefix}status = $status";
    $prefix = " AND ";
  }
  else if ($status == ISSUE_STATUS_CLOSED_WILDCARD) // Show closed
  {
    $query .= "${prefix}status >= " . ISSUE_STATUS_RESOLVED;
    $prefix = " AND ";
  }
  else if ($status == ISSUE_STATUS_OPEN_WILDCARD) // Show open
  {
    $query .= "${prefix}status <= " . ISSUE_STATUS_ACTIVE;
    $prefix = " AND ";
  }

  if ($whose)
  {
    if ($LOGIN_IS_ADMIN)
    {
      $query .= "${prefix}(assigned_id = 0 OR assigned_id = $LOGIN_ID)";
      $prefix = " AND ";
    }
    else if ($LOGIN_ID != 0)
    {
      $query .= "${prefix}(create_id = $LOGIN_ID OR assigned_id = $LOGIN_ID)";
      $prefix = " AND ";
    }
  }

  if ($search != "")
  {
    // Convert the search buging to an array of words...
    $words = html_search_words($search);

    // Loop through the array of words, adding them to the query...
    $query  .= "${prefix}(";
    $prefix = "";
    $next   = " OR";
    $logic  = "";

    foreach ($words as $word)
    {
      $word = db_escape($word);

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
      else if (substr($word, 0, 8) == "creator:")
      {
	// TODO: Map creator names to id's
	$id     = (int)substr($word, 8);
	$query .= "$prefix$logic create_id = $id";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 10) == "developer:")
      {
	// TODO: Map developer names to id's
	$id    = (int)substr($word, 10);
	$query .= "$prefix$logic assigned_id = $id";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 11) == "fixversion:")
      {
	$word   = db_escape(substr($word, 11));
	$query  .= "$prefix$logic fix_version LIKE \"$word%\"";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 7) == "number:")
      {
	$number = (int)substr($word, 7);
	$query  .= "$prefix$logic id = $number";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 6) == "text:")
      {
        // TODO: add search for comments
/*	$word   = db_escape(substr($word, 5));
	$query  .= "$prefix$logic contents LIKE \"%$word%\"";
	$prefix = $next;
	$logic  = '';*/
      }
      else if (substr($word, 0, 6) == "title:")
      {
	$word   = db_escape(substr($word, 6));
	$query  .= "$prefix$logic title LIKE \"%$word%\"";
	$prefix = $next;
	$logic  = '';
      }
      else
      {
	$query .= "$prefix$logic (";
	$subpre = "";

	if (preg_match("/^[0-9]+\$/", $word))
	{
	  $query .= "${subpre}id = $word";
	  $subpre = " OR ";
	}

	$word   = db_escape($word);
	$query .= "${subpre}title LIKE \"%$word%\")";
	$prefix = $next;
	$logic  = '';
      }
    }

    $query .= ")";
  }

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
  $result  = db_query("SELECT id FROM issue$query");
  $matches = array();

  while ($row = db_next($result))
    $matches[sizeof($matches)] = $row["id"];

  // Free the query result and return the array...
  db_free($result);

  return ($matches);
}
?>
