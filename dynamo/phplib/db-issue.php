<?php
//
// Class for the issue table.
//

include_once "db-comment.php";
include_once "db-document.php";
include_once "db-workgroup.php";


$ISSUE_COLUMNS = array(
  "parent_id" => PDO::PARAM_INT,
  "workgroup_id" => PDO::PARAM_INT,
  "document_id" => PDO::PARAM_INT,
  "status" => PDO::PARAM_INT,
  "priority" => PDO::PARAM_INT,
  "title" => PDO::PARAM_STR,
  "assigned_id" => PDO::PARAM_INT,
  "create_date" => PDO::PARAM_STR,
  "create_id" => PDO::PARAM_INT,
  "modify_date" => PDO::PARAM_STR,
  "modify_id" => PDO::PARAM_INT
);

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
  ISSUE_PRIORITY_UNASSIGNED => "Unassigned",
  ISSUE_PRIORITY_CRITICAL => "Critical",
  ISSUE_PRIORITY_HIGH => "High",
  ISSUE_PRIORITY_MODERATE => "Moderate",
  ISSUE_PRIORITY_LOW => "Low",
  ISSUE_PRIORITY_RFE => "Enhancement"
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

    if ($this->id > 0)
    {
      // create_id/date
      html_form_field_start("create_date", "Submitted");
      print(html_date($this->create_date) . " by " . user_name($this->create_id));
      html_form_field_end();

      // modify_id/date
      if ($this->create_date != $this->modify_date)
      {
	html_form_field_start("modify_date", "Last Updated");
	print(html_date($this->modify_date) . " by " . user_name($this->modify_id));
	html_form_field_end();
      }
    }

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
      print($ISSUE_STATUS_SHORT[$this->status]);
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

      html_form_field_start("comments", "Description", $contents != "");
      html_form_text("contents", "Detailed description of issue.", $contents, "", 12);
      html_form_field_end();
    }

    // assigned_id
    html_form_field_start("assigned_id", "Assigned To", $this->assigned_id_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $this->assigned_id)
      user_select("assigned_id", $this->assigned_id, USER_SELECT_MEMBER | USER_SELECT_EDITOR, "Nobody");
    else if ($this->assigned_id > 0)
      print(user_name($this->assigned_id));
    else
      print("<em>Unassigned</em>");
    html_form_field_end();

    // Submit
    if ($LOGIN_ID > 0)
      html_form_buttons(array("SUBMIT" => "+$action"));

    // Attachements and discussion...
    if ($this->id > 0)
    {
      print("<h2>Discussion</h2>\n");

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
      }
      else
	print("<p><a class=\"btn btn-default\" href=\"$html_login_url\">Login to Post Comment</a></p>\n"
	     ."</div>\n");

      $matches = comment_search("issue_$this->id", "", "-id");
      foreach ($matches as $id)
      {
	$comment  = new comment($id);
	$name     = user_name($comment->create_id);
	$contents = html_text($comment->contents);
	$date     = html_date($comment->create_date);

	print("<h3><a name=\"C$id\">$name <small>$date</small></a></h3>\n"
	     ."<p>$contents</p>\n");
      }
    }

    if ($LOGIN_ID > 0)
      html_form_end();
    else
      print("</div>\n");
  }


  //
  // 'issue::load()' - Load an issue object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    global $ISSUE_COLUMNS;

    $this->clear();

    if (!db_load($this, "issue", $id, $ISSUE_COLUMNS))
      return (FALSE);

    $this->id = $id;

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
    global $ISSUE_PRIORITY_SHORT, $ISSUE_STATUS_SHORT;
    global $SITE_EMAIL, $SITE_HOSTNAME, $SITE_URL;


    // Send the email to either the assigned user or create user, depending
    // on who modified the Issue...
    if ($this->assigned_id == 0)
    {
      $workgroup = new workgroup($this->workgroup_id);
      if ($workgroup->id && $workgroup->id == $this->workgroup_id)
        $to = $workgroup->list;
      else
        $to = $SITE_EMAIL;
    }
    else if ($this->modify_id != $this->assigned_id)
      $to = user_email($this->assigned_id);
    else
      $to = user_email($this->create_id);

    $from = user_email($this->modify_id);

    if ($this->status >= ISSUE_STATUS_ACTIVE)
      $replyto = "noreply@$SITE_HOSTNAME";
    else
      $replyto = $SITE_EMAIL;

    $document = new document($this->document_id);
    if ($document->series == 5108)
      $docname = sprintf("PWG5108.%02d", $document->number);
    else if ($document->series > 0)
      $docname = sprintf("PWG%d.%d", $document->series, $document->number);
    else if (strlen($document->title) > 20)
      $docname = substr($document->title, 0, 17) . "...";
    else
      $docname = $document->title;

    // Setup the message and headers...
    $subject  = "${what}[$docname] Issue #$this->id: $this->title";
    $headers  = "From: $from\n"
	       ."Reply-To: $replyto\n";

    if ($this->status >= ISSUE_STATUS_ACTIVE)
      $message  = "DO NOT REPLY TO THIS MESSAGE.  INSTEAD, POST ANY RESPONSES TO "
		 ."THE LINK BELOW.\n\n";
    else
      $message = "";

    $message .= "[Issue " . $ISSUE_STATUS_SHORT[$this->status] . "]\n"
	       ."\n"
	       . wordwrap(trim($contents)) . "\n"
	       ."\n"
	       ."Link: ${SITE_URL}/issues/$this->id\n";

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
    global $ISSUE_COLUMNS, $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
      return (db_save($this, "issue", $this->id, $ISSUE_COLUMNS));

    $this->create_date = $this->modify_date;
    $this->create_id   = $this->modify_id;

    if (($id = db_create($this, "issue", $ISSUE_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

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

    if ($document->id != $this->document_id || $this->document_id == 0)
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
	     $document_id = 0,		// I - Which document
	     $whose = 0)		// I - Whose
{
  global $ISSUE_COLUMNS, $LOGIN_EMAIL, $LOGIN_IS_ADMIN, $LOGIN_ID;


  $keyvals = array();

  if ($priority > 0)
    $keyvals["priority"] = $priority;

  if ($status > ISSUE_STATUS_ALL_WILDCARD)
    $keyvals["status"] = $status;
  else if ($status == ISSUE_STATUS_CLOSED_WILDCARD) // Show closed
    $keyvals["status>="] = ISSUE_STATUS_RESOLVED;
  else if ($status == ISSUE_STATUS_OPEN_WILDCARD) // Show open
    $keyvals["status<="] = ISSUE_STATUS_ACTIVE;

  if ($document_id)
    $keyvals["document_id"] = $document_id;

  if ($whose)
  {
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR)
      $keyvals["assigned_id"] = array(0,$LOGIN_ID);
    else if ($LOGIN_ID != 0)
      $keyvals["create_id"] = $LOGIN_ID;
  }

  if (sizeof($keyvals) == 0)
    $keyvals = null;

  return (db_search("issue", $ISSUE_COLUMNS, $keyvals, $search, $order));
}
?>
