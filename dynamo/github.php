<?php
// Script to receive Github webhook POSTs.

include_once "config/site.cfg";

// Verify we got a POST request with the right headers...
if (!array_key_exists("REQUEST_METHOD", $_SERVER) || $_SERVER["REQUEST_METHOD"] != "POST")
{
  // Unknown/unsupported request method...
  print("POST required.\n");
  exit(1);
}

if (!array_key_exists("HTTP_CONTENT_TYPE", $_SERVER) || $_SERVER["HTTP_CONTENT_TYPE"] != "application/json")
{
  print("Only JSON is supported.\n");
  exit(1);
}

if (!array_key_exists("HTTP_X_GITHUB_EVENT", $_SERVER) || $_SERVER["HTTP_X_GITHUB_EVENT"] != "push")
{
  print("Only push events are supported.\n");
  exit(1);
}

if (!array_key_exists("HTTP_X_HUB_SIGNATURE", $_SERVER))
{
  print("Missing X-Hub-Signature header.\n");
  exit(1);
}

$github_signature = $_SERVER["HTTP_X_HUB_SIGNATURE"];

// Get the POST data...
$post_data    = file_get_contents('php://input');
$my_signature = "sha1=" . hash_hmac('sha1', $post_data, $SITE_SECRET);

if ($github_signature != $my_signature)
{
  print("Signatures don't match.\n");
  exit(1);
}

// If we got this far, then we can update the local checkout...
chdir($SITE_DOCROOT);
$output = array();
$status = 0;

exec("git pull", $output, $status);

$message = "cd $SITE_DOCROOT\ngit pull\n";
foreach ($output as $line)
$message .= "$line\n";

if ($status)
  $subject = "[$SITE_HOSTNAME] Github Webhook Failed";
else
  $subject = "[$SITE_HOSTNAME] Github Webhook Succeeded";

mail($SITE_EMAIL, $subject, $message);

if ($status)
{
  print("Update failed.\n");
  exit(1);
}
else
{
  print("Updated successfully.\n");
  exit(0);
}
?>
