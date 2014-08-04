/* Google custom search code */
google.load('search', '1', {language : 'en'});
google.setOnLoadCallback(function() {
  var customSearchControl = new google.search.CustomSearchControl('018021367961685880654:mdt584m83r4');
  customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
  var options = new google.search.DrawOptions();
  options.setSearchFormRoot('PWGSearchForm');
  options.setAutoComplete(true);
  customSearchControl.draw('PWGSearchResults', options);
}, true);

/* Collapsible panels from http://www.richnetapps.com/javascript-animated-collapsible-panels-without-frameworks/ */
var PANEL_NORMAL_CLASS    = "panel";
var PANEL_COLLAPSED_CLASS = "panelcollapsed";
var PANEL_HEADING_TAG     = "h2";
var PANEL_CONTENT_CLASS   = "panelcontent";
var PANEL_COOKIE_NAME     = "";
var PANEL_ANIMATION_DELAY = 20; /*ms*/
var PANEL_ANIMATION_STEPS = 10;

function setUpPanels()
{
  loadSettings();

  // get all headings
  var headingTags = document.getElementsByTagName(PANEL_HEADING_TAG);

  // go through all tags
  for (var i=0; i<headingTags.length; i++)
  {
    var el = headingTags[i];

    // make sure it's the heading inside a panel
    if (el.parentNode.className != PANEL_NORMAL_CLASS && el.parentNode.className != PANEL_COLLAPSED_CLASS)
      continue;

    // get the text value of the tag
    var name = el.firstChild.nodeValue;

    // look for the name in loaded settings, apply the normal/collapsed class
    if (panelsStatus[name] == "false")
      el.parentNode.className = PANEL_COLLAPSED_CLASS;
    else if (panelsStatus[name] == "true")
      el.parentNode.className = PANEL_NORMAL_CLASS;
    else
    {
      // if no saved setting, see the initial setting
      panelsStatus[name] = (el.parentNode.className == PANEL_NORMAL_CLASS) ? "true" : "false";
    }

    // add the click behavor to headings
    el.onclick = function()
    {
      var target    = this.parentNode;
      var name      = this.firstChild.nodeValue;
      var collapsed = (target.className == PANEL_COLLAPSED_CLASS);
      saveSettings(name, collapsed?"true":"false");
      animateTogglePanel(target, collapsed);
    };
  }
}

/**
 * Start the expand/collapse animation of the panel
 * @param panel reference to the panel div
 */
function animateTogglePanel(panel, expanding)
{
  // find the .panelcontent div
  var elements = panel.getElementsByTagName("div");
  var panelContent = null;
  for (var i=0; i<elements.length; i++)
  {
    if (elements[i].className == PANEL_CONTENT_CLASS)
    {
	    panelContent = elements[i];
	    break;
    }
  }

  // make sure the content is visible before getting its height
  panelContent.style.display = "block";

  // get the height of the content
  var contentHeight = panelContent.offsetHeight;

  // if panel is collapsed and expanding, we must start with 0 height
  if (expanding)
    panelContent.style.height = "0px";

  var stepHeight = contentHeight / PANEL_ANIMATION_STEPS;
  var direction = (!expanding ? -1 : 1);

  setTimeout(function(){animateStep(panelContent,1,stepHeight,direction)}, PANEL_ANIMATION_DELAY);
}

/**
 * Change the height of the target
 * @param panelContent	reference to the panel content to change height
 * @param iteration		current iteration; animation will be stopped when iteration reaches PANEL_ANIMATION_STEPS
 * @param stepHeight	height increment to be added/substracted in one step
 * @param direction		1 for expanding, -1 for collapsing
 */
function animateStep(panelContent, iteration, stepHeight, direction)
{
  if (iteration<PANEL_ANIMATION_STEPS)
  {
    panelContent.style.height = Math.round(((direction>0) ? iteration : 10 - iteration) * stepHeight) +"px";
    iteration++;
    setTimeout(function(){animateStep(panelContent,iteration,stepHeight,direction)}, PANEL_ANIMATION_DELAY);
  }
  else
  {
    // set class for the panel
    panelContent.parentNode.className = (direction<0) ? PANEL_COLLAPSED_CLASS : PANEL_NORMAL_CLASS;
    // clear inline styles
    panelContent.style.display = panelContent.style.height = "";
  }
}

// -----------------------------------------------------------------------------------------------
// Load-Save
// -----------------------------------------------------------------------------------------------
/**
 * Reads the "panels" cookie if exists, expects data formatted as key:value|key:value... puts in panelsStatus object
 */
function loadSettings()
{
  // prepare the object that will keep the panel statuses
  panelsStatus = {};

  if (PANEL_COOKIE_NAME == "")
    return;

  // find the cookie name
  var start = document.cookie.indexOf(PANEL_COOKIE_NAME + "=");
  if (start == -1) return;

  // starting point of the value
  start += PANEL_COOKIE_NAME.length+1;

  // find end point of the value
  var end = document.cookie.indexOf(";", start);
  if (end == -1) end = document.cookie.length;

  // get the value, split into key:value pairs
  var cookieValue = unescape(document.cookie.substring(start, end));
  var panelsData = cookieValue.split("|");

  // split each key:value pair and put in object
  for (var i=0; i< panelsData.length; i++)
  {
    var pair = panelsData[i].split(":");
    panelsStatus[pair[0]] = pair[1];
  }
}

function expandAll()
{
  for (var key in panelsStatus)
    saveSettings(key, "true");

  setUpPanels();
}

function collapseAll()
{
  for (var key in panelsStatus)
    saveSettings(key, "false");

  setUpPanels();
}

/**
 * Takes data from the panelsStatus object, formats as key:value|key:value... and puts in cookie valid for 365 days
 * @param key	key name to save
 * @paeam value	key value
 */
function saveSettings(key, value)
{
  if (PANEL_COOKIE_NAME == "")
    return

  // put the new value in the object
  panelsStatus[key] = value;

  // create an array that will keep the key:value pairs
  var panelsData = [];
  for (var key in panelsStatus)
    panelsData.push(key+":"+panelsStatus[key]);

  // set the cookie expiration date 1 year from now
  var today = new Date();
  var expirationDate = new Date(today.getTime() + 365 * 1000 * 60 * 60 * 24);
  // write the cookie
  document.cookie = PANEL_COOKIE_NAME + "=" + escape(panelsData.join("|")) + ";expires=" + expirationDate.toGMTString();
}

/* Sidebar loading code - we do it here to avoid changing it on every page */
function load_sidebar(path) {
  setUpPanels();
  contents =
'<p>Printer Working Group:</p>\
<ul>\
  <li><a href="' + path + 'index.html">Home</a></li>\
  <li><a href="' + path + 'about.html">About Us</a></li>\
  <li><a href="http://www.google.com/calendar/embed?src=istopwg%40gmail.com">Calendar</a></li>\
  <li><a href="' + path + 'chair/index.html">Officers</a></li>\
  <li><a href="' + path + 'pwg-logos/members.html">Members</a><span class="PWGNoMobile">/<a href="' + path + 'pwg-logos/members.html#JOINING">Joining</a></span></li>\
  <li><a href="' + path + 'chair/meeting-info/meetings.html">Meetings</a></li>\
  <li><a href="' + path + 'chair/participating.html">Participating</a></li>\
</ul>\
<p>Publications:</p>\
<ul>\
  <li><a href="' + path + 'informational.html">Informational</a></li>\
  <li><a href="' + path + 'namespaces.html">Namespaces</a></li>\
  <li><a href="' + path + 'standards.html">Standards</a></li>\
</ul>\
<p>Technologies:</p>\
<ul>\
  <li><a href="' + path + 'ipp/everywhere.html">IPP Everywhere</a></li>\
</ul>\
<p>Workgroups:</p>\
<ul>\
  <li><a href="' + path + 'cloud/index.html"><span class="PWGNoMobile">Cloud Imaging Model</span><span class="PWGMobile">Cloud</span></a></li>\
  <li><a href="' + path + 'ids/index.html"><span class="PWGNoMobile">Imaging Device Security</span><span class="PWGMobile">IDS</span></a></li>\
  <li><a href="' + path + 'ipp/index.html"><span class="PWGNoMobile">Internet Printing Protocol</span><span class="PWGMobile">IPP</span></a></li>\
  <li><a href="' + path + 'sm/index.html"><span class="PWGNoMobile">Semantic Model</span><span class="PWGMobile">SM</span></a></li>\
</ul>';

  if (document.anchors.length > 0)
  {
    contents = contents + '\n<p>This Page:</p>\n<ul>\n';
    for (i = 0; i < document.anchors.length; i ++)
      contents = contents + '  <li><a href="#' + document.anchors[i].name +
                 '">' + document.anchors[i].innerHTML + '</a></li>\n';
    contents = contents + '</ul>';
  }

  document.getElementById('PWGSideBody').innerHTML = contents;
}
