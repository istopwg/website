<?php
//
// "$Id: logout.php 98 2013-08-21 16:28:15Z msweet $"
//
// Logout page...
//

include_once "phplib/site.php";

if (array_key_exists("PAGE", $_GET))
  $page = $_GET["PAGE"];
else
  $page = "${html_path}index.html";

if (!preg_match("/^(\\/dynamo\\/[a-z]+|dynamo\\/[a-z]+)\\.php(|\\?.*)\$/", $page) && !preg_match("/\\.html\$/", $page))
  $page = "{$html_path}index.html";

auth_logout();

header("Location: $page");

//
// End of "$Id: logout.php 98 2013-08-21 16:28:15Z msweet $".
//
?>
