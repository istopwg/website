#!/usr/bin/php -f
<?php
// Script to notify people about new IPP Everywhere Printer submissions in
// the last week...  Run from crontab on Sunday nights...
include_once "phplib/site.php";
include_once "phplib/db-printer.php";

// Who mail comes from - MUST be someone on the pwg-announce@pwg.org list
$FROM = "msweet@apple.com";
// Where mail goes to - normally pwg-announce@pwg.org
//$TO = "pwg-announce@pwg.org";
$TO = "msweet@apple.com";

if ($argc > 1)
  $DAYS = (int)$argv[1];
else
  $DAYS = 7;

//////// END OF CONFIGURABLE STUFF ////////

// Prepare a message of new printers...
$since   = db_datetime(time() - $DAYS * 24 * 60 * 60);
$replyto = "noreply@$SITE_HOSTNAME";
$message = "";
$ids     = printer_search();

foreach ($ids as $id)
{
  $printer = new printer($id);

  if ($printer->id == $id && $printer->create_date >= $since)
    $message .= "  - $printer->model\n";
}

if ($message != "")
{
  // Send the email...
  $subject = "New IPP Everywhere Self-Certified Printers";
  $headers = "From: $FROM\n"
            ."Reply-To: $replyto\n"
            ."Mime-Version: 1.0\n"
            ."Content-Type: text/plain\n";

  $message = "The following new IPP Everywhere Printers have been submitted to the PWG web site:\n\n"
            ."$message\n"
            ."You can see these and other printers on the IPP Everywhere printer page:\n\n"
             ."    http://www.pwg.org/printers\n";

  // Send the email notification...
  mail($TO, $subject, wordwrap($message), $headers);
}
