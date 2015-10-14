<?
//
// Authentication functions...
//

//
// Include necessary headers...
//

include_once "db.php";
include_once "db-user.php";


// Current user information in the global LOGIN_xxx variables...
$LOGIN_EMAIL        = "";
$LOGIN_ID           = 0;
$LOGIN_IS_ADMIN     = 0;
$LOGIN_IS_EDITOR    = 0;
$LOGIN_IS_MEMBER    = 0;
$LOGIN_IS_OFFICER   = 0;
$LOGIN_NAME         = "";
$LOGIN_ORGANIZATION = 0;
$LOGIN_PAGEMAX      = 10;
$LOGIN_TIMEZONE     = "UTC";

auth_current();


//
// 'auth_current()' - Return the currently logged in user.
//

function				// O - Current user ID or ""
auth_current()
{
  global $_SERVER, $LOGIN_EMAIL, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR,
         $LOGIN_IS_MEMBER, $LOGIN_IS_OFFICER,
         $LOGIN_NAME, $LOGIN_ORGANIZATION, $LOGIN_PAGEMAX, $LOGIN_TIMEZONE, $SITE_SECRET;


  // See if the SID cookie is set; if not, the user is not logged in...
  if (!array_key_exists("PWGSID", $_COOKIE))
    return ("");

  // Extract the "username:hash" from the SID string...
  $cookie = explode(':', $_COOKIE["PWGSID"]);

  // Don't allow invalid values...
  if (count($cookie) != 3)
    return ("");

  $id = (int)$cookie[0];
  if ($id <= 0)
    return ("");

  // Don't allow values older than 1 day
  $date = time() - 86400;
  if ((int)$cookie[1] < $date)
    return ("");

  // Lookup the username in the user table and compare...
  $result = db_query("SELECT * FROM user WHERE id=? AND status=2", array($id));
  if ($row = db_next($result))
  {
    // Compute the session ID...
    $sid = hash("sha256", "$_SERVER[REMOTE_ADDR]:$cookie[0]:$cookie[1]:"
			 ."$SITE_SECRET:$row[hash]:"
			 ."$_SERVER[HTTP_USER_AGENT]");

    // See if it matches the cookie value...
    if ($cookie[2] == $sid)
    {
      // Set globals...
      $LOGIN_EMAIL        = $row["email"];
      $LOGIN_ID           = $row["id"];
      $LOGIN_IS_ADMIN     = $row["is_admin"];
      $LOGIN_IS_EDITOR    = $row["is_editor"];
      $LOGIN_IS_MEMBER    = $row["is_member"];
      $LOGIN_NAME         = $row["name"];
      $LOGIN_ORGANIZATION = $row["organization_id"];
      $LOGIN_PAGEMAX      = $row["itemsperpage"];
      $LOGIN_TIMEZONE     = $row["timezone"];

      $result = db_query("SELECT id FROM workgroup WHERE chair_id=? OR vicechair_id=? OR secretary_id=?", array($LOGIN_ID, $LOGIN_ID, $LOGIN_ID));
      if (db_count($result) > 0)
        $LOGIN_IS_OFFICER = 1;
      else
        $LOGIN_IS_OFFICER = 0;

      // Return the current user...
      return ($cookie[0]);
    }
  }

  return ("");
}


//
// 'auth_hash()' - Hash a password.
//

function
auth_hash($password, $salt = "")
{
  // The password hash is crypt("password", "sha512-salt")
  if ($salt == "")
    $salt = "\$6\$" . bin2hex(openssl_random_pseudo_bytes(8));

  return (crypt($password, $salt));
}


//
// 'auth_login()' - Log a user into the system.
//

function				// O - Current username or ""
auth_login($email,			// I - Email
           $password,			// I - Password
           $remember = FALSE)		// I - Remember after browser quit?
{
  global $_SERVER, $LOGIN_EMAIL, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR,
         $LOGIN_IS_MEMBER, $LOGIN_IS_OFFICER,
         $LOGIN_NAME, $LOGIN_ORGANIZATION, $LOGIN_PAGEMAX, $LOGIN_TIMEZONE, $SITE_SECRET;


  // Reset the user...
  $LOGIN_EMAIL        = "";
  $LOGIN_ID           = 0;
  $LOGIN_IS_ADMIN     = 0;
  $LOGIN_IS_EDITOR    = 0;
  $LOGIN_IS_MEMBER    = 0;
  $LOGIN_IS_OFFICER   = 0;
  $LOGIN_NAME         = "";
  $LOGIN_ORGANIZATION = 0;
  $LOGIN_PAGEMAX      = 10;
  $LOGIN_TIMEZONE     = "UTC";

  // Lookup the username in the database...
  $result = db_query("SELECT * FROM user WHERE email LIKE ? AND status=2", array($email));
  if (db_count($result) == 1 && ($row = db_next($result)))
  {
    // Encrypt the password...
    $hash = auth_hash($password, $row["hash"]);

    // See if they match...
    if ($row["hash"] == $hash)
    {
      // Update the username and email...
      $LOGIN_EMAIL        = $row["email"];
      $LOGIN_ID           = $row["id"];
      $LOGIN_IS_ADMIN     = $row["is_admin"];
      $LOGIN_IS_EDITOR    = $row["is_editor"];
      $LOGIN_IS_MEMBER    = $row["is_member"];
      $LOGIN_NAME         = $row["name"];
      $LOGIN_ORGANIZATION = $row["organization_id"];
      $LOGIN_PAGEMAX      = $row["itemsperpage"];
      $LOGIN_TIMEZONE     = $row["timezone"];

      $result = db_query("SELECT id FROM workgroup WHERE chair_id=? OR vicechair_id=? OR secretary_id=?", array($LOGIN_ID, $LOGIN_ID, $LOGIN_ID));
      if (db_count($result) > 0)
        $LOGIN_IS_OFFICER = 1;
      else
        $LOGIN_IS_OFFICER = 0;

      // Compute the session ID...
      $date = time();
      $sid  = "$LOGIN_ID:$date:" .
              hash("sha256", "$_SERVER[REMOTE_ADDR]:$LOGIN_ID:$date:"
                            ."$SITE_SECRET:$hash:$_SERVER[HTTP_USER_AGENT]");

      // Save the SID and email address cookies...
      setcookie("PWGSID", $sid, 0, "/", $_SERVER["SERVER_NAME"], TRUE, TRUE);
    }
  }

  return ($LOGIN_EMAIL);
}


//
// 'auth_logout()' - Logout of the current user by clearing the session ID.
//

function
auth_logout()
{
  global $_SERVER, $LOGIN_EMAIL, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR,
         $LOGIN_IS_MEMBER, $LOGIN_IS_OFFICER,
         $LOGIN_NAME, $LOGIN_ORGANIZATION, $LOGIN_PAGEMAX, $LOGIN_TIMEZONE;


  // Reset the user...
  $LOGIN_EMAIL        = "";
  $LOGIN_ID           = 0;
  $LOGIN_IS_ADMIN     = 0;
  $LOGIN_IS_EDITOR    = 0;
  $LOGIN_IS_MEMBER    = 0;
  $LOGIN_IS_OFFICER   = 0;
  $LOGIN_NAME         = "";
  $LOGIN_ORGANIZATION = 0;
  $LOGIN_PAGEMAX      = 10;
  $LOGIN_TIMEZONE     = "UTC";

  setcookie("PWGSID", "", 0, "/", $_SERVER["SERVER_NAME"], TRUE, TRUE);
}
?>
