<?php
//
// PHP functions for standardized HTML output...
//
// This file should be included using "include_once"...
//

// Detect iPhone/mobile clients
if (array_key_exists("HTTP_USER_AGENT", $_SERVER))
{
  if (preg_match("/(iPhone;|iPhone Simulator;)/", $_SERVER["HTTP_USER_AGENT"]))
  {
    $html_input_width    = 20;
    $html_is_phone       = TRUE;
    $html_is_tablet      = FALSE;
    $html_search_width   = 30;
    $html_textarea_width = 40;
  }
  else
  {
    $html_input_width    = 72;
    $html_is_phone       = FALSE;
    $html_search_width   = 70;
    $html_textarea_width = 80;

    if (preg_match("/(iPad;|iPad Simulator;)/", $_SERVER["HTTP_USER_AGENT"]))
      $html_is_tablet = TRUE;
    else
      $html_is_tablet = FALSE;

  }
}
else
{
  $html_input_width    = 72;
  $html_is_phone       = FALSE;
  $html_is_tablet      = FALSE;
  $html_search_width   = 70;
  $html_show_all       = TRUE;
  $html_textarea_width = 80;
}

$html_is_mobile = $html_is_phone || $html_is_tablet;


//
// 'html_abbreviate()' - Abbreviate long strings.
//

function				// O - Abbreviated string
html_abbreviate($text,			// I - String
                $maxlen = 32)		// I - Maximum length of string
{
  $newtext   = "";
  $textlen   = strlen($text);
  $inelement = 0;

  for ($i = 0, $len = 0; $i < $textlen && $len < $maxlen; $i ++)
    switch ($text[$i])
    {
      case '<' :
          $newtext .= "&lt;";
          $len ++;
	  break;

      case '>' :
          $newtext .= "&lt;";
          $len ++;
	  break;

      case '&' :
          $newtext .= "&amp;";
          $len ++;
	  break;

      default :
	  $newtext .= $text[$i];
	  $len ++;
	  break;
    }

  if ($i < $textlen)
    return ($newtext . "&hellip;");
  else
    return ($newtext);
}


//
// 'html_date()' - Return a HTML-ready friendly date string.
//
// This function returns either "Mon Day, Year", "HH:MM", "Yesterday", or
// "N days ago".
//

function				// O - Date string
html_date($datetime = "")		// I - DATETIME value
{
  $seconds   = db_seconds($datetime);
  $secstoday = mktime(0, 0, 0);
  $days      = (int)ceil(($secstoday - $seconds) / 86400);

  if ($seconds >= $secstoday && $days == 0)
    return ("Today @ " . date("H:i", $seconds));
  else if ($days == 1)
    return ("Yesterday");
  else if ($days < 11 && $days > 0)
    return ("$days Days Ago");
  else
    return (date("M j, Y", $seconds));
}


//
// 'html_end_table()' - End a rounded, shaded table.
//

function
html_end_table()
{
  print("</tbody></table>\n");
}


//
// 'html_format()' - Convert plain text to HTML.
//
// Formatting rules:
//
// ! Header
// !! Sub-header
// - Unordered list
// * Unordered list
// 1. Numbered list
// " Blockquote
// SPACE preformatted
// [[link||text label]] or [[link]]
//

function				// O - Quoted string
html_format($text,			// I - Original string
            $abstract = FALSE,		// I - Generate an abstract
            $baseheading = 1)		// I - Base heading level?
{
  global $html_path;


  $text  = str_replace(array("\r\n", "\r"), array("\n", ""), $text);
  $block = "";
  $col   = 0;
  $html  = "";
  $len   = strlen($text);
  $list  = "";
  $table = FALSE;
  $sq = $dq = 0;

  for ($i = 0; $i < $len; $i ++)
  {
    switch ($text[$i])
    {
      case '<' :
          $col ++;
	  $html .= "&lt;";
	  break;

      case '>' :
          $col ++;
          $html .= "&gt;";
	  break;

      case '&' :
          $col ++;
	  if (preg_match("/^&([a-z]+|#[0-9]+|#x[0-9a-f]+);/i", substr($text, $i, 32), $matches))
	  {
	    $html .= "&$matches[1];";
	    $i += strlen($matches[1]) + 1;
	  }
	  else
            $html .= "&amp;";
	  break;

      case " " : /* SPACE preformatted */
          if ($col == 0)
          {
            if ($block != "pre")
            {
              if ($block != "")
                $html .= "</$block>\n";

              if ($list != "")
              {
                $html .= "</$list>\n";
                $list = "";
              }

	      if ($abstract && $i > 100)
		return ($html . "&hellip;\n");

              $block = "pre";
              $html .= "<pre>";
            }
          }
          else
	    $html .= " ";

          $col ++;
          break;

      case "\"" : /* " blockquote */
          if ($col == 0 && ($i + 1) < $len && $text[$i + 1] == " ")
          {
	    if ($block != "")
	      $html .= "</$block>\n";

	    if ($list != "")
	    {
	      $html .= "</$list>\n";
	      $list = "";
	    }

	    if ($abstract && $i > 100)
	      return ($html . "&hellip;\n");

            $block = "blockquote";
            $html .= "<blockquote>";
            $i ++;
          }
          else if ($block == "pre")
            $html .= "&quot;";
          else
          {
            $dq = 1 - $dq;
            if ($dq)
              $html .= "&ldquo;";
            else
              $html .= "&rdquo;";
          }

          $col ++;
          break;

      case "'" : /* 'single quotes' */
          if ($block == "pre")
            $html .= "'";
          else
          {
            $sq = 1 - $sq;
            if ($sq)
              $html .= "&lsquo;";
            else
              $html .= "&rsquo;";
          }

          $col ++;
          break;

      case "-" : /* - unordered list */
      case "*" : /* * unordered list */
          if ($col == 0 && ($i + 1) < $len && $text[$i + 1] == " ")
          {
	    if ($block != "")
	      $html .= "</$block>\n";

	    if ($abstract && $i > 100)
	    {
	      if ($list != "")
	        $html .= "</$list>\n";

	      return ($html . "&hellip;\n");
	    }

	    if ($list != "ul")
	    {
	      if ($list != "")
		$html .= "</$list>\n";
	      $list = "ul";
	      $html .= "<ul>\n";
	    }

            $block = "li";
            $html .= "<li>";
            $i ++;
          }
          else
          {
            $html .= $text[$i];
          }

          $col ++;
          break;

      case "1" : /* - ordered list */
      case "2" :
      case "3" :
      case "4" :
      case "5" :
      case "6" :
      case "7" :
      case "8" :
      case "9" :
          if ($col == 0 && preg_match("/^([1-9][0-9]*\\. )/", substr($text, $i, 32), $matches))
          {
	    if ($block != "")
	      $html .= "</$block>\n";

	    if ($abstract && $i > 100)
	    {
	      if ($list != "")
	        $html .= "</$list>\n";

	      return ($html . "&hellip;\n");
	    }

	    if ($list != "ol")
	    {
	      if ($list != "")
		$html .= "</$list>\n";
	      $list = "ol";
	      $html .= "<ol>\n";
	    }

            $block = "li";
            $html .= "<li>";
            $i += strlen($matches[1]) - 1;
          }
          else
          {
            $html .= $text[$i];
          }

          $col ++;
          break;

      case "!" : /* ! heading, !! heading */
          if ($col == 0 && preg_match("/^(!+ )/", substr($text, $i, 32), $matches))
          {
	    if ($block != "")
	      $html .= "</$block>\n";

	    if ($list != "")
	    {
	      $html .= "</$list>\n";
	      $list = "";
	    }

	    if ($abstract && $i > 100)
	      return ($html . "&hellip;\n");

            $block = sprintf("h%d", $baseheading + strlen($matches[1]) - 2);
            $html .= "<$block>";
            $i += strlen($matches[1]) - 1;
          }
          else
          {
            $html .= "!";
          }

          $col ++;
          break;

      case "\n" :
          if ($block == "pre" && ($i + 1) < $len && $text[$i + 1] != " ")
          {
            $html .= "</pre>\n";
            $block = "";
          }
          else if (($i + 1) < $len && $text[$i + 1] == "\n")
          {
            $html .= "</$block>\n";
            $block = "p";
            $html .= "<p>";
          }
          else
            $html .= "\n";

          $col = 0;

	  if ($abstract && $i > 100)
	    return ($html . "</$block>\n&hellip;\n");
	  break;

      case "\t" :
          if ($col == 0)
	    $html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	  else
            $html .= " ";
	  break;

      case '[' : // [[link|text]]
          if (preg_match("/^\\[\\[([^|]+)\\|([^\\]]+)\\]\\]/", substr($text, $i, 1024), $matches))
          {
            if (!validate_url($matches[1]))
              $link = $html_path . htmlspecialchars($matches[1], ENT_QUOTES);
            else
              $link = htmlspecialchars($matches[1], ENT_QUOTES);
            $ltext = htmlspecialchars($matches[2], ENT_QUOTES);
            if (preg_match("/^(http:|https:|ftp:)/", $matches[1]) &&
                strpos($matches[1], "//pwg\\.org/") === FALSE)
              $target = " target=\"_blank\"";
            else
              $target = "";

            $html  .= "<a href=\"$link\"$target>$ltext</a>";
            $i     += strlen($matches[1]) + strlen($matches[2]) + 4;
          }
          else if (preg_match("/^\\[\\[([^\\]]+)\\]\\]/", substr($text, $i, 1024), $matches))
          {
            if (!validate_url($matches[1]))
              $link = $html_path . htmlspecialchars($matches[1], ENT_QUOTES);
            else
              $link = htmlspecialchars($matches[1], ENT_QUOTES);
            $ltext = str_replace(array("/", "&amp;"),
				 array("/<wbr>", "&amp;<wbr>"),
				 htmlspecialchars($matches[1], ENT_QUOTES));
            if (preg_match("/^(http:|https:|ftp:)/", $matches[1]) &&
                strpos($matches[1], "//pwg\\.org/") === FALSE)
              $target = " target=\"_blank\"";
            else
              $target = "";

            $html  .= "<a href=\"$link\"$target>$ltext</a>";
            $i     += strlen($matches[1]) + 3;
          }
          else
            $html .= "[";

	  $col ++;
          break;

      case 'I' :
          if ($col == 0)
          {
	    if ($block != "")
	      $html .= "</$block>\n";

	    if ($list != "")
	    {
	      $html .= "</$list>\n";
	      $list = "";
	    }

            $block = "p";
            $html .= "<p>";
          }

	  if (preg_match("/^Issue #([0-9]+)/",
			 substr($text, $i, 10), $matches))
	  {
	    $html .= "<a href=\"$html_path/dynamo/issues.php?U$matches[1]\">Issue #$matches[1]</a>";
            $i   += 4 + strlen($matches[1]);
	    $col += 5 + strlen($matches[1]);
	    break;
	  }
	  else
	  {
	    $html .= "I";
	    $col ++;
	  }
	  break;

      case 'f' :
      case 'h' :
          if ($col == 0)
          {
	    if ($block != "")
	      $html .= "</$block>\n";

	    if ($list != "")
	    {
	      $html .= "</$list>\n";
	      $list = "";
	    }

            $block = "p";
            $html .= "<p>";
          }

          if (preg_match("/^(ftp:\\/\\/[^ \n\t]+|http:\\/\\/[^ \n\t]+|https:\\/\\/[^ \n\t]+)/", substr($text, $i, 1024), $matches))
          {
            $link  = htmlspecialchars($matches[1], ENT_QUOTES);
            $ltext = str_replace(array("/", "&amp;"),
				 array("/<wbr>", "&amp;<wbr>"),
				 htmlspecialchars($matches[1], ENT_QUOTES));
            if (preg_match("/^(http:|https:|ftp:)/", $matches[1]) &&
                strpos($matches[1], "//msweet\\.org/") === FALSE)
              $target = " target=\"_blank\"";
            else
              $target = "";

            $html  .= "<a href=\"$link\"$target>$ltext</a>";
            $i     += strlen($matches[1]) - 1;
            $col ++;
          }
          else
          {
            $html .= $text[$i];
            $col ++;
	  }
	  break;

      default :
          if ($col == 0)
          {
	    if ($block != "")
	      $html .= "</$block>\n";

	    if ($list != "")
	    {
	      $html .= "</$list>\n";
	      $list = "";
	    }

            $block = "p";
            $html .= "<p>";
          }

          $col ++;
          $html .= $text[$i];
	  break;
    }
  }

  if ($block != "")
    $html .= "</$block>\n";

  if ($list != "")
    $html .= "</$list>\n";

  return ($html);
}


//
// 'html_links()' - Show a list of links.
//

function
html_links($links)
{
  print("<div id=\"pagesublinks\">");
  foreach ($links as $title => $link)
  {
    $title = htmlspecialchars($title);
    print("<a href=\"$link\">$title</a>\n");
  }
  print("</div>\n");
}


//
// 'html_paginate()' - Show pagination controls.
//
// "current" is the currently shown item (0-based).
// "count" is the number of items.
// "perpage" is the number of items shown per page.
// "linkprefix" includes all link text up to the index.
// "linksuffix" includes all link text after the index.
//
// If count <= perpage, no controls are shown.
//

function
html_paginate($current, $count, $perpage, $linkprefix, $linksuffix)
{
  if ($count <= $perpage)
    return;

  $curpage   = (int)($current / $perpage);
  $previndex = ($curpage - 1) * $perpage;
  $nextindex = ($curpage + 1) * $perpage;
  $lastpage  = (int)($count / $perpage);
  $startpage = $curpage - 2;
  if ($startpage < 0)
    $startpage = 0;
  $endpage = $startpage + 4;
  if ($previndex < 0)
    $endpage ++;
  if ($endpage > $lastpage)
  {
    $endpage = $lastpage;
    if ($lastpage >= 4)
      $startpage = $endpage - 4;
  }
  if ($nextindex >= $count && $startpage > 0)
    $startpage --;

  print("<ul class=\"pagination\">");

  if ($previndex >= 0)
    print("<li><a href=\"$linkprefix$previndex$linksuffix\">&laquo;</a></li>");

  if ($startpage > 1)
    print("<li><a href=\"${linkprefix}0$linksuffix\">1 &hellip;</a></li>");
  else if ($startpage == 1)
    print("<li><a href=\"${linkprefix}0$linksuffix\">1</a></li>");

  for ($page = $startpage; $page <= $endpage; $page ++)
  {
    $index = $page * $perpage;
    $label = $index + 1;
    print("<li><a href=\"$linkprefix$index$linksuffix\">$label</a></li>");
  }

  if ($endpage < $lastpage)
  {
    $index = $lastpage * $perpage;
    $label = $index + 1;

    if ($endpage < ($lastpage - 1))
      print("<li><a href=\"$linkprefix$index$linksuffix\">&hellip; $label</a></li>");
    else
      print("<li><a href=\"$linkprefix$index$linksuffix\">$label</a></li>");
  }

  if ($nextindex < $count)
    print("<li><a href=\"$linkprefix$nextindex$linksuffix\">&raquo;</a></li>");

  print("</ul>\n");
}


//
// 'html_search_words()' - Generate an array of search words.
//

function				// O - Array of words
html_search_words($search = "")		// I - Search string
{
  $words = array();
  $temp  = "";
  $len   = strlen($search);

  for ($i = 0; $i < $len; $i ++)
  {
    switch ($search[$i])
    {
      case "\"" :
          if ($temp != "")
	  {
	    $words[sizeof($words)] = strtolower($temp);
	    $temp = "";
	  }

	  $i ++;

	  while ($i < $len && $search[$i] != "\"")
	  {
	    $temp .= $search[$i];
	    $i ++;
	  }

	  $words[sizeof($words)] = strtolower($temp);
	  $temp = "";
          break;

      case " " :
      case "\t" :
      case "\n" :
          if ($temp != "")
	  {
	    $words[sizeof($words)] = strtolower($temp);
	    $temp = "";
	  }
	  break;

      default :
          $temp .= $search[$i];
	  break;
    }
  }

  if ($temp != "")
    $words[sizeof($words)] = strtolower($temp);

  return ($words);
}


//
// 'html_select_is_published()' - Do a <select> for the "is published" field.
//

function
html_select_is_published($is_published = 1,
					// I - Default state
                         $help = "")	// I - Help text
{
  print("<select name='is_published'>");
  if ($is_published)
  {
    print("<option value='0'>Private</option>");
    print("<option value='1' selected>Public</option>");
  }
  else
  {
    print("<option value='0' selected>Private</option>");
    print("<option value='1'>Public</option>");
  }
  print("</select>");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_start_table()' - Start a table.
//
// Each heading string can have a leading + (mobile only) or "-" (desktop only)
// and a trailing ":N" to do "colspan='N'".
//

function
html_start_table($headings)		// I - Array of heading strings
{
  global $html_row;


  print("<table class=\"table table-condensed table-striped\" summary=\"\">\n"
       ."<thead><tr>");

  $html_row = 0;

  for ($i = 0; $i < count($headings); $i ++)
  {
    $class  = "";
    $header = $headings[$i];

    if ($header[0] == '-')
    {
      $class  = " class=\"hidden-phone\"";
      $header = substr($header, 1);
    }
    else if ($header[0] == '+')
    {
      $class  = " class=\"visible-phone\"";
      $header = substr($header, 1);
    }

    $len = strlen($header);

    if ($len >= 2 && $header[$len - 2] == ":")
    {
      $colspan = " colspan=\"" . $header[$len - 1] . "\"";
      $header  = substr($header, 0, $len - 2);
    }
    else
      $colspan = "";

    if ($header != "")
      print("<th nowrap$class$colspan>$header</th>");
    else
      print("<th$class$colspan>&nbsp;</th>");
  }

  print("</tr></thead><tbody>\n");
}


//
// 'html_text()' - Convert plain text to HTML.
//

function				// O - HTML string
html_text($text,			// I - Plain text string
	  $abstract = FALSE)		// I - Generate an abstract
{
  global $PHP_BASE;


  $col  = 0;
  $html = "";
  $len  = strlen($text);
  $word = 0;

  for ($i = 0; $i < $len; $i ++)
  {
    switch ($text[$i])
    {
      case '<' :
          $col ++;
	  $word ++;
          $html .= "&lt;";
	  break;

      case '>' :
          $col ++;
	  $word ++;
          $html .= "&gt;";
	  break;

      case '&' :
          $col ++;
	  $word ++;
          $html .= "&amp;";
	  break;

      case "\n" :
	  $html .= "<br>\n";
          $col  = 0;
	  $word = 0;

	  if ($abstract && $i > 100)
	    return ($html . "&hellip;\n");
	  break;

      case "\r" :
	  break;

      case "\t" :
          if ($col == 0)
	    $html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	  else
            $html .= " ";

	  $word = 0;
	  break;

      case " " :
	  $word = 0;
          if ($col == 0 || (($i + 1) < $len && $text[$i + 1] == " "))
	    $html .= "&nbsp;";
	  else
            $html .= " ";

          if ($col > 0)
	    $col ++;

	  if ($abstract && $i > 100)
	    return ($html . "&hellip;\n");
	  break;

      case '.' :
          if (($i + 1) < $len && $text[$i + 1] != " " &&
              ($text[$i + 1] < "0" || $text[$i + 1] > "9"))
	    $html .= $text[$i] . "<wbr>";
	  else
	    $html .= $text[$i];

          $word  = 0;
          break;

      case '-' :
      case '+' :
      case '=' :
      case '_' :
      case ';' :
      case ':' :
      case ',' :
      case '?' :
      case '!' :
      case '/' :
          if (($i + 1) < $len && $text[$i + 1] != " ")
	    $html .= $text[$i] . "<wbr>";
	  else
	    $html .= $text[$i];

          $word  = 0;
          break;

      case 'B' :
      case 'S' :
      case 'f' :
      case 'h' :
          if (substr($text, $i, 7) == "http://" ||
              substr($text, $i, 8) == "https://" ||
              substr($text, $i, 6) == "ftp://")
	  {
	    // Extract the URL and make this a link...
	    for ($j = $i; $j < $len; $j ++)
	      if (!preg_match("/[-+~a-z0-9%_\\/:@.?#=&]/i", $text[$j]))
	        break;

	    if ($text[$j - 1] == '.')
	      $j --;

            $count = $j - $i;
            $url   = substr($text, $i, $count);
	    $html .= "<a href='$url'>" .
	             preg_replace("/([-+=_;:.,?|\\/])/", "\\1<wbr>",
	                          htmlspecialchars($url, ENT_QUOTES)) .
	             "</a>";
	    $col   += $count;
	    $word  += $count;
	    $i     = $j - 1;
	    break;
	  }
	  else if (preg_match("/^(Bug|STR) #([0-9]+)/", substr($text, $i, 10),
	                      $matches))
	  {
	    $html .= "<a href='$PHP_BASE/bugs.php?L$matches[2]'>$matches[1] "
	             ."#$matches[2]</a>";
            $count = 5 + strlen($matches[2]);
	    $col   += $count;
	    $i     += $count - 1;
	    break;
	  }

      default :
          $col ++;
	  $word ++;
          $html .= $text[$i];

	  if ($word >= 80)
	  {
	    $html .= "<wbr>";
	    $word = 0;

	    if ($abstract && $i > 100)
	      return ($html . "&hellip;\n");
	  }
	  break;
    }
  }

  return ($html);
}


//
// 'html_show_error()' - Show an error alert.
//

function
html_show_error($message,		// I - Error message
		$dismiss = FALSE)	// I - Dismissable?
{
  print("<div class=\"alert alert-danger\">");
  if ($dismiss)
    print("<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>\n");
  print("<span class=\"glyphicon glyphicon-warning-sign\"></span> $message</div>\n");
}


//
// 'html_show_info()' - Show an informational alert.
//

function
html_show_info($message,		// I - Error message
               $dismiss = FALSE)	// I - Dismissable?
{
  print("<div class=\"alert alert-info\">");
  if ($dismiss)
    print("<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>\n");
  print("<span class=\"glyphicon glyphicon-info-sign\"></span> $message</div>\n");
}


//
// 'html_title()' - Show the page title.
//

function html_title($title, $subtitle = "")
{
  $title = htmlspecialchars($title);
  if ($subtitle != "")
    $subtitle = " <small>" . htmlspecialchars($subtitle) . "</small>";

  print("<div class=\"page-header\"><h1>$title$subtitle</h1></div>\n");
}


//
// 'html_form_button()' - Show a form button.
//
// If the label string starts with a "+" then it is shown as a primary button.
//
// If the label string starts with a "-" then it is shown as a mini button.
//

function
html_form_button($name, $label)
{
  $name = htmlspecialchars($name, ENT_QUOTES);

  if ($label[0] == "+")
  {
    $bclass = "btn btn-primary";
    $label  = htmlspecialchars(substr($label, 1));
  }
  else if ($label[0] == "-")
  {
    if ($label[1] == "-")
    {
      $bclass = "btn btn-default btn-xs";
      $label  = htmlspecialchars(substr($label, 2));
    }
    else
    {
      $bclass = "btn btn-default btn-sm";
      $label  = htmlspecialchars(substr($label, 1));
    }
  }
  else
  {
    $bclass = "btn btn-default";
    $label  = htmlspecialchars($label);
  }

  print("<button type=\"submit\" class=\"$bclass\" name=\"$name\">$label</button>\n");
}


//
// 'html_form_buttons()' - Show form buttons.
//
// The buttons array is associative, e.g.:
//
//   array("NAME" => "+Label", "NAME2" => "Label 2")
//
// If the label string starts with a "+" then it is shown as a primary button.
//
// If the label string starts with a "-" then it is shown as a mini button.
//

function
html_form_buttons($buttons)		// I - Array of buttons
{
  global $html_inline_form;


  if (!$html_inline_form)
    print("<div class=\"form-group\">");

  foreach ($buttons as $name => $label)
    html_form_button($name, $label);

  if (!$html_inline_form)
    print("</div>\n");
}


//
// 'html_form_csrf()' - Compute a validation hash for form submissions.
//

function
html_form_csrf($provided = "")
{
  global $LOGIN_ID, $SITE_SECRET, $_SERVER;


  if ($provided == "")
    $number = time();
  else
    $number = (int)$provided;	// Validated in html_form_validate()

  $hash = hash("sha256",
               "$LOGIN_ID:$number:$_SERVER[REMOTE_ADDR]:$SITE_SECRET");

  return ("$number:$hash");
}


//
// 'html_form_end()' - End a web form.
//
// The buttons array is associative, e.g.:
//
//   array("NAME" => "+Label", "NAME2" => "Label 2")
//
// If the label string starts with a "+" then it is shown as a primary button.
//
// If the label string starts with a "-" then it is shown as a mini button.
//

function
html_form_end($buttons = FALSE)		// I - Array of buttons
{
  global $html_inline_form;

  if ($buttons !== FALSE)
  {
    if ($html_inline_form)
    {
      html_form_buttons($buttons);
    }
    else
    {
      print("<div class=\"form-group\"><div class=\"col-sm-10 col-sm-offset-2\">");
      html_form_buttons($buttons);
      print("</div></div>\n");
    }
  }

  print("</form></div>\n");
}


//
// 'html_form_field_end()' - End a form field.
//

function
html_form_field_end()
{
  global $REQUEST_METHOD, $html_field_valid;


  if (!$html_field_valid && $REQUEST_METHOD != "GET")
    print("<span class=\"glyphicon glyphicon-warning-sign form-control-feedback\"></span>");

  print("</div></div>\n");
}


//
// 'html_form_field_start()' - Start a form field.
//

function
html_form_field_start($name,		// I - Field (form) name
                      $label,		// I - Label string
                      $valid = TRUE,	// I - Is the current value valid?
                      $desktop = FALSE)	// I - Only desktop?
{
  global $REQUEST_METHOD, $html_field_valid;


  $html_field_valid = $valid;
  $hclass = "form-group";
  if (!$valid && $REQUEST_METHOD != "GET")
    $hclass .= " has-error has-feedback";
  if ($desktop)
    $hclass .= " visible-desktop";

  $name  = htmlspecialchars($name, ENT_QUOTES);
  $label = htmlspecialchars($label);

  print("<div class=\"$hclass\">"
       ."<label for=\"$name\" class=\"control-label col-sm-2\">$label</label>"
       ."<div class=\"col-sm-10\">");
}


//
// 'html_form_start()' - Start a web form.
//

function
html_form_start($action,		// I - URL for submission
                $inline = FALSE,	// I - Inline form?
                $attachments = FALSE,	// I - Allow attachments?
                $center = FALSE)	// I - Center form?
{
  global $html_inline_form;


  $html_inline_form = $inline;
  $action = htmlspecialchars($action, ENT_QUOTES);

  if ($inline)
  {
    $hclass = "form-inline";

    if ($center)
      print("<div style=\"display: block-inline; text-align: center;\">\n");
    else
      print("<div style=\"display: block-inline;\">\n");
  }
  else
  {
    $hclass = "form-horizontal";
    print("<div class=\"container\">\n");
  }
  if ($attachments)
    print("<form action=\"$action\" method=\"POST\" class=\"$hclass\" "
         ."enctype=\"multipart/form-data\" role=\"form\">"
	 ."<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"33554432\">");
  else
    print("<form action=\"$action\" method=\"POST\" class=\"$hclass\" role=\"form\">");

  $csrf = html_form_csrf();
  print("<input type=\"hidden\" name=\"validation\" value=\"$csrf\">");
}


//
// 'html_form_checkbox()' - Show a checkbox field.
//
// Use "+name" to make the field required.
//

function
html_form_checkbox($name,	// I - Field name
                   $label = "",	// I - Label text
		   $value = 0,	// I - Value (0 = off, 1 = on)
		   $help = "")	// I - Help, if any
{
  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  $name  = htmlspecialchars($name, ENT_QUOTES);
  $label = htmlspecialchars($label, ENT_QUOTES);

  if ($value == 0)
    $value = "";
  else
    $value = " checked";

  print("<div class=\"checkbox\">");
  if ($label != "")
    print("<label><input class=\"form-control\" type=\"checkbox\" name=\"$name\"$value$required> $label</label>");
  else
    print("<input class=\"form-control\" type=\"checkbox\" name=\"$name\"$value$required>");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
  print("</div>");
}


//
// 'html_form_email()' - Show an email field.
//
// Use "+name" to make the field required.
//

function
html_form_email($name,			// I - Field name
                $placeholder,		// I - Placeholder text
                $value = "",		// I - Current value
                $help = "",		// I - Help, if any
                $autofill = TRUE)	// I - Allow auto-fill?
{
  global $html_input_width;


  if ($html_input_width > 40)
    $email_width = 40;
  else
    $email_width = $html_input_width;

  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  if ($autofill)
    $readonly = " autocomplete=\"on\"";
  else
    $readonly = " autocomplete=\"off\" readonly onfocus=\"this.removeAttribute('readonly');\"";

  $name        = htmlspecialchars($name, ENT_QUOTES);
  $placeholder = htmlspecialchars($placeholder, ENT_QUOTES);
  $value       = htmlspecialchars($value, ENT_QUOTES);

  print("<input class=\"form-control\" type=\"email\" name=\"$name\" size=\"$email_width\" "
       ."placeholder=\"$placeholder\" maxlength=\"255\"$required "
       ."value=\"$value\"$readonly>");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_form_file()' - Show a file upload field.
//
// Use "+name" to make the field required.
//

function
html_form_file($name,			// I - Field name
               $placeholder = "",	// I - Placeholder text
	       $help = "")		// I - Help, if any
{
  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  $name        = htmlspecialchars($name, ENT_QUOTES);
  $placeholder = htmlspecialchars($placeholder, ENT_QUOTES);

  print("<input class=\"form-control\" type=\"file\" name=\"$name\" placeholder=\"$placeholder\""
       ."$required>");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_form_hidden()' - Add a hidden field to a form.
//

function
html_form_hidden($name,			// I - Field name
                 $value)		// I - Field value
{
  $name = htmlspecialchars($name, ENT_QUOTES);
  $value = htmlspecialchars($value, ENT_QUOTES);

  print("<input type=\"hidden\" name=\"$name\" value=\"$value\">");
}


//
// 'html_form_number()' - Show a number field.
//
// Use "+name" to make the field required.
//

function
html_form_number($name,		// I - Field name
                 $placeholder,	// I - Placeholder text
                 $value = 0,	// I - Value (0 = empty)
		 $help = "",	// I - Help, if any
		 $digits = 5)	// I - Number of digits to show
{
  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  $name        = htmlspecialchars($name, ENT_QUOTES);
  $placeholder = htmlspecialchars($placeholder, ENT_QUOTES);

  if ($value == 0)
    $value = "";

  $max = (int)pow(10, $digits) - 1;

  print("<input class=\"form-control\" type=\"number\" name=\"$name\" size=\"$digits\" "
       ."placeholder=\"$placeholder\" min=\"0\" max=\"$max\" "
       ."value=\"$value\"$required>");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_form_password()' - Show a password field.
//
// Use "+name" to make the field required.
//

function
html_form_password($name,		// I - Field name
                   $placeholder = "",	// I - Placeholder text
		   $help = "",		// I - Help, if any
		   $autofill = TRUE)	// I - Allow autocomplete?
{
  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  if ($autofill)
    $readonly = " type=\"password\" autocomplete=\"on\"";
  else
    $readonly = " autocomplete=\"off\" onfocus=\"this.setAttribute('type', 'password');\"";

  $name        = htmlspecialchars($name, ENT_QUOTES);
  $placeholder = htmlspecialchars($placeholder, ENT_QUOTES);

  print("<input class=\"form-control\" name=\"$name\" size=\"20\" placeholder=\"$placeholder\" maxlength=\"255\"$required$readonly>");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_form_search()' - Show a search field.
//
// Use "+name" to make the field required.
//

function
html_form_search($name,		// I - Field name
                 $placeholder,	// I - Placeholder text
                 $value = "",	// I - Value (0 = empty)
		 $help = "")	// I - Help, if any
{
  global $html_search_width;


  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  $name        = htmlspecialchars($name, ENT_QUOTES);
  $placeholder = htmlspecialchars($placeholder, ENT_QUOTES);
  $value       = htmlspecialchars($value, ENT_QUOTES);

  print("<input class=\"form-control\" type=\"search\" name=\"$name\" size=\"$html_search_width\" "
       ."placeholder=\"$placeholder\" autosave=\"org.msweet.search\" "
       ."results=\"20\" value=\"$value\"$required>");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_form_select()' - Show a drop-down selection field.
//
// Use "+name" to make the field required.  The items array is, by default,
// associative, but you can specify the array type with the last argument:
//
//     "assoc" = Associative array
//     "0"     = Indexed array starting at 0
//     "1"     = Indexed array starting at 1
//     "value" = Use the array value as the selection value
//

function
html_form_select($name,			// I - Field name
                 $items,		// I - Array of items
                 $placeholder = "",	// I - Placeholder item
                 $value = "",		// I - Value (0 = empty)
		 $help = "",		// I - Help, if any
		 $type = "assoc")	// I - Type of array?
{
  global $LOGIN_LEVEL, $html_inline_form, $html_is_phone;


  if ($html_inline_form || $html_is_phone)
    $maxlabel = 24;
  else
    $maxlabel = 72;

  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  $name        = htmlspecialchars($name, ENT_QUOTES);
  $placeholder = htmlspecialchars($placeholder);

  print("<select name=\"$name\"$required>");
  if ($placeholder != "")
  {
    if ($type == "0")
      $key = "-1";
    else if ($type == "1")
      $key = "0";
    else
      $key = "";

    print("<option value=\"$key\">$placeholder</option>");
  }

  $index = (int)$type;
  foreach ($items as $key => $label)
  {
    // Force label to be a string...
    $label = "$label";

    if ($label[0] == "_")
    {
      if ($LOGIN_LEVEL < USER_LEVEL_DEVEL)
        continue;

      $label = substr($label, 1);
    }

    if ($type == "value")
      $key = $label;
    else if ($type != "assoc")
      $key = $index;

    $hkey  = htmlspecialchars($key, ENT_QUOTES);
    $label = html_abbreviate($label, $maxlabel);
    if ("$key" == "$value")
      print("<option value=\"$hkey\" selected>$label</option>");
    else
      print("<option value=\"$hkey\">$label</option>");

    $index ++;
  }
  print("</select>");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_form_text()' - Show a text field.
//
// Use "+name" to make the field required.
//

function
html_form_text($name,			// I - Field name
               $placeholder,		// I - Placeholder text
               $value = "",		// I - Current value
               $help = "",		// I - Help, if any
               $rows = 1,		// I - Number of rows (>1 is textarea)
               $addon = "",		// I - Add-on text
               $width = 999)		// I - Maximum width
{
  global $html_input_width, $html_textarea_width;


  if ($rows > 1)
  {
    if ($width > $html_textarea_width)
      $width = $html_textarea_width;
  }
  else if ($width > $html_input_width)
    $width = $html_input_width;

  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  $name        = htmlspecialchars($name, ENT_QUOTES);
  $placeholder = htmlspecialchars($placeholder, ENT_QUOTES);
  $value       = htmlspecialchars($value, ENT_QUOTES);

  if ($addon != "")
  {
    print("<div class=\"input-group\">");
    if ($addon[0] != '+')
      print("<span class=\"input-group-addon\">$addon</span>");
  }

  if ($rows <= 1)
    print("<input class=\"form-control\" type=\"text\" name=\"$name\" size=\"$width\" "
         ."placeholder=\"$placeholder\" maxlength=\"255\"$required "
         ."value=\"$value\">");
  else
    print("<textarea class=\"form-control\" name=\"$name\" cols=\"$width\" rows=\"$rows\" "
         ."wrap=\"virtual\" placeholder=\"$placeholder\""
         ."$required>$value</textarea>");

  if ($addon != "")
  {
    if ($addon[0] == '+')
    {
      $addon = substr($addon, 1);
      print("<span class=\"input-group-addon\">$addon</span></div>");
    }
    else
      print("</div>");
  }

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_form_url()' - Show a URL field.
//
// Use "+name" to make the field required.
//

function
html_form_url($name,			// I - Field name
	      $placeholder,		// I - Placeholder text
	      $value = "",		// I - Current value
	      $help = "")		// I - Help, if any
{
  global $html_input_width;


  if ($html_input_width > 40)
    $url_width = 40;
  else
    $url_width = $html_input_width;

  if ($name[0] == "+")
  {
    $required = " required";
    $name     = substr($name, 1);
  }
  else
    $required = "";

  $name        = htmlspecialchars($name, ENT_QUOTES);
  $placeholder = htmlspecialchars($placeholder, ENT_QUOTES);
  $value       = htmlspecialchars($value, ENT_QUOTES);

  print("<input class=\"form-control\" type=\"url\" name=\"$name\" size=\"$url_width\" "
       ."placeholder=\"$placeholder\" maxlength=\"255\"$required "
       ."value=\"$value\">");

  if ($help != "")
  {
    $help = str_replace("\n", "<br>\n", htmlspecialchars($help));
    print("<span class=\"help-block\">$help</span>");
  }
}


//
// 'html_form_validate()' - Validate the CSRF hash in a form submissions.
//

function				// O - TRUE if OK, FALSE otherwise
html_form_validate()
{
  global $LOGIN_ID, $_SERVER, $_POST, $REQUEST_METHOD;


  if ($REQUEST_METHOD != "POST")
    return (FALSE);			// Not a POST

  if (array_key_exists("validation", $_POST))
    $validation = trim($_POST["validation"]);
  else
    $validation = "";

  if (!preg_match("/^[0-9]+:[0-9a-z]{64,64}\$/i", $validation))
    return (FALSE);			// Wrong format

  $number = (int)$validation;
  $diff   = time() - $number;
  if ($diff > 86400 || $diff < -60)
    return (FALSE);			// No more than 1 day old or 1 minute in the future

  return (html_form_csrf($validation) == $validation);
}
?>
