<?php
//
// PHP functions for validating common input.
//
// This file should be included using "include_once"...
//


//
// 'validate_email()' - Validate an email address.
//

function				// O - TRUE if OK, FALSE otherwise
validate_email($email)			// I - Email address
{
  // Check for both "name@example.com" and "Full Name <name@example.com>"
  return (preg_match("/^[a-zA-Z0-9_\\.+-]+@[a-zA-Z0-9\\.-]+\\.[a-zA-Z]{2,}\$/",
                $email) ||
          preg_match("/^[^<]*<[a-zA-Z0-9_\\.+-]+@[a-zA-Z0-9\\.-]+\\."
                    ."[a-zA-Z]{2,}>\$/", $email));
}


//
// 'validate_path()' - Validate a URL or relative path.
//

$PATH_REQUIREMENTS = "Paths must be valid relative or absolute http or https "
                    ."URLs.";

function				// O - TRUE if OK, FALSE otherwise
validate_path($path)			// I - Path string
{
  if (preg_match("/^(http:|https:)\\/\\/[-_.%@:;a-zA-Z0-9]+\\//", $path))
  {
    if ($fp = fopen($path, "r"))
    {
      fclose($fp);
      return (TRUE);
    }

    return (FALSE);
  }

  if (strpos($path, "../") !== FALSE)
    return (FALSE);

  return (file_exists($path));
}


//
// 'validate_password()' - Validate a password.
//

$PASSWORD_REQUIREMENTS = "Passwords must be at least eight characters in "
                        ."length and contain at least one uppercase letter, "
                        ."lowercase letter, and number.";

function				// O - TRUE if OK, FALSE otherwise
validate_password($password)		// I - Password string
{
  return (preg_match("/((?=.*\d)(?=.*[a-z])(?=.*[A-Z])).{8,}/", $password));
}


//
// 'validate_url()' - Validate a URL.
//

$URL_REQUIREMENTS = "URLs must be valid absolute http:, https:, or ftp: URIs "
                   ."as defined in RFC 3986.";

function				// O - TRUE if OK, FALSE otherwise
validate_url($url)			// I - URL string
{
  return (preg_match("/^(http:|https:|ftp:)\\/\\/[-_.%@:;a-zA-Z0-9]+(\\/.*|)\$/", $url));
}
?>
