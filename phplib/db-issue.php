<?php
//
// "$Id: db-bug.php 141 2014-03-30 01:11:14Z msweet $"
//
// Class for the bug table.
//

include_once "db-comment.php";
include_once "db-file.php";
include_once "db-project.php";
include_once "db-projversion.php";


// Standard messages...
$BUG_MESSAGES = array(
  "Fixed in SVN" =>
      "Fixed in Subversion repository.",
  "No Support" =>
      "General support is not available via the bug form. Please post to the "
     ."project mailing list for general support.",
  "Old Bug" =>
      "This bug has not been updated by the submitter for two or more weeks "
     ."and has been closed. If the issue still requires resolution, please "
     ."re-submit a new bug.",
  "Unresolvable" =>
      "We are unable to resolve this problem with the information provided. "
     ."If you discover new information, please file a new bug referencing "
     ."this one."
);


//
// Bug constants...
//

define("BUG_STATUS_ALL", 0);
define("BUG_STATUS_CLOSED", -1);
define("BUG_STATUS_OPEN", -2);

define("BUG_STATUS_RESOLVED", 1);
define("BUG_STATUS_UNRESOLVED", 2);
define("BUG_STATUS_ACTIVE", 3);
define("BUG_STATUS_PENDING", 4);
define("BUG_STATUS_NEW", 5);

define("BUG_PRIORITY_ANY", -1);
define("BUG_PRIORITY_UNASSIGNED", 0);
define("BUG_PRIORITY_RFE", 1);
define("BUG_PRIORITY_LOW", 2);
define("BUG_PRIORITY_MODERATE", 3);
define("BUG_PRIORITY_HIGH", 4);
define("BUG_PRIORITY_CRITICAL", 5);


//
// String definitions for Bug constants...
//

$BUG_STATUS_LIST = array(
  BUG_STATUS_ALL => "Status: All",
  BUG_STATUS_CLOSED => "Status: Closed",
  BUG_STATUS_OPEN => "Status: Open",
  BUG_STATUS_RESOLVED => "Status: Resolved",
  BUG_STATUS_UNRESOLVED => "Status: Unresolved",
  BUG_STATUS_ACTIVE => "Status: Active",
  BUG_STATUS_PENDING => "Status: Pending",
  BUG_STATUS_NEW => "Status: New"
);

$BUG_STATUS_SHORT = array(
  BUG_STATUS_RESOLVED => "Resolved",
  BUG_STATUS_UNRESOLVED => "Unresolved",
  BUG_STATUS_ACTIVE => "Active",
  BUG_STATUS_PENDING => "Pending",
  BUG_STATUS_NEW => "New"
);

$BUG_STATUS_LONG = array(
  BUG_STATUS_RESOLVED => "1 - Resolved",
  BUG_STATUS_UNRESOLVED => "2 - Unresolved",
  BUG_STATUS_ACTIVE => "3 - Active",
  BUG_STATUS_PENDING => "4 - Pending",
  BUG_STATUS_NEW => "5 - New"
);

$BUG_PRIORITY_LIST = array(
  BUG_PRIORITY_ANY => "Priority: Any",
  BUG_PRIORITY_UNASSIGNED => "Priority: NA",
  BUG_PRIORITY_RFE => "Priority: RFE",
  BUG_PRIORITY_LOW => "Priority: LOW",
  BUG_PRIORITY_MODERATE => "Priority: MOD",
  BUG_PRIORITY_HIGH => "Priority: HIGH",
  BUG_PRIORITY_CRITICAL => "Priority: CRIT"
);

$BUG_PRIORITY_SHORT = array(
  BUG_PRIORITY_UNASSIGNED => "NA",
  BUG_PRIORITY_RFE => "RFE",
  BUG_PRIORITY_LOW => "LOW",
  BUG_PRIORITY_MODERATE => "MOD",
  BUG_PRIORITY_HIGH => "HIGH",
  BUG_PRIORITY_CRITICAL => "CRIT"
);

$BUG_PRIORITY_LONG = array(
  BUG_PRIORITY_UNASSIGNED => "0 - Unassigned",
  BUG_PRIORITY_RFE => "1 - Feature",
  BUG_PRIORITY_LOW => "2 - Low",
  BUG_PRIORITY_MODERATE => "3 - Moderate",
  BUG_PRIORITY_HIGH => "4 - High",
  BUG_PRIORITY_CRITICAL => "5 - Critical"
);

class bug
{
  //
  // Instance variables...
  //

  var $id;
  var $project_id, $project_id_valid;
  var $parent_id, $parent_id_valid;
  var $is_published;
  var $status, $status_valid;
  var $priority, $priority_valid;
  var $summary, $summary_valid;
  var $contents, $contents_valid;
  var $bug_version, $bug_version_valid;
  var $bug_revision, $bug_revision_valid;
  var $fix_version, $fix_version_valid;
  var $fix_revision, $fix_revision_valid;
  var $developer_id, $developer_id_valid;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'bug::add_file()' - Add a file to a Bug.
  //

  function				// O - FALSE on failure, filename on success
  add_file()
  {
    global $_FILES, $_POST, $LOGIN_EMAIL;


    // Grab form data...
    if (array_key_exists("file", $_FILES))
      $file = $_FILES["file"];
    else
      return ("");

    $filename = $file['name'];
    if (strlen($filename) < 1 || $filename[0] == '.' ||
	$filename[0] == '/' || $filename == "")
      return ("");

    // Get the source and destination filenames...
    $tmpname = $file['tmp_name'];
    $bugname = "files/bug$this->id/$filename";

    if (file_exists($bugname))
    {
      // Rename file to avoid conflicts...
      for ($i = 2; $i < 1000; $i ++)
      {
	if (preg_match("/.*\\..*/", $filename))
	  $temp = preg_replace("/([^\\.]*)\\.(.*)/",
			       "\\1_v$i.\\2", $filename);
	else
	  $temp = "${filename}_v$i";

	if (!file_exists("files/bug$this->id/$temp"))
	  break;
      }

      // Only support up to 1000 versions of the same file...
      if ($i >= 1000)
	return (FALSE);

      $filename = $temp;
      $bugname  = "files/bug$this->id/$filename";
    }

    // Make the attachment directory...
    if (!file_exists("files/bug$this->id"))
      mkdir("files/bug$this->id");

    // Copy the attachment...
    if (!copy($tmpname, $bugname))
      return (FALSE);

    // Create the file record...
    $file = new file();

    $file->ref_id       = "bug$this->id";
    $file->is_published = 1;
    $file->filename     = $filename;
    $file->version      = "";
    $file->md5          = md5_file("files/bug$this->id/$filename");
    $file->create_date  = $file->modify_date = db_datetime();
    $file->create_id    = $file->modify_id = $this->modify_id;

    // Save it...
    if (!$file->save())
      return (FALSE);

    return ($filename);
  }


  //
  // 'bug::add_text()' - Add text to a Bug.
  //

  function				// O - FALSE on failure, text on success
  add_text()
  {
    global $_POST, $LOGIN_EMAIL, $BUG_MESSAGES;


    // Get form data...
    $contents = "";

    if (array_key_exists("message", $_POST) &&
        array_key_exists($_POST["message"], $BUG_MESSAGES))
      $contents .= $BUG_MESSAGES[$_POST["message"]];

    if (array_key_exists("message", $_POST) && $_POST["message"] != "" &&
	array_key_exists("text", $_POST))
      $contents .= "\n\n";

    if (array_key_exists("text", $_POST))
      $contents .= $_POST["text"];

    $contents = trim(str_replace("\r\n", "\n", $contents));

    if ($contents == "")
      return ("");

    // Create a new text record...
    $comment = new comment();

    $comment->ref_id       = "bug$this->id";
    $comment->is_published = 1;
    $comment->contents     = $contents;

    // Save it...
    if (!$comment->save())
      return (FALSE);

    return ($contents);
  }


  //
  // 'bug::bug()' - Create a Bug object.
  //

  function				// O - New Bug object
  bug($id = 0)				// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'bug::clear()' - Initialize a new a Bug object.
  //

  function
  clear()
  {
    $this->id           = 0;
    $this->project_id   = -1;
    $this->parent_id    = 0;
    $this->is_published = 1;
    $this->status       = BUG_STATUS_NEW;
    $this->priority     = BUG_PRIORITY_UNASSIGNED;
    $this->summary      = "";
    $this->contents     = "";
    $this->bug_version  = "";
    $this->bug_revision = 0;
    $this->fix_version  = "";
    $this->fix_revision = 0;
    $this->developer_id = 0;
    $this->create_date  = "";
    $this->create_id    = 0;
    $this->modify_date  = "";
    $this->modify_id    = 0;
  }


  //
  // 'bug::delete()' - Delete a Bug object.
  //

  function
  delete()
  {
    db_query("DELETE FROM bug WHERE id=$this->id");
    db_query("DELETE FROM file WHERE ref_id='bug$this->id'");
    db_query("DELETE FROM comment WHERE ref_id='bug$this->id'");
    $this->clear();
  }


  //
  // 'bug::form()' - Display the form for a Bug object.
  //

  function
  form($action,				// I - Action text
       $options = "")			// I - URL options
  {
    global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_NAME, $PHP_SELF, $REQUEST_METHOD;
    global $BUG_STATUS_LONG, $BUG_PRIORITY_LONG, $BUG_MESSAGES;
    global $_POST, $_FILES;
    global $html_is_phone, $html_input_width, $html_textarea_width;


    print("<h2>Information</h2>\n");

    html_form_start("$PHP_SELF?U$this->id$options", FALSE, TRUE);

    // project_id
    if ($LOGIN_IS_ADMIN)
    {
      html_form_field_start("project_id", "Project", $this->project_id_valid);
      project_select("project_id", $this->project_id,
		     $this->id < 0 ? "Select Project" : "");
      html_form_field_end();
    }

    // parent_id
    html_form_field_start("parent_id", "Duplicate Of", $this->parent_id_valid);
    if ($LOGIN_IS_ADMIN)
      html_form_number("parent_id", "Bug #", $this->parent_id, "", 4);
    else if ($this->parent_id > 0)
      print("<a href=\"bugs.php?L$this->parent_id$options\">Bug "
	   ."#$this->parent_id</a>");
    else
      print("None");
    html_form_field_end();

    // is_published
    html_form_field_start("is_published", "Visibility");
    if ($LOGIN_IS_ADMIN || $this->id <= 0)
      html_select_is_published($this->is_published,
                               "Choose \"Private\" for security advisories.");
    else if ($this->is_published)
      print("Public");
    else
      print("Private");
    html_form_field_end();

    // status
    html_form_field_start("status", "Status", $this->status_valid);
    if ($LOGIN_IS_ADMIN)
      html_form_select("status", $BUG_STATUS_LONG, "", $this->status);
    else
      print($BUG_STATUS_LONG[$this->status]);
    html_form_field_end();

    // priority
    html_form_field_start("priority", "Priority", $this->priority_valid);
    if ($LOGIN_IS_ADMIN)
      html_form_select("priority", $BUG_PRIORITY_LONG, "Choose Priority",
                       $this->priority);
    else
      print($BUG_PRIORITY_LONG[$this->priority]);
    html_form_field_end();

    // bug_version
    html_form_field_start("bug_version", "Version", $this->bug_version_valid);
    projversion_select($this->project_id, "bug_version", $this->bug_version);
    html_form_field_end();

    // bug_revision
    html_form_field_start("bug_revision", "SVN Revision");
    html_form_number("bug_revision", "", $this->bug_revision);
    html_form_field_end();

    // create_id/date
    $date = html_date($this->create_date);
    $name = user_name($this->create_id);

    html_form_field_start("", "Created By");
    print("$name ($date)");
    html_form_field_end();

    // summary
    html_form_field_start("summary", "Summary", $this->summary_valid);
    html_form_text("summary", "Short description of bug.", $this->summary);
    html_form_field_end();

    // contents
    html_form_field_start("contents", "Description", $this->contents_valid);
    if ($this->id == 0)
      html_form_text("contents", "Detailed description of bug.", $this->contents, "", 12);
    else
      print(html_text($this->contents));
    html_form_field_end();

    // developer_id
    html_form_field_start("developer_id", "Assigned To");
    if ($LOGIN_IS_ADMIN)
      user_select("developer_id", $this->developer_id);
    else
      print(user_name($this->developer_id));
    html_form_field_end();

    // fix_version
    html_form_field_start("fix_version", "Fix Version",
                          $this->fix_version_valid);
    if ($LOGIN_IS_ADMIN)
      projversion_select($this->project_id, "fix_version", $this->fix_version,
                         "Select Version");
    else if ($this->fix_version != "")
      print(htmlspecialchars($this->fix_version, ENT_QUOTES));
    else
      print("Unassigned");
    html_form_field_end();

    // fix_revision
    html_form_field_start("fix_revision", "Fix Revision",
                          $this->fix_revision_valid);
    if ($LOGIN_IS_ADMIN)
      html_form_number("fix_revision", "", $this->fix_revision);
    else if ($this->fix_revision > 0)
      print("r$this->fix_revision");
    html_form_field_end();

    // Submit
    html_form_buttons(array("SUBMIT" => $action));

    // Attachements and discussion...
    print("<h2>Discussion</h2>\n");
    if ($this->id > 0)
    {
      if ($LOGIN_IS_ADMIN)
        $is_published = "";
      else
        $is_published = " AND is_published = 1";

      $matches = array();
      $results = db_query("SELECT id, create_date FROM file WHERE ref_id='bug$this->id'$is_published ORDER BY create_date;");
      while ($row = db_next($results))
        $matches["$row[create_date]F$row[id]"] = "F$row[id]";
      db_free($results);

      $results = db_query("SELECT id, create_date FROM comment WHERE ref_id='bug$this->id'$is_published ORDER BY create_date;");
      while ($row = db_next($results))
        $matches["$row[create_date]C$row[id]"] = "C$row[id]";
      db_free($results);

      ksort($matches);

      foreach ($matches as $key => $value)
      {
        if ($value[0] == "F")
        {
          $id       = (int)substr($value, 1);
	  $file     = new file($id);
	  $name     = user_name($file->create_id);
	  $date     = html_date($file->create_date);
	  $filename = htmlspecialchars($file->filename, ENT_QUOTES);
	  $pathname = htmlspecialchars($file->pathname, ENT_QUOTES);

	  if (!$file->is_published)
	    $filename = "<del>$filename</del>";

	  print("<h3><a name=\"C$id\">");
	  if ($LOGIN_IS_ADMIN)
	  {
	    if ($file->is_published)
	      html_form_button("HIDE_FILE_ID_$id", "-Hide");
	    else
	      html_form_button("SHOW_FILE_ID_$id", "-Show");
	  }

	  print("$name <small>$date</small></a></h3>\n"
	       ."<p><a href=\"$pathname\">$filename</a> ($file->filesize)</p>\n");
	}
	else
	{
          $id       = (int)substr($value, 1);
	  $comment  = new comment($id);
	  $name     = user_name($comment->create_id);
	  $contents = html_text($comment->contents);
	  $date     = html_date($comment->create_date);

	  if (!$comment->is_published)
	    $contents = "<del>$contents</del>";

	  print("<h3><a name=\"C$id\">");
	  if ($LOGIN_IS_ADMIN)
	  {
	    if ($row["is_published"])
	      html_form_button("HIDE_COMMENT_ID_$id", "-Hide");
	    else
	      html_form_button("SHOW_COMMENT_ID_$id", "-Show");
	  }
	  print("$name <small>$date</small></a></h3>\n"
	       ."<p>$contents</p>\n");
	}
      }
    }

    if (array_key_exists("text", $_POST))
      $text = $_POST["text"];
    else
      $text = "";

    print("<h3><a name=\"POST\">$LOGIN_NAME <small>Today</small></a></h3>\n"
	 ."<p>");

    if ($LOGIN_IS_ADMIN)
    {
      if (array_key_exists("message", $_POST))
	$message = trim($_POST["message"]);
      else
	$message = "";

      html_form_select("message", $BUG_MESSAGES,
		       "--- Pick a Standard Message ---", $message);
      print("<br>\n");
    }

    html_form_text("text", "Comment text.", $text, "", 12);
    print("<br>\nAttach File: ");
    html_form_file("file");
    print("<br>\n");
    html_form_button("SUBMIT", "-$action");
    print("</p>\n");

    html_form_end();
  }


  //
  // 'bug::load()' - Load a Bug object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    $this->clear();

    $result = db_query("SELECT * FROM bug WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);

    $this->id           = $row["id"];
    $this->project_id   = $row["project_id"];
    $this->parent_id    = $row["parent_id"];
    $this->is_published = $row["is_published"];
    $this->status       = $row["status"];
    $this->priority     = $row["priority"];
    $this->summary      = $row["summary"];
    $this->contents     = $row["contents"];
    $this->bug_version  = $row["bug_version"];
    $this->bug_revision = $row["bug_revision"];
    $this->fix_version  = $row["fix_version"];
    $this->fix_revision = $row["fix_revision"];
    $this->developer_id = $row["developer_id"];
    $this->create_date  = $row["create_date"];
    $this->create_id    = $row["create_id"];
    $this->modify_date  = $row["modify_date"];
    $this->modify_id    = $row["modify_id"];

    db_free($result);

    return ($this->validate());
  }


  //
  // 'bug::loadform()' - Load a Bug object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST, $LOGIN_IS_ADMIN;


    if (!html_form_validate())
      return (FALSE);

    if ($LOGIN_IS_ADMIN)
    {
      if (array_key_exists("parent_id", $_POST))
	$this->parent_id = (int)trim($_POST["parent_id"]);

      if (array_key_exists("project_id", $_POST) &&
	  preg_match("/^p[0-9]+\$/", $_POST["project_id"]))
	$this->project_id = (int)substr($_POST["project_id"], 1);
    }

    if (array_key_exists("is_published", $_POST))
      $this->is_published = (int)$_POST["is_published"];

    if ($LOGIN_IS_ADMIN)
    {
      if (array_key_exists("status", $_POST))
	$this->status = (int)$_POST["status"];

      if (array_key_exists("priority", $_POST))
	$this->priority = (int)$_POST["priority"];
    }

    if (array_key_exists("summary", $_POST))
      $this->summary = trim($_POST["summary"]);

    if ($this->id == 0 || $LOGIN_IS_ADMIN)
    {
      if (array_key_exists("contents", $_POST))
	$this->contents = trim($_POST["contents"]);
    }

    if (array_key_exists("bug_version", $_POST))
      $this->bug_version = trim($_POST["bug_version"]);

    if (strpos($this->bug_version, "-current") !== FALSE &&
        array_key_exists("bug_revision", $_POST))
      $this->bug_revision = (int)$_POST["bug_revision"];

    if ($LOGIN_IS_ADMIN)
    {
      if (array_key_exists("fix_version", $_POST))
	$this->fix_version = trim($_POST["fix_version"]);

      if (strpos($this->fix_version, "-current") !== FALSE &&
          array_key_exists("fix_revision", $_POST))
	$this->fix_revision = (int)$_POST["fix_revision"];

      if (array_key_exists("developer_id", $_POST))
	$this->developer_id = (int)$_POST["developer_id"];
    }

    return ($this->validate());
  }


  //
  // 'bug::notify_users()' - Notify users of Bug changes.
  //

  function
  notify_users($contents = "",		// I - Notification message, if any
	       $file = "",		// I - Attached file, if any
	       $what = "Re: ")		// I - Reply or new message
  {
    global $BUG_PRIORITY_SHORT, $BUG_STATUS_LONG;
    global $SITE_EMAIL, $SITE_HOSTNAME, $SITE_URL, $SITE_MODULE;


    // Make sure contents is non-empty for file attachments...
    if ($contents == "" && $file != "")
      $contents = "Attached file \"$file\".";

    // Send the email to either the manager user or create user, depending
    // on who modified the Bug...
    if ($this->modify_id != $this->developer_id)
      $to = user_email($this->developer_id);
    else
      $to = user_email($this->create_id);

    $from = user_email($this->modify_id);

    if ($this->status >= BUG_STATUS_ACTIVE)
      $replyto = "noreply@$SITE_HOSTNAME";
    else
      $replyto = $SITE_EMAIL;

    // Setup the message and headers...
    $subject  = "${what}[" . $BUG_PRIORITY_SHORT["$this->priority"]
	       ."] Bug #$this->id: $this->summary";
    $headers  = "From: $from\n"
	       ."Reply-To: $replyto\n";

    if ($this->status >= BUG_STATUS_ACTIVE)
      $message  = "DO NOT REPLY TO THIS MESSAGE.  INSTEAD, POST ANY RESPONSES TO "
		 ."THE LINK BELOW.\n\n";
    else
      $message = "";

    $message .= "[Bug " . substr($BUG_STATUS_LONG[$this->status], 4) . "]\n"
	       ."\n"
	       . wordwrap(trim($contents)) . "\n"
	       ."\n"
	       ."Link: ${SITE_URL}bugs.php?U$this->id\n"
	       ."Version: $this->bug_version\n";

    if ($this->fix_version != "")
    {
      // Add fix version
      $message .= "Fix Version: $this->fix_version";
      if ($this->fix_revision != 0)
	$message .= " (r$this->fix_revision)";
      $message .= "\n";
    }

    // Set message ID to track this bug...
    if ($this->create_date == $this->modify_date)
      $headers .= "Message-Id: <bug-$this->id@$SITE_HOSTNAME>\n";
    else
      $headers .= "In-Reply-To: <bug-$this->id@$SITE_HOSTNAME>\n";

    // Carbon copy create user, devel/bug lists, and interested addressees...
    if ($this->modify_id != $this->create_id)
      $headers .= "Cc: " . user_email($this->create_id) . "\n";

    if ($this->developer_id != "" && $this->status <= BUG_STATUS_UNRESOLVED)
    {
      // Carbon copy the email to the project address...
      $headers .= "Cc: $SITE_EMAIL\n";
    }

    // Check for file attachments...
    if ($file != "")
      $bytes = filesize("files/bug$this->id/$file");
    else
      $bytes = 0;

    if ($bytes > 0 && $bytes <= 102400 && !preg_match("/\\.zip\$/i", $file))
    {
      // Attach the file to the message...
      if (preg_match("/\\.(c|cc|cpp|cxx|diff|diffs|h|hpp|htm|html|txt|patch|"
		    ."php)\$/i", $file))
	$content_type = "text/plain";
      else if (preg_match("/\\.bmp\$/i", $file))
	$content_type = "image/bmp";
      else if (preg_match("/\\.gif\$/i", $file))
	$content_type = "image/gif";
      else if (preg_match("/\\.jpg\$/i", $file))
	$content_type = "image/jpeg";
      else if (preg_match("/\\.pdf\$/i", $file))
	$content_type = "application/pdf";
      else if (preg_match("/\\.png\$/i", $file))
	$content_type = "image/png";
      else
	$content_type = "application/octet-stream";

      $headers .= "Mime-Version: 1.0\n"
		 ."Content-Type: multipart/mixed; boundary=\"PART-BOUNDARY\"\n"
		 ."Content-Transfer-Encoding: 8bit\n"
		 ."\n";
      $body    = "--PART-BOUNDARY\n"
		."Content-Type: text/plain\n"
		."\n"
		."$message"
		."--PART-BOUNDARY\n"
		."Content-Type: $content_type\n"
		."Content-Disposition: attachment; filename=\"$file\"\n";

      if ($content_type == "text/plain")
      {
	$data = str_replace("\r\n", "\n",
			    file_get_contents("files/bug$this->id/$file"));

	$body .= "\n"
		."$data\n";
      }
      else
      {
	$data = chunk_split(base64_encode(
			    file_get_contents("files/bug$this->id/$file")),
			    76, "\n");

	$body .= "Content-Transfer-Encoding: BASE64\n"
		."Content-Length: $bytes\n"
		."\n"
		."$data\n";
      }

      $body .= "--PART-BOUNDARY--\n";
    }
    else
    {
      // Message without attachment...
      $headers .= "Mime-Version: 1.0\n"
		 ."Content-Type: text/plain\n";
      $body    = $message;

      // Add URL to attachment file, since it is too big to email...
      if ($file != "")
	$body .= "Attachment: ${SITE_URL}files/bug$this->id/$file\n";
    }

    // Send the email notification...
    mail($to, $subject, $body, $headers);
  }


  //
  // 'bug::save()' - Save a Bug object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
    {
      return (db_query("UPDATE bug "
                      ." SET project_id = $this->project_id"
                      .", parent_id = $this->parent_id"
                      .", is_published = $this->is_published"
                      .", status = $this->status"
                      .", priority = $this->priority"
                      .", summary = '" . db_escape($this->summary) . "'"
                      .", contents = '" . db_escape($this->contents) . "'"
                      .", bug_version = '" . db_escape($this->bug_version) . "'"
                      .", bug_revision = $this->bug_revision"
                      .", fix_version = '" . db_escape($this->fix_version) . "'"
                      .", fix_revision = $this->fix_revision"
                      .", developer_id = $this->developer_id"
                      .", modify_date = '" . db_escape($this->modify_date) . "'"
                      .", modify_id = $this->modify_id"
                      ." WHERE id = $this->id") !== FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if ($this->developer_id == 0)
      {
        // Find the default developer ID for the project...
        $results = db_query("SELECT developer_id FROM project WHERE "
                           ."id=$this->project_id");
        if ($row = db_next($results))
          $this->developer_id = $row["developer_id"];
        else
          return (FALSE);
      }

      if (db_query("INSERT INTO bug VALUES"
                  ."(NULL"
                  .", $this->project_id"
                  .", $this->parent_id"
                  .", $this->is_published"
                  .", $this->status"
                  .", $this->priority"
                  .", '" . db_escape($this->summary) . "'"
                  .", '" . db_escape($this->contents) . "'"
                  .", '" . db_escape($this->bug_version) . "'"
                  .", $this->bug_revision"
                  .", '" . db_escape($this->fix_version) . "'"
		  .", $this->fix_revision"
                  .", $this->developer_id"
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
  // 'bug::validate()' - Validate the current Bug object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    global $REQUEST_METHOD, $LOGIN_IS_ADMIN;

    $valid = TRUE;

    if ($this->project_id > 0)
    {
      $temp = new project($this->project_id);

      if ($temp->id != $this->project_id ||
          (!$temp->is_published && !$LOGIN_IS_ADMIN))
      {
	$this->project_id_valid = FALSE;
	$valid = FALSE;
      }
      else
	$this->project_id_valid = TRUE;
    }
    else if ($this->project_id <= 0)
    {
      $this->project_id_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->project_id_valid = TRUE;

    if ($this->parent_id > 0)
    {
      $temp = new bug($this->parent_id);

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

    if ($this->status < BUG_STATUS_RESOLVED ||
        $this->status > BUG_STATUS_NEW)
    {
      $this->status_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->status_valid = TRUE;

    if ($this->priority < BUG_PRIORITY_UNASSIGNED ||
        $this->priority > BUG_PRIORITY_CRITICAL)
    {
      $this->priority_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->priority_valid = TRUE;

    if ($this->summary == "" && $REQUEST_METHOD == "POST")
    {
      $this->summary_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->summary_valid = TRUE;

    if ($this->contents == "" && $REQUEST_METHOD == "POST")
    {
      $this->contents_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->contents_valid = TRUE;

    if (($this->bug_version == "" ||
         ($this->priority == BUG_PRIORITY_RFE &&
	  strpos($this->bug_version, "-feature") === FALSE)) &&
	$REQUEST_METHOD == "POST")
    {
      $this->bug_version_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->bug_version_valid = TRUE;

    if ($this->bug_revision < 0)
    {
      $this->bug_revision_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->bug_revision_valid = TRUE;

    if ($this->fix_version == "" && $this->status == BUG_STATUS_RESOLVED &&
        $REQUEST_METHOD == "POST")
    {
      $this->fix_version_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->fix_version_valid = TRUE;

    if ($this->fix_revision < 0)
    {
      $this->fix_revision_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->fix_revision_valid = TRUE;

    if ($this->developer_id <= 0 && $this->status != BUG_STATUS_NEW)
    {
      $this->developer_id_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->developer_id_valid = TRUE;

    return ($valid);
  }
}


//
// 'bug_search()' - Get a list of bug IDs.
//

function				// O - Array of bug IDs
bug_search($search = "",		// I - Search buging
	   $order = "",			// I - Order fields
	   $project_id = 0,		// I - Project ID
	   $priority = 0,		// I - Priority
	   $status = 0,			// I - Status
	   $whose = 0)			// I - Whose
{
  global $LOGIN_EMAIL, $LOGIN_IS_ADMIN, $LOGIN_ID;


  $query  = "";
  $prefix = " WHERE ";

  if ($project_id > 0)
  {
    $query .= "${prefix}project_id = $project_id";
    $prefix = " AND ";
  }

  if ($priority > 0)
  {
    $query .= "${prefix}priority = $priority";
    $prefix = " AND ";
  }

  if ($status > BUG_STATUS_ALL)
  {
    $query .= "${prefix}status = $status";
    $prefix = " AND ";
  }
  else if ($status == BUG_STATUS_CLOSED) // Show closed
  {
    $query .= "${prefix}status <= " . BUG_STATUS_UNRESOLVED;
    $prefix = " AND ";
  }
  else if ($status == BUG_STATUS_OPEN) // Show open
  {
    $query .= "${prefix}status >= " . BUG_STATUS_ACTIVE;
    $prefix = " AND ";
  }

  if ($whose)
  {
    if ($LOGIN_IS_ADMIN)
    {
      $query .= "${prefix}(developer_id = 0 OR developer_id = $LOGIN_ID)";
      $prefix = " AND ";
    }
    else if ($LOGIN_ID != 0)
    {
      $query .= "${prefix}create_id = $LOGIN_ID";
      $prefix = " AND ";
    }
  }
  else if (!$LOGIN_IS_ADMIN)
  {
    $query .= "${prefix}(is_published = 1 OR create_id = $LOGIN_ID)";
    $prefix = " AND ";
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
	$query .= "$prefix$logic developer_id = $id";
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
      else if (substr($word, 0, 10) == "project:")
      {
	// TODO: Map project names to id's
	$id     = (int)substr($word, 10);
	$query  .= "$prefix$logic project = $id";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 6) == "text:")
      {
	$word   = db_escape(substr($word, 5));
	$query  .= "$prefix$logic contents LIKE \"%$word%\"";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 6) == "title:")
      {
	$word   = db_escape(substr($word, 6));
	$query  .= "$prefix$logic summary LIKE \"%$word%\"";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 8) == "version:")
      {
	$word   = db_escape(substr($word, 8));
	$query  .= "$prefix$logic bug_version LIKE \"$word%\"";
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
	$query .= "${subpre}summary LIKE \"%$word%\"";
	$subpre = " OR ";
	$query .= "${subpre}contents LIKE \"$word%\"";
	$query .= "${subpre}bug_version LIKE \"$word%\"";
	$query .= "${subpre}fix_version LIKE \"$word%\"";

	$query .= ")";
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
  $result  = db_query("SELECT id FROM bug$query");
  $matches = array();

  while ($row = db_next($result))
    $matches[sizeof($matches)] = $row["id"];

  // Free the query result and return the array...
  db_free($result);

  return ($matches);
}



//
// End of "$Id: db-bug.php 141 2014-03-30 01:11:14Z msweet $".
//
?>
