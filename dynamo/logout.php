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
  $page = "index.php";

if (!preg_match("/^[a-z]+\\.php(|\\?.*)\$/", $page))
  $page = "index.php";

auth_logout();

header("Location: $page");

//
// End of "$Id: logout.php 98 2013-08-21 16:28:15Z msweet $".
//
?>
