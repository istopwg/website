<?php
//
// Issue tracking page for errata and document updates...
//

//
// Include necessary headers...
//

include_once "phplib/db-printer.php";
include_once "phplib/plist.php";

// Get command-line options...
//
// Usage: evesubmit.php

site_header("Submit IPP Everywhere Self-Certification");

if (!$LOGIN_ID)
{
  print("<p>Access to the IPP Everywhere Printer self-certification submission page requires a login on this web site and PWG membership.</p>\n"
       ."<p><a class=\"btn btn-default\" href=\"$html_login_url?PAGE=" . urlencode("${html_path}dynamo/evesubmit.php") . "\">Login or Request Account</a></p>\n");
  site_footer();
  exit(0);
}

if (!$LOGIN_IS_MEMBER)
{
  print("<p>Access to the IPP Everywhere Printer self-certification submission page requires PWG membership.</p>\n"
       ."<p><a class=\"btn btn-default\" href=\"${html_path}dynamo/request.php\">Request Access</a> "
       ."<a class=\"btn btn-default\" href=\"${html_path}pwg-logos/members.html#JOINING\">Membership Information</a></p>\n");
  site_footer();
  exit(0);
}

// Load form data as possible...
$contact_name          = $LOGIN_NAME;
$contact_email         = $LOGIN_EMAIL;
$organization_id       = $LOGIN_ORGANIZATION;
$product_family        = "";
$url                   = "";
$models                = "";
$cert_version          = "";
$used_approved         = FALSE;
$used_prodready        = FALSE;
$printed_correctly     = FALSE;
$bonjour_file_plist    = NULL;
$bonjour_file_error    = "";
$ipp_file_plist        = NULL;
$ipp_file_error        = "";
$document_file_plist   = NULL;
$document_file_error   = "";

if ($REQUEST_METHOD == "POST")
{
  if (array_key_exists("contact_name", $_POST))
    $contact_name = trim($_POST["contact_name"]);

  if (array_key_exists("contact_email", $_POST))
    $contact_email = trim($_POST["contact_email"]);

  if (array_key_exists("organization_id", $_POST) &&
      preg_match("/^o[0-9]+\$/", $_POST["organization_id"]))
    $organization_id = (int)substr($_POST["organization_id"], 1);

  if (array_key_exists("product_family", $_POST))
    $product_family = trim($_POST["product_family"]);

  if (array_key_exists("url", $_POST))
    $url = trim($_POST["url"]);

  if (array_key_exists("models", $_POST))
    $models = trim($_POST["models"]);

  if (array_key_exists("cert_version", $_POST))
    $cert_version = trim($_POST["cert_version"]);

  if (array_key_exists("used_approved", $_POST))
    $used_approved = TRUE;
  else
    $used_approved = FALSE;

  if (array_key_exists("used_prodready", $_POST))
    $used_prodready = TRUE;
  else
    $used_prodready = FALSE;

  if (array_key_exists("printed_correctly", $_POST))
    $printed_correctly = TRUE;
  else
    $printed_correctly = FALSE;

  if (array_key_exists("print_server", $_POST))
    $print_server = TRUE;
  else
    $print_server = FALSE;

  if (array_key_exists("bonjour_file", $_FILES) && array_key_exists("tmp_name", $_FILES["bonjour_file"]))
  {
    $filename = $_FILES["bonjour_file"]["tmp_name"];
    if ($filename == "")
    {
      $bonjour_file_error = "No file provided.";
    }
    else
    {
      $bonjour_file_plist = plist_read_file($filename);
      $bonjour_file_error = printer_validate_plist($bonjour_file_plist, $cert_version, "bonjour", $print_server);
    }
  }
  else
    $bonjour_file_error = "No file provided.";

  if (array_key_exists("ipp_file", $_FILES) && array_key_exists("tmp_name", $_FILES["ipp_file"]))
  {
    $filename = $_FILES["ipp_file"]["tmp_name"];
    if ($filename == "")
    {
      $ipp_file_error = "No file provided.";
    }
    else
    {
      $ipp_file_plist = plist_read_file($filename);
      $ipp_file_error = printer_validate_plist($ipp_file_plist, $cert_version, "ipp", $print_server);
    }
  }
  else
    $ipp_file_error = "No file provided.";

  if (array_key_exists("document_file", $_FILES) && array_key_exists("tmp_name", $_FILES["document_file"]))
  {
    $filename = $_FILES["document_file"]["tmp_name"];

    if ($filename == "")
    {
      $document_file_error = "No file provided.";
    }
    else
    {
      $document_file_plist = plist_read_file($filename);
      $document_file_error = printer_validate_plist($document_file_plist, $cert_version, "document", $print_server);
    }
  }
  else
    $document_file_error = "No file provided.";
}

// Validate form input...
$valid = TRUE;

if ($organization_id > 0)
{
  $org = new organization($organization_id);
  if ($org->id != $organization_id)
  {
    $organization_id_valid = FALSE;
    $valid = FALSE;
  }
  else if ($org->status != ORGANIZATION_STATUS_NON_VOTING &&
           $org->status != ORGANIZATION_STATUS_SMALL_VOTING&&
           $org->status != ORGANIZATION_STATUS_LARGE_VOTING)
  {
    $organization_id_valid = FALSE;
    $valid = FALSE;
  }
  else
    $organization_id_valid = TRUE;
}
else
  $organization_id_valid = TRUE;

if ($contact_name == "" && $REQUEST_METHOD == "POST")
{
  $contact_name_valid = FALSE;
  $valid = FALSE;
}
else
  $contact_name_valid = TRUE;

if (!validate_email($contact_email) && $REQUEST_METHOD == "POST")
{
  $contact_email_valid = FALSE;
  $valid = FALSE;
}
else
  $contact_email_valid = TRUE;

if ($product_family == "" && $REQUEST_METHOD == "POST")
{
  $product_family_valid = FALSE;
  $valid = FALSE;
}
else
  $product_family_valid = TRUE;

if ($models == "" && $REQUEST_METHOD == "POST")
{
  $models_valid = FALSE;
  $valid = FALSE;
}
else
  $models_valid = TRUE;

if ($url != "" && !validate_url($url))
{
  $url_valid = FALSE;
  $valid = FALSE;
}
else
  $url_valid = TRUE;

if (!array_key_exists($cert_version, $CERT_VERSIONS) && $REQUEST_METHOD == "POST")
{
  $cert_version_valid = FALSE;
  $valid = FALSE;
}
else
  $cert_version_valid = TRUE;

if ((!$used_approved || !$used_prodready || !$printed_correctly) && $REQUEST_METHOD == "POST")
{
  $checklist_valid = FALSE;
  $valid = FALSE;
}
else
  $checklist_valid = TRUE;

if (($bonjour_file_plist == NULL || $bonjour_file_error != "") && $REQUEST_METHOD == "POST")
{
  $bonjour_file_valid = FALSE;
  $valid = FALSE;
}
else
{
  $bonjour_file_valid = TRUE;

  if ($REQUEST_METHOD == "POST")
    $bonjour_file_error = "Results are valid.";
}


if (($ipp_file_plist == NULL || $ipp_file_error != "") && $REQUEST_METHOD == "POST")
{
  $ipp_file_valid = FALSE;
  $valid = FALSE;
}
else
{
  $ipp_file_valid = TRUE;
  if ($REQUEST_METHOD == "POST")
    $ipp_file_error = "Results are valid.";
}

if (($document_file_plist == NULL || $document_file_error != "") && $REQUEST_METHOD == "POST")
{
  $document_file_valid = FALSE;
  $valid = FALSE;
}
else
{
  $document_file_valid = TRUE;
  if ($REQUEST_METHOD == "POST")
    $document_file_error = "Results are valid.";
}

// Post results if everything is OK...
if ($REQUEST_METHOD == "POST" && $valid)
{
  print("<h2>Results</h2>\n"
       ."<p>The following printers have been published to PWG.org:</p>\n"
       ."<ul>\n");

  $ids = printer_publish_submission($organization_id, $product_family, $url, $models, $cert_version, $bonjour_file_plist, $ipp_file_plist, $document_file_plist);
  printer_notify_users($ids, $contact_name, $contact_email);

  print("</ul>\n"
       ."<p><a class=\"btn btn-default\" href=\"eveprinters.php\">Find Printers</a></p>\n");

  site_footer();
  exit(0);
}

// Show form...
print("<h2>Information</h2>\n");

if (!$valid)
{
  // Show error...
  html_show_error("There was a problem with your submission. Please correct the highlighted fields below.");
}

html_form_start("${html_path}dynamo/evesubmit.php", FALSE, TRUE);

// organization_id
html_form_field_start("+organization_id", "Organization Name", $organization_id_valid);
organization_select("organization_id", $organization_id, "-- Choose --", "", "", "", 1);
html_form_field_end();

// contact_name
html_form_field_start("+contact_name", "Contact Name", $contact_name_valid);
html_form_text("contact_name", "Contact for submission", $contact_name);
html_form_field_end();

// contact_email
html_form_field_start("+contact_email", "Contact Email", $contact_email_valid);
html_form_email("contact_email", "name@example.com", $contact_email);
html_form_field_end();

// product_family
html_form_field_start("+product_family", "Product Family Name", $product_family_valid);
html_form_text("product_family", "Name of product family being submitted", $product_family);
html_form_field_end();

// url
html_form_field_start("url", "Product Family URL", $url_valid);
html_form_url("url", "http://www.example.com/products", $url);
html_form_field_end();

// models
html_form_field_start("+models", "Models", $models_valid);
html_form_text("models", "Make Model\nMake Model\n...", $models,
	       "List the make and model of every printer in the product family, one per line.", 20);
html_form_field_end();

// cert_version
html_form_field_start("+cert_version", "Self-Certification Manual");
html_form_select("cert_version", $CERT_VERSIONS, "", $cert_version);
html_form_field_end();

// used_approved, used_prodready, printed_correctly
html_form_field_start("+used_approved", "Submission Checklist", $checklist_valid);
html_form_checkbox("used_approved", "Used PWG self-certification tools.", $used_approved, "As supplied on the PWG FTP server.");
html_form_checkbox("used_prodready", "Used Production-Ready Code.", $used_prodready, "Production-Ready Code: Software and/or firmware that is considered ready to be included in products shipped to customers.");
html_form_checkbox("printed_correctly", "All output printed correctly.", $printed_correctly, "As documented in section 7.3 of the IPP Everywhere Printer Self-Certification Manual 1.0.");
html_form_checkbox("print_server", "Results are for print server software.", $print_server);
html_form_field_end();

// files
html_form_field_start("+bonjour_file", "Bonjour Test Results");
html_form_file("bonjour_file", "", $bonjour_file_error);
html_form_field_end();

html_form_field_start("+ipp_file", "IPP Test Results");
html_form_file("ipp_file", "", $ipp_file_error);
html_form_field_end();

html_form_field_start("+document_file", "Document Data Test Results");
html_form_file("document_file", "", $document_file_error);
html_form_field_end();

// Submit
html_form_end(array("SUBMIT" => "+Submit Self-Certification"));

site_footer();

?>
