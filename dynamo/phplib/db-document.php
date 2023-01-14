<?php
//
// Class for the document table.
//

include_once "site.php";
include_once "db-workgroup.php";

$DOCUMENT_COLUMNS = array(
  "replaces_id" => PDO::PARAM_INT,
  "workgroup_id" => PDO::PARAM_INT,
  "status" => PDO::PARAM_INT,
  "series" => PDO::PARAM_INT,
  "number" => PDO::PARAM_INT,
  "version" => PDO::PARAM_STR,
  "title" => PDO::PARAM_STR,
  "contents" => PDO::PARAM_STR,
  "editable_url" => PDO::PARAM_STR,
  "clean_url" => PDO::PARAM_STR,
  "redline_url" => PDO::PARAM_STR,
  "create_date" => PDO::PARAM_STR,
  "create_id" => PDO::PARAM_INT,
  "modify_date" => PDO::PARAM_STR,
  "modify_id" => PDO::PARAM_INT
);

define("DOCUMENT_STATUS_OBSOLETE", 0);
define("DOCUMENT_STATUS_INITIAL_WORKING_DRAFT", 1);
define("DOCUMENT_STATUS_INTERIM_WORKING_DRAFT", 2);
define("DOCUMENT_STATUS_PROTOTYPE_WORKING_DRAFT", 3);
define("DOCUMENT_STATUS_STABLE_WORKING_DRAFT", 4);
define("DOCUMENT_STATUS_CONFERENCE_CALL_MINUTES", 5);
define("DOCUMENT_STATUS_FACE_TO_FACE_MINUTES", 6);
define("DOCUMENT_STATUS_WHITE_PAPER", 7);
define("DOCUMENT_STATUS_CHARTER", 8);
define("DOCUMENT_STATUS_INFORMATIONAL", 9);
define("DOCUMENT_STATUS_CANDIDATE_STANDARD", 10);
define("DOCUMENT_STATUS_FULL_STANDARD", 11);

$DOCUMENT_STATUSES = array(
  DOCUMENT_STATUS_OBSOLETE => "Obsolete",
  DOCUMENT_STATUS_INITIAL_WORKING_DRAFT => "Initial Working Draft",
  DOCUMENT_STATUS_INTERIM_WORKING_DRAFT => "Interim Working Draft",
  DOCUMENT_STATUS_PROTOTYPE_WORKING_DRAFT => "Prototype Working Draft",
  DOCUMENT_STATUS_STABLE_WORKING_DRAFT => "Stable Working Draft",
  DOCUMENT_STATUS_CONFERENCE_CALL_MINUTES => "Conference Call Minutes",
  DOCUMENT_STATUS_FACE_TO_FACE_MINUTES => "Face-to-Face Minutes",
  DOCUMENT_STATUS_WHITE_PAPER => "White Paper (Approved)",
  DOCUMENT_STATUS_CHARTER => "Charter (Approved)",
  DOCUMENT_STATUS_INFORMATIONAL => "Informational (Approved)",
  DOCUMENT_STATUS_CANDIDATE_STANDARD => "Candidate Standard",
  DOCUMENT_STATUS_FULL_STANDARD => "Full Standard"
);

$DOCUMENT_STATUSES_ANY_USER = array(
  DOCUMENT_STATUS_INITIAL_WORKING_DRAFT => "Initial Working Draft",
  DOCUMENT_STATUS_INTERIM_WORKING_DRAFT => "Interim Working Draft",
  DOCUMENT_STATUS_PROTOTYPE_WORKING_DRAFT => "Prototype Working Draft",
  DOCUMENT_STATUS_STABLE_WORKING_DRAFT => "Stable Working Draft",
  DOCUMENT_STATUS_CONFERENCE_CALL_MINUTES => "Conference Call Minutes",
  DOCUMENT_STATUS_FACE_TO_FACE_MINUTES => "Face-to-Face Minutes"
);



class document
{
  //
  // Instance variables...
  //

  var $id;
  var $replaces_id;
  var $workgroup_id;
  var $status;
  var $series, $series_valid;
  var $number, $number_valid;
  var $version, $version_valid;
  var $title, $title_valid;
  var $contents, $contents_valid;
  var $editable_url, $editable_url_valid;
  var $clean_url, $clean_url_valid;
  var $redline_url, $redline_url_valid;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'document::document()' - Create a document object.
  //

  function				// O - New document object
  document($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'document::clear()' - Initialize a new a document object.
  //

  function
  clear()
  {
    global $LOGIN_ID;

    $this->id           = 0;
    $this->replaces_id  = 0;
    $this->workgroup_id = 0;
    $this->status       = DOCUMENT_STATUS_CONFERENCE_CALL_MINUTES;
    $this->series       = 0;
    $this->number       = 0;
    $this->version      = "";
    $this->title        = "";
    $this->contents     = "";
    $this->editable_url = "";
    $this->clean_url    = "";
    $this->redline_url  = "";
    $this->create_date  = "";
    $this->create_id    = $LOGIN_ID;
    $this->modify_date  = "";
    $this->modify_id    = $LOGIN_ID;
  }


  //
  // 'document::delete()' - Delete a document object.
  //

  function
  delete()
  {
    db_delete("document", $this->id);
    $this->clear();
  }


  //
  // 'document::display_name()' - Return the display name for a document object.
  //

  function				// O - Display name
  display_name()
  {
    if ($this->series != 0)
    {
      if (preg_match("/-(20[0-9][0-9])[0-9]{4,4}-51/", $this->clean_url, $matches))
        $year = "-$matches[1]";
      else
        $year = "";

      if ($this->series == 5108)		// For Pete
        return (sprintf("PWG %d.%02d%s: %s", $this->series, $this->number, $year, htmlspecialchars($this->title, ENT_QUOTES)));
      else
        return (sprintf("PWG %d.%d%s: %s", $this->series, $this->number, $year, htmlspecialchars($this->title, ENT_QUOTES)));
    }
    else
      return (htmlspecialchars($this->title, ENT_QUOTES));
  }


  //
  // 'document::form()' - Display a form for an document object.
  //

  function
  form($options = "")			// I - Page options
  {
    global $PHP_SELF, $DOCUMENT_STATUSES, $DOCUMENT_STATUSES_ANY_USER;
    global $html_is_phone, $html_is_tablet;
    global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR, $LOGIN_IS_OFFICER;


    if ($this->id <= 0)
      $action = "Submit Document";
    else
      $action = "Save Changes";

    html_form_start("$PHP_SELF?U$this->id$options", FALSE, TRUE);

    html_form_field_start("status", "Type");
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER)
      html_form_select("status", $DOCUMENT_STATUSES, "", $this->status);
    else if ($this->create_id == $LOGIN_ID)
      html_form_select("status", $DOCUMENT_STATUSES_ANY_USER, "", $this->status);
    else
      print($DOCUMENT_STATUSES[$this->status]);
    html_form_field_end();

    html_form_field_start("replaces_id", "Replaces");
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
      document_select("replaces_id", $this->replaces_id, "None", "", TRUE);
    else if ($this->replaces_id)
    {
      $document = new document($this->replaces_id);
      if ($document->id == $this->replaces_id)
        print($document->display_name());
      else
        print("None");
    }
    else
      print("None");
    html_form_field_end();

    html_form_field_start("title", "Title", $this->title_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
      html_form_text("title", "The title/summary of the document.", $this->title);
    else
      print(htmlspecialchars($this->title));
    html_form_field_end();

    html_form_field_start("version", "Version", $this->title_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
      html_form_text("version", "1.0, 1.1, 2.0, etc.", $this->version);
    else if ($this->version != "")
      print(htmlspecialchars($this->version));
    else
      print("None");
    html_form_field_end();

    html_form_field_start("number", "Standard Number", $this->title_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
    {
      html_form_text("series", "5100, etc.", $this->series);
      print(".");
      html_form_text("number", "1, 2, etc.", $this->number);
    }
    else if ($this->series != 0)
      print("$this->series.$this->number");
    else
      print("None");
    html_form_field_end();

    html_form_field_start("contents", "Abstract", $this->contents_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
      html_form_text("contents", "An abstract of the document - leave blank for minutes.", $this->contents,
		     "Formatting/markup rules:\n\n"
		    ."! Header\n"
		    ."!! Sub-header\n"
		    ."- Unordered list\n"
		    ."* Unordered list\n"
		    ."1. Numbered list\n"
		    ."\" Blockquote\n"
		    ."SPACE preformatted text\n"
		    ."[[link||text label]]\n", 10);
    else
      print(html_format($this->contents));
    html_form_field_end();

    html_form_field_start("editable_url", "Editable URL", $this->editable_url_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
    {
      html_form_url("editable_url", "http://ftp.pwg.org/pub/pwg/WORKGROUP/wd/wd-something-YYYYMMDD.docx", $this->editable_url);
      if (!$html_is_phone && !$html_is_tablet)
      {
	print("<br>Or upload the file: ");
	html_form_file("editable_file");
      }
    }
    else if ($this->editable_url != "")
    {
      $link = htmlspecialchars($this->editable_url, ENT_QUOTES);
      print("<a href=\"$link\">$link</a>");
    }
    else
      print("None");
    html_form_field_end();

    html_form_field_start("clean_url", "Clean Copy URL", $this->clean_url_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
    {
      html_form_url("clean_url", "http://ftp.pwg.org/pub/pwg/WORKGROUP/wd/wd-something-YYYYMMDD.pdf", $this->clean_url);
      if (!$html_is_phone && !$html_is_tablet)
      {
	print("<br>Or upload the file: ");
	html_form_file("clean_file");
      }
    }
    else if ($this->clean_url != "")
    {
      $link = htmlspecialchars($this->clean_url, ENT_QUOTES);
      print("<a href=\"$link\">$link</a>");
    }
    else
      print("None");
    html_form_field_end();

    html_form_field_start("redline_url", "Redlined Copy URL", $this->redline_url_valid);
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
    {
      html_form_url("redline_url", "http://ftp.pwg.org/pub/pwg/WORKGROUP/wd/wd-something-YYYYMMDD-rev.pdf", $this->redline_url);
      if (!$html_is_phone && !$html_is_tablet)
      {
	print("<br>Or upload the file: ");
	html_form_file("redline_file");
      }
    }
    else if ($this->redline_url != "")
    {
      $link = htmlspecialchars($this->redline_url, ENT_QUOTES);
      print("<a href=\"$link\">$link</a>");
    }
    else
      print("None");
    html_form_field_end();

    html_form_field_start("workgroup_id", "Workgroup");
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
      workgroup_select("workgroup_id", $this->workgroup_id, "None");
    else if ($this->workgroup_id)
      print(workgroup_name($this->workgroup_id));
    else
      print("None");
    html_form_field_end();

    // Submit
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)
      html_form_end(array("SUBMIT" => "+$action"));
    else
      html_form_end(array());
  }


  //
  // 'document::load()' - Load a document object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    global $DOCUMENT_COLUMNS;

    $this->clear();

    if (!db_load($this, "document", $id, $DOCUMENT_COLUMNS))
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }


  //
  // 'document::loadform()' - Load an document object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform(&$error)
  {
    global $_POST, $_FILES;


    if (!html_form_validate())
    {
      $error = "Unable to validate form data.";
      return (FALSE);
    }

    $error = "";

    if (array_key_exists("status", $_POST))
      $this->status = (int)$_POST["status"];

    if (array_key_exists("replaces_id", $_POST))
      $this->replaces_id = (int)$_POST["replaces_id"];

    if (array_key_exists("title", $_POST))
      $this->title = trim($_POST["title"]);

    if (array_key_exists("series", $_POST))
      $this->series = (int)($_POST["series"]);

    if (array_key_exists("number", $_POST))
      $this->number = (int)($_POST["number"]);

    if (array_key_exists("version", $_POST))
      $this->version = trim($_POST["version"]);

    if (array_key_exists("contents", $_POST))
      $this->contents = trim($_POST["contents"]);

    if (array_key_exists("editable_url", $_POST))
      $this->editable_url = str_replace("ftp://ftp.pwg.org/", "https://ftp.pwg.org/", trim($_POST["editable_url"]));

    if (array_key_exists("clean_url", $_POST))
      $this->clean_url = str_replace("ftp://ftp.pwg.org/", "https://ftp.pwg.org/", trim($_POST["clean_url"]));

    if (array_key_exists("redline_url", $_POST))
      $this->redline_url = str_replace("ftp://ftp.pwg.org/", "https://ftp.pwg.org/", trim($_POST["redline_url"]));

    if (array_key_exists("workgroup_id", $_POST))
      $this->workgroup_id = (int)$_POST["workgroup_id"];

    if (array_key_exists("editable_file", $_FILES) && $_FILES["editable_file"]["tmp_name"] != "")
    {
      if ($url = $this->upload($_FILES["editable_file"], $error))
        $this->editable_url = $url;
      else
        return (FALSE);
    }

    if (array_key_exists("redline_file", $_FILES) && $_FILES["redline_file"]["tmp_name"] != "")
    {
      if ($url = $this->upload($_FILES["redline_file"], $error))
        $this->redline_url = $url;
      else
        return (FALSE);
    }

    if (array_key_exists("clean_file", $_FILES) && $_FILES["clean_file"]["tmp_name"] != "")
    {
      if ($url = $this->upload($_FILES["clean_file"], $error))
        $this->clean_url = $url;
      else
        return (FALSE);
    }

    if (!$this->validate())
    {
      $error = "Please correct the highlighted fields.";
      return (FALSE);
    }

    return (TRUE);
  }


  //
  // 'document::save()' - Save a document object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $DOCUMENT_COLUMNS, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR, $LOGIN_IS_OFFICER, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->replaces_id)
    {
      $document = new document($this->replaces_id);

      if ($document->id != $this->replaces_id)
        return (FALSE);

      if ($document->status != DOCUMENT_STATUS_OBSOLETE)
      {
        if ($LOGIN_ID == $document->create_id || $LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER)
        {
          $document->status = DOCUMENT_STATUS_OBSOLETE;
          if (!db_save($document, "document", $document->id, $DOCUMENT_COLUMNS))
            return (FALSE);
        }
        else
          return (FALSE);
      }
    }

    if ($this->id > 0)
      return (db_save($this, "document", $this->id, $DOCUMENT_COLUMNS));

    $this->create_date = $this->modify_date;
    $this->create_id   = $this->modify_id;

    if (($id = db_create($this, "document", $DOCUMENT_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }


  //
  // 'document::upload()' - Upload a document to the FTP server.
  //

  function				// O - URL if OK, empty string otherwise
  upload($file, &$error)		// I - File to upload (from form)
  {
    global $FTP_USER, $FTP_PASSWORD;	// FTP account info from site.cfg


    $filename = $file["name"];		// Original filename
    $filetype = $file["type"];		// File type as reported by browser
    $tmp_name = $file["tmp_name"];	// Local temporary file

    if (!preg_match("/^[a-z0-9-_.]+\\.(doc|docx|html|pdf|rtf|txt|xml)\$/i", $filename))
    {
      $error = "Bad filename \"$filename\" - only HTML, PDF, text, RTF, MS Word, and XML documents can be uploaded at this time.";
      return ("");
    }

    switch ($this->status)
    {
      case DOCUMENT_STATUS_OBSOLETE :
          $error = "Cannot upload new files for obsolete documents.";
          return ("");

      case DOCUMENT_STATUS_INITIAL_WORKING_DRAFT :
      case DOCUMENT_STATUS_INTERIM_WORKING_DRAFT :
      case DOCUMENT_STATUS_PROTOTYPE_WORKING_DRAFT :
      case DOCUMENT_STATUS_STABLE_WORKING_DRAFT :
          $workgroup = new workgroup($this->workgroup_id);
          if ($workgroup->id == $this->workgroup_id)
            $ftpdir = $workgroup->ftpdir;
          else
            $ftpdir = "general";

          $path = "/pub/pwg/$ftpdir/wd/$filename";
          break;
      case DOCUMENT_STATUS_CONFERENCE_CALL_MINUTES :
      case DOCUMENT_STATUS_FACE_TO_FACE_MINUTES :
          $workgroup = new workgroup($this->workgroup_id);
          if ($workgroup->id == $this->workgroup_id)
            $ftpdir = $workgroup->ftpdir;
          else
            $ftpdir = "general";

          $path = "/pub/pwg/$ftpdir/minutes/$filename";
          break;
      case DOCUMENT_STATUS_WHITE_PAPER :
          $workgroup = new workgroup($this->workgroup_id);
          if ($workgroup->id == $this->workgroup_id)
            $ftpdir = $workgroup->ftpdir;
          else
            $ftpdir = "general";

          $path = "/pub/pwg/$ftpdir/white/$filename";
          break;
      case DOCUMENT_STATUS_CHARTER :
          if (!preg_match("/^ch-[-a-z0-9]-[0-9]{8}\\.(doc|docx|pdf)/i", $filename))
          {
            $error = "Approved charters must have names of the form 'ch-name-YYYYMMDD.ext'.";
            return ("");
          }

          $workgroup = new workgroup($this->workgroup_id);
          if ($workgroup->id == $this->workgroup_id)
            $ftpdir = $workgroup->ftpdir;
          else
            $ftpdir = "general";

          $path = "/pub/pwg/$ftpdir/charter/$filename";
          break;
      case DOCUMENT_STATUS_INFORMATIONAL :
          if (!preg_match("/^(bp|info|req)-[-a-z0-9]-[0-9]{8}\\.(doc|docx|pdf)/i", $filename))
          {
            $error = "Approved informational documents must have names of the form '(bp|info|req)-name-YYYYMMDD.ext'.";
            return ("");
          }

          $path = "/pub/pwg/informational/$filename";
          break;
      case DOCUMENT_STATUS_CANDIDATE_STANDARD :
          if (!preg_match("/^cs-[-a-z0-9][0-9][0-9]-[0-9]{8}-51[0-9][0-9]\\.[0-9]+\\.(doc|docx|pdf)/i", $filename))
          {
            $error = "Approved candidate standards must have names of the form 'cs-nameNN-YYYYMMDD-51XX.X.ext'.";
            return ("");
          }

          $path = "/pub/pwg/candidates/$filename";
          break;
      case DOCUMENT_STATUS_FULL_STANDARD :
          if (!preg_match("/^cs-[-a-z0-9][0-9][0-9]-[0-9]{8}-51[0-9][0-9]\\.[0-9]+\\.(doc|docx|pdf)/i", $filename))
          {
            $error = "Approved full standards must have names of the form 'std-nameNN-YYYYMMDD-51XX.X.ext'.";
            return ("");
          }

          $path = "/pub/pwg/standards/$filename";
          break;
    }

    $url = "ftp://$FTP_USER:$FTP_PASSWORD@ftp.pwg.org$path";
    if (copy($tmp_name, $url))
      return ("https://ftp.pwg.org$path");

    if ($temp = error_get_last())
      $error = $temp["message"];
    else
      $error = htmlspecialchars("Unable to upload '$filename' to the FTP server.");

    return ("");
  }


  //
  // 'document::validate()' - Validate the current document object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    $valid = TRUE;


    if (($this->series == 0 && $this->status >= DOCUMENT_STATUS_CANDIDATE_STANDARD) ||
        ($this->series != 0 && $this->status > DOCUMENT_STATUS_OBSOLETE && $this->status < DOCUMENT_STATUS_INFORMATIONAL) ||
        ($this->series != 0 && $this->series < 5100 || $this->series > 5199))
    {
      $this->series_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->series_valid = TRUE;

    if (($this->number == 0 && $this->status >= DOCUMENT_STATUS_CANDIDATE_STANDARD) ||
        ($this->number != 0 && $this->status > DOCUMENT_STATUS_OBSOLETE && $this->status < DOCUMENT_STATUS_INFORMATIONAL))
    {
      $this->number_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->number_valid = TRUE;

    if ($this->version != "" && !preg_match("/^[0-9]+\\.[0-9]+\$/", $this->version))
    {
      $this->version_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->version_valid = TRUE;

    if ($this->title == "")
    {
      $this->title_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->title_valid = TRUE;

    if ($this->contents == "" && $this->status != DOCUMENT_STATUS_CONFERENCE_CALL_MINUTES && $this->status != DOCUMENT_STATUS_FACE_TO_FACE_MINUTES)
    {
      $this->contents_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->contents_valid = TRUE;

    if ($this->editable_url != "" && !preg_match("/^https:\\/\\/ftp\\.pwg\\.org\\/pub\\/pwg\\//", $this->editable_url))
    {
      $this->editable_url_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->editable_url_valid = TRUE;

    if ($this->clean_url != "" && !preg_match("/^https:\\/\\/ftp\\.pwg\\.org\\/pub\\/pwg\\//", $this->clean_url))
    {
      $this->clean_url_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->clean_url_valid = TRUE;

    if ($this->redline_url != "" && !preg_match("/^https:\\/\\/ftp\\.pwg\\.org\\/pub\\/pwg\\//", $this->redline_url))
    {
      $this->redline_url_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->redline_url_valid = TRUE;

    return ($valid);
  }


  //
  // 'document::view()' - View an document.
  //

  function
  view($options = "", $level = 2, $links = TRUE, $btnclass = "btn-xs")
  {
    global $html_path, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_OFFICER, $html_login_url;


    $title    = $this->display_name();
    $contents = html_format($this->contents, FALSE, $level + 1);

    if (preg_match("/-(20[0-9]{2,2})([0-9]{2,2})([0-9]{2,2})/", $this->clean_url, $matches))
      $date = html_date("$matches[1]-$matches[2]-$matches[3]");
    else
      $date = html_date($this->modify_date);

    if ($this->clean_url != "")
      $url = htmlspecialchars($this->clean_url, ENT_QUOTES);
    else if ($this->editable_url != "")
      $url = htmlspecialchars($this->editable_url, ENT_QUOTES);
    else
      $url = htmlspecialchars($this->redline_url, ENT_QUOTES);

    print("<h$level>$title <small>$date</small></h$level>\n"
	 ."$contents\n");
    if ($url != "" || ($links && ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)))
    {
      print("<p>");

      if ($url != "")
	print("<a class=\"btn btn-primary $btnclass\" href=\"$url\">View</a>\n");

      if ($links && ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID))
	print("<a class=\"btn btn-default $btnclass\" href=\"${html_path}dynamo/documents.php?U$this->id$options\">Edit</a>\n");

      $stmt  = db_query("SELECT id FROM issue WHERE document_id=?", array($this->id));
      $count = db_count($stmt);
      if ($count == 0)
      {
        if ($LOGIN_ID)
	{
	  print("<a class=\"btn btn-default $btnclass\" href=\"${html_path}dynamo/issues.php?U+Z$this->id\">Report Issue</a>\n");
        }
        else
        {
          $url = "$html_login_url?PAGE=" . urlencode("${html_path}dynamo/issues.php?U+Z$this->id");
	  print("<a class=\"btn btn-default $btnclass\" href=\"$url\">Login to Report Issue</a>\n");
        }
      }
      else if ($count == 1)
	print("<a class=\"btn btn-default $btnclass\" href=\"${html_path}dynamo/issues.php?L+Z$this->id\">1 Reported Issue</a>\n");
      else
	print("<a class=\"btn btn-default $btnclass\" href=\"${html_path}dynamo/issues.php?L+Z$this->id\">$count Reported Issues</a>\n");

      print("</p>\n");
    }
  }
}


//
// 'document_search()' - Return an array of document IDs for the given reference.
//

function				// O - Array of document objects
document_search($search = "",		// I - Search text
                $order = "-modify_date",// I - Order of objects
                $workgroup_id = -1,	// I - Which workgroup to limit to
                $min_status = DOCUMENT_STATUS_OBSOLETE,
                $max_status = DOCUMENT_STATUS_FULL_STANDARD)
                			// I - Min and max status
{
  global $DOCUMENT_COLUMNS;

  if ($min_status != DOCUMENT_STATUS_OBSOLETE || $max_status != DOCUMENT_STATUS_FULL_STANDARD)
  {
    $keyvals = array("status>=" => $min_status, "status<=" => $max_status);
    if ($workgroup_id >= 0)
      $keyvals["workgroup_id"] = $workgroup_id;
  }
  else if ($workgroup_id >= 0)
    $keyvals = array("workgroup_id" => $workgroup_id);
  else
    $keyvals = null;

  return (db_search("document", $DOCUMENT_COLUMNS, $keyvals, $search, $order));
}


//
// 'document_select()' - Show the (approved/published) document selection control.
//

function
document_select(
    $formname = "document_id",		// I - Form name to use
    $id = 0,				// I - Currently selected document, if any
    $any_id = "",			// I - Allow "any document"?
    $prefix = "",			// I - Prefix on values
    $editable = FALSE)			// I - Select editable documents?
{
  global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR, $LOGIN_IS_OFFICER;


  print("<select name=\"$formname\">");

  if ($any_id != "")
    print("<option value=\"0\">$prefix$any_id</option>");

  if ($editable)
  {
    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER)
      $results = db_query("SELECT id, title, series, number FROM document WHERE status >= " . DOCUMENT_STATUS_INITIAL_WORKING_DRAFT . " ORDER BY status,series,number,title");
    else
      $results = db_query("SELECT id, title, series, number FROM document WHERE status >= " . DOCUMENT_STATUS_INITIAL_WORKING_DRAFT . " AND create_id = ? ORDER BY status,series,number,title", array($LOGIN_ID));
  }
  else
    $results = db_query("SELECT id, title, series, number FROM document WHERE status >= " . DOCUMENT_STATUS_WHITE_PAPER . " ORDER BY status,series,number,title");

  while ($row = db_next($results))
  {
    $did    = $row["id"];
    $title  = html_abbreviate($row["title"]);
    $series = $row["series"];
    $number = $row["number"];

    if ($series && $number)
    {
      if ($series == 5108) // For Pete
        $name = sprintf("PWG 5108.%02d: %s", $number, $title);
      else
        $name = "PWG $series.$number: $title";
    }
    else
      $name = $title;

    if ($did == $id)
      print("<option value=\"$did\" selected>$prefix$name</option>");
    else
      print("<option value=\"$did\">$prefix$name</option>");
  }

  print("</select>");
}


?>
