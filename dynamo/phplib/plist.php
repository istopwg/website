<?php
//
// Apple plist parser adapted from:
//
//   http://blog.iconara.net/2007/05/08/php-plist-parsing/comment-page-1/
//

//
// 'plist_read_file()' - Read a plist file into an associative array.
//

function				// O - Associative array or FALSE on error
plist_read_file($path)			// I - File to read
{
  if ($path == "")
    return (FALSE);

  $document = new DOMDocument();
  if (!$document->load($path))
    return (FALSE);

  $plistNode = $document->documentElement;
  $root      = $plistNode->firstChild;

  // skip any text nodes before the first value node
  while ($root->nodeName == "#text")
    $root = $root->nextSibling;

  return (_plist_parseValue($root));
}


// Private functions that implement the parser...
function
_plist_parseValue($valueNode)
{
  $valueType       = $valueNode->nodeName;
  $transformerName = "_plist_parse_$valueType";

  if (is_callable($transformerName))
  {
    // there is a transformer function for this node type
    return (call_user_func($transformerName, $valueNode));
  }

  // if no transformer was found
  return (null);
}

function
_plist_parse_integer($integerNode)
{
  return ((int)($integerNode->textContent));
}

function
_plist_parse_string($stringNode)
{
  return ($stringNode->textContent);
}

function
_plist_parse_date($dateNode)
{
  return ($dateNode->textContent);
}

function
_plist_parse_true($trueNode)
{
  return (TRUE);
}

function
_plist_parse_false($trueNode)
{
  return (FALSE);
}

function
_plist_parse_dict($dictNode)
{
  $dict = array();

  // for each child of this node
  for ($node = $dictNode->firstChild; $node != null; $node = $node->nextSibling)
  {
    if ($node->nodeName == "key")
    {
      $key       = $node->textContent;
      $valueNode = $node->nextSibling;

      // skip text nodes
      while ($valueNode->nodeType == XML_TEXT_NODE)
        $valueNode = $valueNode->nextSibling;

      // recursively parse the children
      $value = _plist_parseValue($valueNode);

      $dict[$key] = $value;
    }
  }

  return ($dict);
}

function
_plist_parse_array($arrayNode)
{
  $array = array();

  for ($node = $arrayNode->firstChild; $node != null; $node = $node->nextSibling)
    if ($node->nodeType == XML_ELEMENT_NODE)
      array_push($array, _plist_parseValue($node));

  return ($array);
}
?>
