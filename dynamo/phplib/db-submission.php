<?php
//
// Class for the submission table.
//

include_once "site.php";
include_once "db-comment.php";

define("SUBMISSION_STATUS_PENDING", 0);
define("SUBMISSION_STATUS_REVIEW", 1);
define("SUBMISSION_STATUS_APPROVED", 2);
define("SUBMISSION_STATUS_REJECTED", 3);
define("SUBMISSION_STATUS_APPEALED", 4);

$SUBMISSION_STATUSES = array(
  SUBMISSION_STATUS_PENDING => "Pending",
  SUBMISSION_STATUS_REVIEW => "SC Review",
  SUBMISSION_STATUS_APPROVED => "Approved",
  SUBMISSION_STATUS_REJECTED => "Rejected",
  SUBMISSION_STATUS_APPEALED => "Appealed"
);

$SUBMISSION_VERSIONS = array(
  "org.pwg.ipp-everywhere.20140826" => "1.0 Draft (August 26, 2014)"
);


class submission
{
  //
  // Instance variables...
  //

  var $id;
  var $status;
  var $organization_id, $organization_id_valid;
  var $contact_name, $contact_name_valid;
  var $contact_email, $contact_email_valid;
  var $product_family, $product_family_valid;
  var $models, $models_valid;
  var $url, $url_valid;
  var $cert_version, $cert_version_valid;
  var $used_approved, $used_approved_valid;
  var $used_prodready, $used_prodready_valid;
  var $printed_correctly, $printed_correctly_valid;
  var $reviewer1_id, $reviewer1_id_valid;
  var $reviewer1_status;
  var $reviewer2_id, $reviewer2_id_valid;
  var $reviewer2_status;
  var $bonjour_file_error, $ipp_file_error, $document_file_error;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'submission::submission()' - Create a submission object.
  //

  function				// O - New Article object
  submission($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'document::add_file()' - Add a submission file
  //

  function				// O - Error string, if any
  add_file($srcfile,			// I - File from form
           $dstname)			// I - Destination filename
  {
    global $SUBMISSION_DIR;


    $filename = $srcfile["name"];	// Original filename
    $filetype = $srcfile["type"];	// File type as reported by browser
    $tmp_name = $srcfile["tmp_name"];	// Local temporary file

    if (!preg_match("/\\.plist\$/", $filename))
      return (htmlspecialchars("Expected a plist file, got '$filename'."));

    $dstdir  = "$SUBMISSION_DIR/$this->id";
    $dstfile = "$SUBMISSION_DIR/$this->id/$dstname";

    if (!file_exists($dstdir))
    {
      if (!mkdir($dstdir, 0700))
        return ("Unable to create submission directory.");
    }

    if (!copy($tmp_name, $dstfile))
      return ("Unable to copy '$filename' to submission directory.");

    return ("");
  }


  //
  // 'submission::add_files()' - Add all files in a form submission.
  //

  function				// O - TRUE if successful, FALSE otherwise
  add_files()
  {
    global $_FILES;


    $this->bonjour_file_error  = "";
    $this->ipp_file_error      = "";
    $this->document_file_error = "";

    if ($this->create_id == $LOGIN_ID)
    {
      if (array_key_exists("bonjour_file", $_FILES))
        $this->bonjour_file_error = $this->add_file($_FILES["bonjour_file"], "bonjour.plist");

      if (array_key_exists("ipp_file", $_FILES))
        $this->ipp_file_error = $this->add_file($_FILES["ipp_file"], "ipp.plist");

      if (array_key_exists("document_file", $_FILES))
        $this->document_file_error = $this->add_file($_FILES["document_file"], "document.plist");
    }

    return ($this->bonjour_file_error == "" && $this->ipp_file_error == "" && $this->document_file_error == "");
  }

  //
  // 'submission::clear()' - Initialize a new submission object.
  //

  function
  clear()
  {
    global $LOGIN_ID, $LOGIN_ORGANIZATION;


    $this->id                = 0;
    $this->status            = SUBMISSION_STATUS_PENDING;
    $this->organization_id   = $LOGIN_ORGANIZATION;
    $this->contact_name      = "";
    $this->contact_email     = "";
    $this->product_family    = "";
    $this->models            = "";
    $this->url               = "";
    $this->cert_version      = "";
    $this->used_approved     = 0;
    $this->used_prodready    = 0;
    $this->printed_correctly = 0;
    $this->reviewer1_id      = -1;
    $this->reviewer1_status  = SUBMISSION_STATUS_PENDING;
    $this->reviewer2_id      = -1;
    $this->reviewer2_status  = SUBMISSION_STATUS_PENDING;
    $this->create_date       = "";
    $this->create_id         = $LOGIN_ID;
    $this->modify_date       = "";
    $this->modify_id         = 0;
  }


  //
  // 'submission::delete()' - Delete a submission object.
  //

  function
  delete()
  {
    db_query("DELETE FROM submission WHERE id=$this->id");
    $this->clear();
  }


  //
  // 'submission::form()' - Display a form for a submission object.
  //

  function
  form()
  {
    global $LOGIN_ID, $LOGIN_IS_ADMIN, $PHP_SELF, $SUBMISSION_DIR, $SUBMISSION_STATUSES, $SUBMISSION_VERSIONS, $_POST;


    print("<h2>Information</h2>\n");

    if ($this->id <= 0)
      $action = "Submit Self-Certification";
    else
      $action = "Modify Submission #$this->id";

    html_form_start("$PHP_SELF?U$this->id", FALSE, TRUE);

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

    // status
    html_form_field_start("status", "Status");
    if ($LOGIN_IS_ADMIN && $this->status != SUBMISSION_STATUS_APPROVED)
      html_form_select("status", $SUBMISSION_STATUSES, "", $this->status);
    else
      print($SUBMISSION_STATUSES[$this->status]);
    html_form_field_end();

    // organization_id
    html_form_field_start("+organization_id", "Organization Name", $this->organization_id_valid);
    if ($this->create_id == $LOGIN_ID)
      organization_select("organization_id", $this->organization_id, "-- Choose --");
    else
      print(organization_name($this->organization_id));
    html_form_field_end();

    // contact_name
    html_form_field_start("+contact_name", "Contact Name", $this->contact_name_valid);
    if ($this->create_id == $LOGIN_ID)
      html_form_text("contact_name", "Contact for submission", $this->contact_name);
    else
      print(htmlspecialchars($this->contact_name));
    html_form_field_end();

    // contact_email
    html_form_field_start("+contact_email", "Contact Name", $this->contact_email_valid);
    if ($this->create_id == $LOGIN_ID)
      html_form_email("contact_email", "name@example.com", $this->contact_email);
    else
      print(htmlspecialchars($this->contact_email));
    html_form_field_end();

    // product_family
    html_form_field_start("+product_family", "Product Family Name", $this->product_family_valid);
    if ($this->create_id == $LOGIN_ID)
      html_form_text("product_family", "Name of product family being submitted", $this->product_family);
    else
      print(htmlspecialchars($this->product_family));
    html_form_field_end();

    // url
    html_form_field_start("url", "Product Family URL", $this->url_valid);
    if ($this->create_id == $LOGIN_ID)
      html_form_url("url", "http://www.example.com/products", $this->url);
    else
    {
      $temp = htmlspecialchars($this->url, ENT_QUOTES);
      print("<a href=\"$temp\">$temp</a>");
    }
    html_form_field_end();

    // models
    html_form_field_start("+models", "Models", $this->models_valid);
    if ($this->create_id == $LOGIN_ID)
      html_form_text("models", "Make Model\nMake Model\n...", $this->models,
                   "List the make and model of every printer in the product family, one per line.", 20);
    else
      print(html_text($this->models));
    html_form_field_end();

    // cert_version
    html_form_field_start("+cert_version", "Self-Certification Manual");
    if ($this->create_id == $LOGIN_ID)
      html_form_select("cert_version", $SUBMISSION_VERSIONS, "", $this->cert_version);
    else if (array_key_exists($this->cert_version, $SUBMISSION_VERSIONS))
      print($SUBMISSION_VERSIONS[$this->cert_version]);
    else
      print("Unknown (" . htmlspecialchars($this->cert_version) . ")");
    html_form_field_end();

    // used_approved, used_prodready, printed_correctly
    html_form_field_start("+used_approved", "Submission Checklist");
    if ($this->create_id == $LOGIN_ID)
    {
      html_form_checkbox("used_approved", "Used PWG self-certification tools.", $this->used_approved, "As supplied on the PWG FTP server.");
      html_form_checkbox("used_prodready", "Used Production-Ready Code.", $this->used_prodready, "Production-Ready Code: Software and/or firmware that is considered ready to be included in products shipped to customers.");
      html_form_checkbox("printed_correctly", "All output printed correctly.", $this->printed_correctly, "As documented in section 7.3 of the IPP Everywhere Printer Self-Certification Manual 1.0.");
    }
    else
    {
      if ($this->used_approved)
        print("<span class=\"glyphicon glyphicon-check\"></span>");
      else
        print("<span class=\"glyphicon glyphicon-unchecked\"></span>");
      print(" Used PWG self-certification tools.<br>\n");

      if ($this->used_prodready)
        print("<span class=\"glyphicon glyphicon-check\"></span>");
      else
        print("<span class=\"glyphicon glyphicon-unchecked\"></span>");
      print(" Used Production-Ready Code.<br>\n");

      if ($this->printed_correctly)
        print("<span class=\"glyphicon glyphicon-check\"></span>");
      else
        print("<span class=\"glyphicon glyphicon-unchecked\"></span>");
      print(" All output printed correctly.\n");
    }
    html_form_field_end();

    // reviewer1_id
    html_form_field_start("+reviewer1_id", "First Reviewer", $this->reviewer1_id_valid);
    if ($this->create_id == $LOGIN_ID)
      user_select("reviewer1_id", $this->reviewer1_id, USER_SELECT_REVIEWER, "-- Choose --");
    else
      print(user_name($this->reviewer1_id));
    html_form_field_end();

    if ($this->id)
    {
      html_form_field_start("+reviewer1_status", "First Status");
      if ($this->reviewer1_id == $LOGIN_ID)
	html_form_select("reviewer1_status", $SUBMISSION_STATUSES, "", $this->reviewer1_status);
      else
	print($SUBMISSION_STATUSES[$this->reviewer1_status]);
      html_form_field_end();
    }

    // reviewer2_id
    html_form_field_start("+reviewer2_id", "Second Reviewer", $this->reviewer2_id_valid);
    if ($this->create_id == $LOGIN_ID)
      user_select("reviewer2_id", $this->reviewer2_id, USER_SELECT_REVIEWER, "-- Choose --");
    else
      print(user_name($this->reviewer2_id));
    html_form_field_end();

    if ($this->id)
    {
      html_form_field_start("+reviewer2_status", "Second Status");
      if ($this->reviewer2_id == $LOGIN_ID)
	html_form_select("reviewer2_status", $SUBMISSION_STATUSES, "", $this->reviewer2_status);
      else
	print($SUBMISSION_STATUSES[$this->reviewer2_status]);
      html_form_field_end();
    }

    // files
    if ($this->id == 0)
    {
      html_form_field_start("+bonjour_file", "Bonjour Test Results");
      html_form_file("bonjour_file", "", $this->bonjour_file_error);
      html_form_field_end();

      html_form_field_start("+ipp_file", "IPP Test Results");
      html_form_file("ipp_file", "", $this->bonjour_file_error);
      html_form_field_end();

      html_form_field_start("+document_file", "Document Data Test Results");
      html_form_file("document_file", "", $this->bonjour_file_error);
      html_form_field_end();

      html_form_field_start("exceptions", "Exceptions");
      print("EXCEPTION UI HERE");
      html_form_field_end();
    }
    else
    {
      html_form_field_start("+bonjour_file", "Bonjour Test Results");
      $filename = "$SUBMISSION_DIR/$this->id/bonjour.plist";
      $filesize = sprintf("%.1fk", filesize($filename) / 1024);
      if ($LOGIN_ID == $this->create_id || $LOGIN_ID == $this->reviewer1_id || $LOGIN_ID == $this->reviewer2_id)
        print("<a class=\"btn btn-default btn-mini\" href=\"${html_path}dynamo/evefile.php/$this->id/bonjour.plist\"><span class=\"glyphicon glyphicon-download\"></span> Download bonjour.plist ($filesize)</a>");
      else
        print("bonjour.plist ($filesize)");
      html_form_field_end();

      html_form_field_start("+ipp_file", "IPP Test Results");
      $filename = "$SUBMISSION_DIR/$this->id/ipp.plist";
      $filesize = sprintf("%.1fk", filesize($filename) / 1024);
      if ($LOGIN_ID == $this->create_id || $LOGIN_ID == $this->reviewer1_id || $LOGIN_ID == $this->reviewer2_id)
        print("<a class=\"btn btn-default btn-mini\" href=\"${html_path}dynamo/evefile.php/$this->id/ipp.plist\"><span class=\"glyphicon glyphicon-download\"></span> Download ipp.plist ($filesize)</a>");
      else
        print("ipp.plist ($filesize)");
      html_form_field_end();

      html_form_field_start("+document_file", "Document Data Test Results");
      $filename = "$SUBMISSION_DIR/$this->id/document.plist";
      $filesize = sprintf("%.1fk", filesize($filename) / 1024);
      if ($LOGIN_ID == $this->create_id || $LOGIN_ID == $this->reviewer1_id || $LOGIN_ID == $this->reviewer2_id)
        print("<a class=\"btn btn-default btn-mini\" href=\"${html_path}dynamo/evefile.php/$this->id/document.plist\"><span class=\"glyphicon glyphicon-download\"></span> Download document.plist ($filesize)</a>");
      else
        print("document.plist ($filesize)");
      html_form_field_end();

      html_form_field_start("exceptions", "Exceptions");
      print("EXCEPTION LIST HERE");
      html_form_field_end();
    }

    // Submit
    html_form_buttons(array("SUBMIT" => "+$action"));

    if ($this->id > 0)
    {
      print("<h2>Discussion</h2>\n");

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

      $comments = comment_search("submission_$this->id", "", "-id");
      foreach ($comments as $id)
      {
	$comment  = new comment($id);
	$name     = user_name($comment->create_id);
	$contents = html_text($comment->contents);
	$date     = html_date($comment->create_date);

	print("<h3><a name=\"C$id\">$name <small>$date</small></a></h3>\n"
	     ."<p>$contents</p>\n");
      }
    }

    // Submit
    html_form_end();
  }


  //
  // 'submission::load()' - Load a submission object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    $this->clear();

    $result = db_query("SELECT * FROM submission WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);
    $this->id                = $row["id"];
    $this->status            = $row["status"];
    $this->organization_id   = $row["organization_id"];
    $this->contact_name      = $row["contact_name"];
    $this->contact_email     = $row["contact_email"];
    $this->product_family    = $row["product_family"];
    $this->models            = $row["models"];
    $this->url               = $row["url"];
    $this->cert_version      = $row["cert_version"];
    $this->used_approved     = $row["used_approved"];
    $this->used_prodready    = $row["used_prodready"];
    $this->printed_correctly = $row["printed_correctly"];
    $this->reviewer1_id      = $row["reviewer1_id"];
    $this->reviewer1_status  = $row["reviewer1_status"];
    $this->reviewer2_id      = $row["reviewer2_id"];
    $this->reviewer2_status  = $row["reviewer2_status"];
    $this->create_date       = $row["create_date"];
    $this->create_id         = $row["create_id"];
    $this->modify_date       = $row["modify_date"];
    $this->modify_id         = $row["modify_id"];

    db_free($result);

    return ($this->validate());
  }


  //
  // 'submission::loadform()' - Load a submission object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST, $LOGIN_ID, $LOGIN_IS_ADMIN;


    if (!html_form_validate())
      return (FALSE);

    if ($LOGIN_IS_ADMIN && array_key_exists("status", $_POST))
      $this->status = (int)$_POST["status"];

    if ($this->create_id == $LOGIN_ID)
    {
      if (array_key_exists("organization_id", $_POST) &&
	  preg_match("/^o[0-9]+\$/", $_POST["organization_id"]))
	$this->organization_id = (int)substr($_POST["organization_id"], 1);

      if (array_key_exists("contact_name", $_POST))
	$this->contact_name = trim($_POST["contact_name"]);

      if (array_key_exists("contact_email", $_POST))
	$this->contact_email = trim($_POST["contact_email"]);

      if (array_key_exists("product_family", $_POST))
	$this->product_family = trim($_POST["product_family"]);

      if (array_key_exists("models", $_POST))
	$this->models = trim($_POST["models"]);

      if (array_key_exists("url", $_POST))
	$this->url = trim($_POST["url"]);

      if (array_key_exists("cert_version", $_POST))
	$this->cert_version = trim($_POST["cert_version"]);

      if (array_key_exists("used_approved", $_POST))
	$this->used_approved = 1;
      else
	$this->used_approved = 0;

      if (array_key_exists("used_prodready", $_POST))
	$this->used_prodready = 1;
      else
	$this->used_prodready = 0;

      if (array_key_exists("printed_correctly", $_POST))
	$this->printed_correctly = 1;
      else
	$this->printed_correctly = 0;

      if (array_key_exists("reviewer1_id", $_POST))
	$this->reviewer1_id = (int)$_POST["reviewer1_id"];

      if (array_key_exists("reviewer2_id", $_POST))
	$this->reviewer2_id = (int)$_POST["reviewer2_id"];
    }

    if ($LOGIN_ID == $this->reviewer1_id &&
        array_key_exists("reviewer1_status", $_POST))
      $this->reviewer1_status = (int)$_POST["reviewer1_status"];

    if ($LOGIN_ID == $this->reviewer2_id &&
        array_key_exists("reviewer2_status", $_POST))
      $this->reviewer2_status = (int)$_POST["reviewer2_status"];

    return ($this->validate());
  }


  //
  // 'submission::notify_users()' - Notify users of submission changes.
  //

  function
  notify_users($what = "Re: ")		// I - Reply or new message
  {
    global $_POST, $SITE_EMAIL, $SITE_HOSTNAME, $SITE_URL, $SUBMISSION_STATUSES;


    // Emails always go to the contact in the submission, and are Cc'd to the
    // reviewers.
    $to      = $this->contact_email;
    $cc1     = user_email($this->reviewer1_id);
    $cc2     = user_email($this->reviewer2_id);
    $from    = user_email($this->modify_id);
    $replyto = "noreply@$SITE_HOSTNAME";
    if ($this->create_date == $this->modify_date)
      $mid = "Message-Id: <submission-$this->id@$SITE_HOSTNAME>";
    else
      $mid = "In-Reply-To: <submission-$this->id@$SITE_HOSTNAME>";

    // Send the email...
    $subject = "${what}[IPPEVE] Submission #$this->id: $this->product_family";
    $headers = "From: $from\n"
	      ."Reply-To: $replyto\n"
	      ."$mid\n"
	      ."Cc: $cc1\n"
	      ."Cc: $cc2\n"
	      ."Mime-Version: 1.0\n"
	      ."Content-Type: text/plain\n";

    $message = "DO NOT REPLY TO THIS MESSAGE.  INSTEAD, POST ANY RESPONSES TO "
	      ."THE LINK BELOW.\n\n"
	      ."Overall Status: " . $SUBMISSION_STATUSES[$this->status] . "\n"
	      ."Reviewer 1 Status: " . $SUBMISSION_STATUSES[$this->reviewer1_status] . "\n"
	      ."Reviewer 2 Status: " . $SUBMISSION_STATUSES[$this->reviewer2_status] . "\n"
	      ."Updated by: " . user_name($this->modify_id) . "\n";

    if (array_key_exists("contents", $_POST))
      $message .= "\n" . wordwrap(trim($_POST["contents"])) . "\n";

    $message .= "\nLink: ${SITE_URL}dynamo/evereview.php?U$this->id\n";

    // Send the email notification...
    mail($to, $subject, $message, $headers);
  }


  //
  // 'submission::save()' - Save a submission object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PHP_SELF, $_POST;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
    {
      if (db_query("UPDATE submission "
		  ." SET status = $this->status"
		  .", organization_id = $this->organization_id"
		  .", contact_name = '" . db_escape($this->contact_name) . "'"
		  .", contact_email = '" . db_escape($this->contact_email) . "'"
		  .", product_family = '" . db_escape($this->product_family) . "'"
		  .", models = '" . db_escape($this->models) . "'"
		  .", url = '" . db_escape($this->url) . "'"
		  .", cert_version = '" . db_escape($this->cert_version) . "'"
		  .", used_approved = $this->used_approved"
		  .", used_prodready = $this->used_prodready"
		  .", printed_correctly = $this->printed_correctly"
		  .", reviewer1_id = $this->reviewer1_id"
		  .", reviewer1_status = $this->reviewer1_status"
		  .", reviewer2_id = $this->reviewer2_id"
		  .", reviewer2_status = $this->reviewer2_status"
		  .", modify_date = '" . db_escape($this->modify_date) . "'"
		  .", modify_id = $this->modify_id"
		  ." WHERE id = $this->id") === FALSE)
      return (FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if (db_query("INSERT INTO submission VALUES"
                  ."(NULL"
		  .", $this->status"
                  .", $this->organization_id"
                  .", '" . db_escape($this->contact_name) . "'"
                  .", '" . db_escape($this->contact_email) . "'"
                  .", '" . db_escape($this->product_family) . "'"
                  .", '" . db_escape($this->models) . "'"
                  .", '" . db_escape($this->url) . "'"
                  .", '" . db_escape($this->cert_version) . "'"
                  .", $this->used_approved"
                  .", $this->used_prodready"
                  .", $this->printed_correctly"
                  .", $this->reviewer1_id"
                  .", $this->reviewer1_status"
                  .", $this->reviewer2_id"
                  .", $this->reviewer2_status"
                  .", '" . db_escape($this->create_date) . "'"
                  .", $this->create_id"
                  .", '" . db_escape($this->modify_date) . "'"
                  .", $this->modify_id"
                  .")") === FALSE)
        return (FALSE);

      $this->id = db_insert_id();
    }

    if (array_key_exists("contents", $_POST) && ($contents = trim($_POST["contents"])) != "")
    {
      $comment           = new comment();
      $comment->ref_id   = "submission_$this->id";
      $comment->contents = $contents;

      return ($comment->save());
    }
    return (TRUE);
  }


  //
  // 'submission::validate()' - Validate the current Article object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    global $REQUEST_METHOD, $SUBMISSION_VERSIONS;


    $valid = TRUE;

    if ($this->organization_id < 0 && $REQUEST_METHOD == "POST")
    {
      $this->organization_id_valid = FALSE;
      $valid = FALSE;
    }
    else if ($this->organization_id > 0)
    {
      $org = new organization($this->organization_id);
      if ($org->id != $this->organization_id || !$org->is_everywhere)
      {
	$this->organization_id_valid = FALSE;
	$valid = FALSE;
      }
      else
	$this->organization_id_valid = TRUE;
    }
    else
      $this->organization_id_valid = TRUE;

    if ($this->contact_name == "" && $REQUEST_METHOD == "POST")
    {
      $this->contact_name_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->contact_name_valid = TRUE;

    if ($this->contact_email == "" && $REQUEST_METHOD == "POST")
    {
      $this->contact_email_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->contact_email_valid = TRUE;

    if ($this->product_family == "" && $REQUEST_METHOD == "POST")
    {
      $this->product_family_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->product_family_valid = TRUE;

    if ($this->models == "" && $REQUEST_METHOD == "POST")
    {
      $this->models_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->models_valid = TRUE;

    if ($this->url != "" && !validate_url($this->url))
    {
      $this->url_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->url_valid = TRUE;

    if (!array_key_exists($this->cert_version, $SUBMISSION_VERSIONS) && $REQUEST_METHOD == "POST")
    {
      $this->cert_version_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->cert_version_valid = TRUE;

    if ($this->reviewer1_id > 0)
    {
      $user = new user($this->reviewer1_id);
      if ($user->id != $this->reviewer1_id || !$user->is_reviewer)
      {
        $this->reviewer1_id_valid = FALSE;
        $valid = FALSE;
      }
      else
        $this->reviewer1_id_valid = TRUE;
    }
    else if ($this->reviewer1_id == 0 && $REQUEST_METHOD == "POST")
    {
      $this->reviewer1_id_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->reviewer1_id_valid = TRUE;

    if ($this->reviewer2_id > 0)
    {
      $user = new user($this->reviewer2_id);
      if ($user->id != $this->reviewer2_id || !$user->is_reviewer)
      {
        $this->reviewer2_id_valid = FALSE;
        $valid = FALSE;
      }
      else
        $this->reviewer2_id_valid = TRUE;
    }
    else if ($this->reviewer2_id == 0 && $REQUEST_METHOD == "POST")
    {
      $this->reviewer2_id_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->reviewer2_id_valid = TRUE;

    if ($this->reviewer1_id != 0 && $this->reviewer1_id == $this->reviewer2_id)
    {
      $this->reviewer1_id_valid = FALSE;
      $this->reviewer2_id_valid = FALSE;
      $valid = FALSE;
    }

    return ($valid);
  }
}


?>
