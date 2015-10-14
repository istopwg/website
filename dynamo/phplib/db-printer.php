<?php
//
// Class for the printer table.
//

include_once "site.php";
include_once "db.php";


$PRINTER_COLUMNS = array(
  "organization_id" => PDO::PARAM_INT,
  "product_family" => PDO::PARAM_STR,
  "model" => PDO::PARAM_STR,
  "url" => PDO::PARAM_STR,
  "cert_version" => PDO::PARAM_STR,
  "color_supported" => PDO::PARAM_BOOL,
  "duplex_supported" => PDO::PARAM_BOOL,
  "finishings_supported" => PDO::PARAM_BOOL,
  "ipps_supported" => PDO::PARAM_BOOL,
  "create_date" => PDO::PARAM_STR,
  "create_id" => PDO::PARAM_INT
);

$CERT_VERSIONS = array(
  "org.pwg.ipp-everywhere.20151009" => "1.0 Stable (October 9, 2015)",
  "org.pwg.ipp-everywhere.20150415" => "1.0 Interim (April 15, 2015)",
  "org.pwg.ipp-everywhere.20140826" => "1.0 Interim (August 26, 2014)"
);


class printer
{
  //
  // Instance variables...
  //

  var $id;
  var $organization_id;
  var $product_family;
  var $model;
  var $url;
  var $cert_version;
  var $color_supported;
  var $duplex_supported;
  var $finishings_supported;
  var $ipps_supported;
  var $create_date;
  var $create_id;


  //
  // 'printer::printer()' - Create a printer object.
  //

  function				// O - New Article object
  printer($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'printer::clear()' - Initialize a new a printer object.
  //

  function
  clear()
  {
    global $LOGIN_ID;

    $this->id                   = 0;
    $this->organization_id      = 0;
    $this->product_family       = "";
    $this->model                = "";
    $this->url                  = "";
    $this->cert_version         = "";
    $this->color_supported      = 0;
    $this->duplex_supported     = 0;
    $this->finishings_supported = 0;
    $this->ipps_supported       = 0;
    $this->create_date          = "";
    $this->create_id            = $LOGIN_ID;
  }


  //
  // 'printer::delete()' - Delete a printer object.
  //

  function
  delete()
  {
    db_delete("printer", $this->id);
    $this->clear();
  }


  //
  // 'printer::load()' - Load a printer object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    global $PRINTER_COLUMNS;

    $this->clear();

    if (!db_load($this, "printer", $id, $PRINTER_COLUMNS))
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }


  //
  // 'printer::save()' - Save a printer object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PRINTER_COLUMNS;


    if ($this->id > 0)
      return (db_save($this, "printer", $this->id, $PRINTER_COLUMNS));

    $this->create_date = db_datetime();
    $this->create_id   = $LOGIN_ID;

    if (($id = db_create($this, "printer", $PRINTER_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }
}


//
// 'printer_search()' - Get a list of Article IDs.
//

function				// O - Array of Article IDs
printer_search($search = "",		// I - Search string
               $color = -1,		// I - Color
               $duplex = -1,		// I - Duplex
               $finishings = -1,	// I - Finishings
               $ipps = -1,		// I - IPPS support
	       $order = "")		// I - Order fields
{
  global $PRINTER_COLUMNS;

  if ($color >= 0 || $duplex >= 0 || $finishings >= 0)
  {
    $keyvals = array();
    if ($color >= 0)
      $keyvals["color_supported"] = $color;
    if ($duplex >= 0)
      $keyvals["duplex_supported"] = $duplex;
    if ($finishings >= 0)
      $keyvals["finishings_supported"] = $finishings;
    if ($ipps >= 0)
      $keyvals["ipps_supported"] = $ipps;
  }
  else
    $keyvals = null;

  return (db_search("printer", $PRINTER_COLUMNS, $keyvals, $search, $order));
}


//
// 'printer_notify_users()' - Notify users of submissions.
//

function
printer_notify_users($ids, $contact_name, $contact_email)
{
  global $_POST, $SITE_EMAIL, $SITE_HOSTNAME, $SITE_URL, $LOGIN_NAME, $LOGIN_EMAIL;


  // Emails always go to the contact in the submission, and are Cc'd to the
  // IPP everywhere self-cert list.
  $to      = "$contact_name <$contact_email>";
  $from    = $LOGIN_EMAIL;
  $replyto = "noreply@$SITE_HOSTNAME";
//  $cc      = "Cc: ippeveselfcert@pwg.org\n";
  $cc      = "Cc: msweet@apple.com\n";
  if ($contact_email != $LOGIN_EMAIL)
    $cc .= "Cc: $LOGIN_EMAIL\n";

  // Send the email...
  $subject = "IPP Everywhere Self-Certification Submission";
  $headers = "From: $from\n"
	    ."Reply-To: $replyto\n"
	    ."$cc"
	    ."Mime-Version: 1.0\n"
	    ."Content-Type: text/plain\n";

  $message = "$LOGIN_NAME has submitted the following printers to the IPP Everywhere self-certification page:\n\n";
  foreach ($ids as $id)
  {
    $printer = new printer($id);
    $message .= "- $printer->model\n";
  }

  $message .= "\nYou can see these and other printers on the IPP Everywhere printer page:\n\n"
             ."    http://www.pwg.org/printers\n";

  // Send the email notification...
  mail($to, $subject, wordwrap($message), $headers);
}


//
// 'printer_publish_submission()' - Publish printers in a submission.
//

function				// O - Array of printer IDs
printer_publish_submission($organization_id, $product_family, $url, $models, $cert_version, $bonjour_file_plist, $ipp_file_plist, $document_file_plist)
{
  $ids    = array();
  $errors = "";

  $models     = explode("\n", $models);
  $color      = 0;
  $duplex     = 0;
  $finishings = 0;
  $ipps       = 0;

  $response = $ipp_file_plist["Tests"][8]["ResponseAttributes"][1];
  if (array_key_exists("color-supported", $response))
    $color = $response["color-supported"];
  if (array_key_exists("finishings-supported", $response))
    $finishings = is_array($response["finishings-supported"]);
  if (array_key_exists("sides-supported", $response))
    $duplex = is_array($response["sides-supported"]);

  if (array_key_exists("Successful", $bonjour_file_plist["Tests"][4]))
    $ipps = $bonjour_file_plist["Tests"][4]["Successful"];

  foreach ($models as $model)
  {
    $printer = new printer();
    $printer->organization_id      = $organization_id;
    $printer->product_family       = $product_family;
    $printer->model                = trim($model);
    $printer->url                  = $url;
    $printer->cert_version         = $cert_version;
    $printer->color_supported      = $color;
    $printer->duplex_supported     = $duplex;
    $printer->finishings_supported = $finishings;
    $printer->ipps_supported       = $ipps;

    if ($printer->save())
    {
      $ids[sizeof($ids)] = $printer->id;
      print("<li>" . htmlspecialchars(trim($model)) . "</li>\n");
    }
    else
      $errors .= "<li>" . htmlspecialchars(trim($model)) . "</li>\n";
  }

  if ($errors)
    print("</ul>\n"
         ."<p>The following printers could not be published:</p>\n"
         ."<ul>\n"
         ."$errors");

  return ($ids);
}


//
// 'printer_validate_plist()' - Validate the content of a submission plist.
//

function				// O - String containing errors or "" for OK
printer_validate_plist($plist,		// I - plist to validate
                       $cert_version)	// I - Certification version
{
  $tests = array(
    "org.pwg.ipp-everywhere.20140826.bonjour" => 10,
    "org.pwg.ipp-everywhere.20140826.document" => 34,
    "org.pwg.ipp-everywhere.20140826.ipp" => 28,
    "org.pwg.ipp-everywhere.20150415.bonjour" => 10,
    "org.pwg.ipp-everywhere.20150415.document" => 34,
    "org.pwg.ipp-everywhere.20150415.ipp" => 28,
    "org.pwg.ipp-everywhere.20151009.bonjour" => 10,
    "org.pwg.ipp-everywhere.20151009.document" => 34,
    "org.pwg.ipp-everywhere.20151009.ipp" => 28
  );

  if ($plist == NULL)
    return ("Unable to parse plist file.");

  if (!array_key_exists("Successful", $plist))
    return ("Missing Successful key in plist file.");

  if (!array_key_exists("Tests", $plist))
    return ("Missing Tests key in plist file.");

  foreach ($plist["Tests"] as $test)
  {
    if (!array_key_exists("Name", $test))
      return ("Error: Missing Name key for test in plist file.");

    if (!array_key_exists("Successful", $test))
      return ("Error: Missing Successful key for test in plist file.");

    if (!array_key_exists("FileId", $test))
      return ("Error: Missing FileId key in plist file.");
  }

  $fileid = $plist["Tests"][0]["FileId"];

  if (substr($fileid, 0, 31) != $cert_version || !array_key_exists($fileid, $tests))
    return (htmlspecialchars("Invalid FileId '$fileid'."));

  if (sizeof($plist["Tests"]) != $tests[$fileid])
    return ("Wrong number of Tests in plist file.");

  return ("");
}

?>
