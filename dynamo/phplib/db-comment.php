<?php
//
// Class for the comment table.
//

include_once "site.php";

$COMMENT_COLUMNS = array(
  "ref_id" => PDO::PARAM_STR,
  "contents" => PDO::PARAM_STR,
  "create_date" => PDO::PARAM_STR,
  "create_id" => PDO::PARAM_INT,
  "modify_date" => PDO::PARAM_STR,
  "modify_id" => PDO::PARAM_INT
);

class comment
{
  //
  // Instance variables...
  //

  var $id;
  var $ref_id;
  var $contents;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'comment::comment()' - Create a comment object.
  //

  function				// O - New comment object
  comment($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'comment::clear()' - Initialize a new a comment object.
  //

  function
  clear()
  {
    global $LOGIN_ID;

    $this->id          = 0;
    $this->ref_id      = "";
    $this->contents    = "";
    $this->create_date = "";
    $this->create_id   = $LOGIN_ID;
    $this->modify_date = "";
    $this->modify_id   = $LOGIN_ID;
  }


  //
  // 'comment::delete()' - Delete a comment object.
  //

  function
  delete()
  {
    db_delete("comment", $this->id);
    $this->clear();
  }


  //
  // 'comment::load()' - Load a comment object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    global $COMMENT_COLUMNS;

    $this->clear();

    if (!db_load($this, "comment", $id, $COMMENT_COLUMNS))
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }


  //
  // 'comment::save()' - Save a comment object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $COMMENT_COLUMNS;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
      return (db_save($this, "comment", $this->id, $COMMENT_COLUMNS));

    $this->create_date = $this->modify_date;
    $this->create_id   = $this->modify_id;

    if (($id = db_create($this, "comment", $COMMENT_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }
}


//
// 'comment_search()' - Return an array of comment IDs for the given reference.
//

function				// O - Array of comment objects
comment_search($ref_id,			// I - Reference ID
               $search = "",		// I - Search text
               $order = "id")		// I - Ordering
{
  global $COMMENT_COLUMNS;

  return (db_search("comment", $COMMENT_COLUMNS, array("ref_id" => $ref_id), $search, $order));
}
?>
