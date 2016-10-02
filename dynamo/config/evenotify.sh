#!/usr/bin/php -f
<?php
// Script to notify people about new IPP Everywhere Printer submissions in
// the last week...  Run from crontab on Sunday nights...
include_once "../phplib/db-printer.php";

// Who mail comes from - MUST be someone on the pwg-announce@pwg.org list
$FROM = "msweet@apple.com";

//////// END OF CONFIGURABLE STUFF ////////

// Prepare a message of new printers...
$since   = time() - 7 * 24 * 60 * 60;   // 1 week ago...
$to      = "pwg-announce@pwg.org";
$from    = $FROM;
$replyto = "noreply@$SITE_HOSTNAME";
$message = "";
$ids     = printer_search();

foreach ($ids as $id)
{
  $printer = new printer($id);

  if ($printer->id == $id && $printer->create_date >= $since)
    $message .= "- $printer->model\n";
}

if ($message != "")
{
  // Send the email...
  $subject = "New IPP Everywhere Self-Certified Printers";
  $headers = "From: $from\n"
            ."Reply-To: $replyto\n"
            ."Mime-Version: 1.0\n"
            ."Content-Type: text/plain\n";

  $message = "The following new IPP Everywhere Printers have be submitted to the PWG web site:\n\n"
            ."$message\n"
            ."You can see these and other printers on the IPP Everywhere printer page:\n\n"
             ."    http://www.pwg.org/printers\n";

  // Send the email notification...
  mail($to, $subject, wordwrap($message), $headers);
}
