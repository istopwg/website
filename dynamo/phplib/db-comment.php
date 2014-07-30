<?php
//
// Class for the comment table.
//

include_once "site.php";


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
    db_query("DELETE FROM comment WHERE id=$this->id");
    $this->clear();
  }


  //
  // 'comment::load()' - Load a comment object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    $this->clear();

    $result = db_query("SELECT * FROM comment WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);
    $this->id          = $row["id"];
    $this->ref_id      = $row["ref_id"];
    $this->contents    = $row["contents"];
    $this->create_date = $row["create_date"];
    $this->create_id   = $row["create_id"];
    $this->modify_date = $row["modify_date"];
    $this->modify_id   = $row["modify_id"];

    db_free($result);

    return (TRUE);
  }


  //
  // 'comment::save()' - Save a comment object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
    {
      return (db_query("UPDATE comment "
                      ." SET ref_id = '" . db_escape($this->ref_id) . "'"
                      .", contents = '" . db_escape($this->contents) . "'"
                      .", modify_date = '" . db_escape($this->modify_date) . "'"
                      .", modify_id = $this->modify_id"
                      ." WHERE id = $this->id") !== FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if (db_query("INSERT INTO comment VALUES"
                  ."(NULL"
                  .", '" . db_escape($this->ref_id) . "'"
                  .", '" . db_escape($this->contents) . "'"
                  .", '" . db_escape($this->create_date) . "'"
                  .", $this->create_id"
                  .", '" . db_escape($this->modify_date) . "'"
                  .", $this->modify_id"
                  .")") === FALSE)
        return (FALSE);

      $this->id = db_insert_id();
    }

    return (TRUE);
  }
}


//
// 'comment_search()' - Return an array of comment IDs for the given reference.
//

function				// O - Array of comment objects
comment_search($ref_id,			// I - Reference ID
               $search = "")		// I - Search text
{
  $comments = array();
  $dref_id  = db_escape($ref_id);
  $results  = db_query("SELECT id FROM comment WHERE ref_id='$dref_id' ORDER BY id");
  while ($row = db_next($results))
    $comments[sizeof($comments)] = $row["id"];
  db_free($results);

  return ($comments);
}
?>
