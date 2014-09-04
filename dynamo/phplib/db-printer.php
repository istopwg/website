<?php
//
// Class for the printer table.
//

include_once "site.php";
include_once "db.php";


$PRINTER_COLUMNS = array(
  "submission_id" => PDO::PARAM_INT,
  "organization_id" => PDO::PARAM_INT,
  "product_family" => PDO::PARAM_STR,
  "model" => PDO::PARAM_STR,
  "url" => PDO::PARAM_STR,
  "cert_version" => PDO::PARAM_STR,
  "color_supported" => PDO::PARAM_BOOL,
  "duplex_supported" => PDO::PARAM_BOOL,
  "finishings_supported" => PDO::PARAM_BOOL,
  "create_date" => PDO::PARAM_STR,
  "create_id" => PDO::PARAM_INT,
  "modify_date" => PDO::PARAM_STR,
  "modify_id" => PDO::PARAM_INT
);


class printer
{
  //
  // Instance variables...
  //

  var $id;
  var $submission_id;
  var $organization_id;
  var $product_family;
  var $model;
  var $url;
  var $cert_version;
  var $color_supported;
  var $duplex_supported;
  var $finishings_supported;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'printer::printer()' - Create a printer object.
  //

  function				// O - New Article object
  printer($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'printer::clear()' - Initialize a new a printer object.
  //

  function
  clear()
  {
    global $LOGIN_ID;

    $this->id                   = 0;
    $this->submission_id        = 0;
    $this->organization_id      = 0;
    $this->product_family       = "";
    $this->model                = "";
    $this->url                  = "";
    $this->cert_version         = "";
    $this->color_supported      = 0;
    $this->duplex_supported     = 0;
    $this->finishings_supported = 0;
    $this->create_date          = "";
    $this->create_id            = $LOGIN_ID;
    $this->modify_date          = "";
    $this->modify_id            = $LOGIN_ID;
  }


  //
  // 'printer::delete()' - Delete a printer object.
  //

  function
  delete()
  {
    db_delete("printer", $this->id);
    $this->clear();
  }


  //
  // 'printer::load()' - Load a printer object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    global $PRINTER_COLUMNS;

    $this->clear();

    if (!db_load($this, "printer", $id, $PRINTER_COLUMNS))
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }


  //
  // 'printer::save()' - Save a printer object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PRINTER_COLUMNS;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
      return (db_save($this, "printer", $this->id, $PRINTER_COLUMNS));

    $this->create_date = $this->modify_date;
    $this->create_id   = $this->modify_id;

    if (($id = db_create($this, "printer", $PRINTER_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

    return (TRUE);
  }
}


//
// 'printer_search()' - Get a list of Article IDs.
//

function				// O - Array of Article IDs
printer_search($search = "",		// I - Search string
               $color = -1,		// I - Color
               $duplex = -1,		// I - Duplex
               $finishings = -1,	// I - Finishings
	       $order = "")		// I - Order fields
{
  global $PRINTER_COLUMNS;

  if ($color >= 0 || $duplex >= 0 || $finishings >= 0)
  {
    $keyvals = array();
    if ($color >= 0)
      $keyvals["color_supported"] = $color;
    if ($duplex >= 0)
      $keyvals["duplex_supported"] = $duplex;
    if ($finishings >= 0)
      $keyvals["finishings_supported"] = $finishings;
  }
  else
    $keyvals = null;

  return (db_search("printer", $PRINTER_COLUMNS, $keyvals, $search, $order));
}
?>
