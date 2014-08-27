<?php
//
// Issue tracking page for errata and document updates...
//

//
// Include necessary headers...
//

include_once "phplib/db-submission.php";

// Get command-line options...
//
// Usage: evefile.php/id/filename.plist

if (!$LOGIN_ID)
{
  header("Location: $html_login_url?PAGE=" . urlencode("${html_path}dynamo/evefile.php$PATH_INFO"));
  exit(0);
}

if (!preg_match("/^\\/([1-9][0-9]*)\\/([a-z]+\\.plist)\$/", $PATH_INFO, $matches))
{
  header("Location: $html_path");
  exit(0);
}

$id   = (int)$matches[1];
$name = $matches[2];

$submission = new submission($id);
if ($submission->id != $id)
{
  header("Status: 404");
  exit(0);
}

$filename = "$SUBMISSION_DIR/$id/$name";
if (!file_exists($filename))
{
  header("Status: 404");
  exit(0);
}

if ($LOGIN_ID != $submission->create_id &&
    $LOGIN_ID != $submission->reviewer1_id &&
    $LOGIN_ID != $submission->reviewer2_id)
{
  header("Status: 403");
  exit(0);
}

header("Content-Type: text/xml");
header("Content-Length: " . filesize($filename));

readfile($filename);

?>
