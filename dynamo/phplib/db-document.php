<?php
//
// Class for the document table.
//

include_once "site.php";
include_once "db-workgroup.php";

define("DOCUMENT_STATUS_WITHDRAWN", 0);
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
  DOCUMENT_STATUS_WITHDRAWN => "Withdrawn",
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
    $this->number       = "";
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
    db_query("DELETE FROM document WHERE id=$this->id");
    $this->clear();
  }


  //
  // 'document::display_name()' - Return the display name for a document object.
  //

  function				// O - Display name
  display_name()
  {
    if ($this->number != "")
      return ("PWG $this->number: " . htmlspecialchars($this->title, ENT_QUOTES));
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
      html_form_text("number", "5100.1, etc.", $this->number);
    else if ($this->number != "")
      print(htmlspecialchars($this->number));
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
    $this->clear();

    $result = db_query("SELECT * FROM document WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);
    $this->id           = $row["id"];
    $this->replaces_id  = $row["replaces_id"];
    $this->workgroup_id = $row["workgroup_id"];
    $this->status       = $row["status"];
    $this->number       = $row["number"];
    $this->version      = $row["version"];
    $this->title        = $row["title"];
    $this->contents     = $row["contents"];
    $this->editable_url = $row["editable_url"];
    $this->clean_url    = $row["clean_url"];
    $this->redline_url  = $row["redline_url"];
    $this->create_date  = $row["create_date"];
    $this->create_id    = $row["create_id"];
    $this->modify_date  = $row["modify_date"];
    $this->modify_id    = $row["modify_id"];

    db_free($result);

    return (TRUE);
  }


  //
  // 'document::loadform()' - Load an document object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST;


    if (!html_form_validate())
      return (FALSE);

    if (array_key_exists("status", $_POST))
      $this->status = (int)$_POST["status"];

    if (array_key_exists("replaces_id", $_POST))
      $this->replaces_id = (int)$_POST["replaces_id"];

    if (array_key_exists("title", $_POST))
      $this->title = trim($_POST["title"]);

    if (array_key_exists("number", $_POST))
      $this->number = trim($_POST["number"]);

    if (array_key_exists("version", $_POST))
      $this->version = trim($_POST["version"]);

    if (array_key_exists("contents", $_POST))
      $this->contents = trim($_POST["contents"]);

    if (array_key_exists("editable_url", $_POST))
      $this->editable_url = str_replace("ftp://ftp.pwg.org/", "http://ftp.pwg.org/", trim($_POST["editable_url"]));

    if (array_key_exists("clean_url", $_POST))
      $this->clean_url = str_replace("ftp://ftp.pwg.org/", "http://ftp.pwg.org/", trim($_POST["clean_url"]));

    if (array_key_exists("redline_url", $_POST))
      $this->redline_url = str_replace("ftp://ftp.pwg.org/", "http://ftp.pwg.org/", trim($_POST["redline_url"]));

    // TODO: Implement file uploads

    if (array_key_exists("workgroup_id", $_POST))
      $this->workgroup_id = (int)$_POST["workgroup_id"];

    return ($this->validate());
  }


  //
  // 'document::save()' - Save a document object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR, $LOGIN_IS_OFFICER, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->replaces_id)
    {
      $document = new document($this->replaces_id);

      if ($document->id != $this->replaces_id)
        return (FALSE);

      if ($document->status != DOCUMENT_STATUS_WITHDRAWN)
      {
        if ($LOGIN_ID == $document->create_id || $LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER)
        {
          if (db_query("UPDATE document SET "
                      ."status = " . DOCUMENT_STATUS_WITHDRAWN
                      .", modify_date='" . db_escape($this->modify_date) . "'"
                      .", modify_id = $LOGIN_ID"
                      ." WHERE id = $this->replaces_id") === FALSE)
            return (FALSE);
        }
        else
          return (FALSE);
      }
    }

    if ($this->id > 0)
    {
      return (db_query("UPDATE document "
                      ." SET replaces_id = $this->replaces_id"
                      .", workgroup_id = $this->workgroup_id"
                      .", status = $this->status"
                      .", number = '" . db_escape($this->number) . "'"
                      .", version = '" . db_escape($this->version) . "'"
                      .", title = '" . db_escape($this->title) . "'"
                      .", contents = '" . db_escape($this->contents) . "'"
                      .", editable_url = '" . db_escape($this->editable_url) . "'"
                      .", clean_url = '" . db_escape($this->clean_url) . "'"
                      .", redline_url = '" . db_escape($this->redline_url) . "'"
                      .", modify_date = '" . db_escape($this->modify_date) . "'"
                      .", modify_id = $this->modify_id"
                      ." WHERE id = $this->id") !== FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if (db_query("INSERT INTO document VALUES"
                  ."(NULL"
                  .", $this->replaces_id"
                  .", $this->workgroup_id"
                  .", $this->status"
                  .", '" . db_escape($this->number) . "'"
                  .", '" . db_escape($this->version) . "'"
                  .", '" . db_escape($this->title) . "'"
                  .", '" . db_escape($this->contents) . "'"
                  .", '" . db_escape($this->editable_url) . "'"
                  .", '" . db_escape($this->clean_url) . "'"
                  .", '" . db_escape($this->redline_url) . "'"
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
  // 'document::validate()' - Validate the current document object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    $valid = TRUE;


    if (($this->number == "" && $this->status >= DOCUMENT_STATUS_CANDIDATE_STANDARD) ||
        ($this->number != "" && $this->status > DOCUMENT_STATUS_WITHDRAWN && $this->status < DOCUMENT_STATUS_CANDIDATE_STANDARD) ||
        ($this->number != "" && !preg_match("/^51[0-9][0-9]\\.[0-9]+(-[0-9]{4}|)\$/", $this->number)))
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

    if ($this->editable_url != "" && !preg_match("/^http:\\/\\/ftp\\.pwg\\.org\\/pub\\/pwg\\//", $this->editable_url))
    {
      $this->editable_url_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->editable_url_valid = TRUE;

    if ($this->clean_url != "" && !preg_match("/^http:\\/\\/ftp\\.pwg\\.org\\/pub\\/pwg\\//", $this->clean_url))
    {
      $this->clean_url_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->clean_url_valid = TRUE;

    if ($this->redline_url != "" && !preg_match("/^http:\\/\\/ftp\\.pwg\\.org\\/pub\\/pwg\\//", $this->redline_url))
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
    global $html_path, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_OFFICER;


    $title    = $this->display_name();
    $contents = html_format($this->contents, FALSE, $level + 1);
    $date     = html_date($this->modify_date);

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
      if ($links && ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID))
	print("<a class=\"btn btn-default $btnclass\" href=\"${html_path}dynamo/documents.php?U$this->id$options\">Edit</a>\n");
      if ($url != "")
	print("<a class=\"btn btn-primary $btnclass\" href=\"$url\">View</a>\n");
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
                $min_status = DOCUMENT_STATUS_WITHDRAWN,
                $max_status = DOCUMENT_STATUS_FULL_STANDARD)
                			// I - Min and max status
{
  if ($search != "")
  {
    // Convert the search string to an array of words...
    $words = html_search_words($search);

    // Loop through the array of words, adding them to the query...
    if ($workgroup_id >= 0)
      $query = " WHERE workgroup_id = $workgroup_id AND (";
    else
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

	$query .= "${subpre}title LIKE \"%$word%\"";
	$subpre = " OR ";
	$query .= "${subpre}number LIKE \"%$word%\"";
	$query .= "${subpre}version LIKE \"%$word%\"";
	$query .= "${subpre}contents LIKE \"%$word%\"";
	$query .= "${subpre}editable_url LIKE \"%$word%\"";
	$query .= "${subpre}clean_url LIKE \"%$word%\"";
	$query .= "${subpre}redline_url LIKE \"%$word%\"";

	$query .= ")";
	$prefix = $next;
	$logic  = '';
      }
    }

    $query .= ")";
  }
  else if ($workgroup_id >= 0)
    $query = " WHERE workgroup_id = $workgroup_id";
  else
    $query = "";

  if ($min_status != DOCUMENT_STATUS_WITHDRAWN || $max_status != DOCUMENT_STATUS_FULL_STANDARD)
  {
    if ($query == "")
      $query = " WHERE";
    else
      $query .= " AND";

    $query .= " status >= $min_status AND status <= $max_status";
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
  $result  = db_query("SELECT id FROM document$query");
  $matches = array();

  while ($row = db_next($result))
    $matches[sizeof($matches)] = $row["id"];

  // Free the query result and return the array...
  db_free($result);

  return ($matches);
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
      $results = db_query("SELECT id, title, number FROM document WHERE status >= " . DOCUMENT_STATUS_INITIAL_WORKING_DRAFT . " ORDER BY title");
    else
      $results = db_query("SELECT id, title, number FROM document WHERE status >= " . DOCUMENT_STATUS_INITIAL_WORKING_DRAFT . " AND create_id = $LOGIN_ID ORDER BY title");
  }
  else
    $results = db_query("SELECT id, title, number FROM document WHERE status >= " . DOCUMENT_STATUS_WHITE_PAPER . " ORDER BY title");

  while ($row = db_next($results))
  {
    $did    = $row["id"];
    $title  = htmlspecialchars($row["title"]);
    $number = $row["number"];

    if ($number != "")
      $name = "PWG $number: $title";
    else
      $name = $title;

    if ($did == $id)
      print("<option value=\"$did\" selected>$prefix$name</option>");
    else
      print("<option value=\"$did\">$prefix$name</option>");
  }

  db_free($results);

  print("</select>");
}


?>
