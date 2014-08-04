/* "Jumbotron" support code for home page */
$('carousel').carousel();

/* Code to support swiping the jumbotron... */
var startX = 0, startY = 0;
var pressed = false, moved = 0;
var obj = document.getElementById('pwg-rolling');

/* Mouse (drag) navigation */
obj.ondragstart = function() {
  $('.carousel').interval = false;
  $('.carousel').carousel('pause');
  return false;
}

obj.addEventListener('mousedown', function(event) {
  event.preventDefault();

  startX  = event.pageX;
  startY  = event.pageY;
  pressed = true;
  moved   = 0;
}, false);

obj.addEventListener('mousemove', function(event) {
  if (!pressed)
    return;

  endX = event.pageX;
  endY = event.pageY;

  if (endX > startX)
    absX = endX - startX;
  else
    absX = startX - endX;

  if (endY > startY)
    absY = endY - startY;
  else
    absY = startY - endY;

  if (absY > absX || absX <= 5 || absY > 5)
    return;

  event.preventDefault();

  if (endX > startX) {
    /* Swipe left */
    startX = endX;

    if (moved < 0)
      return;

    $('.carousel').interval = false;
    $('.carousel').carousel('pause');
    $('.carousel').carousel('prev');
    moved = -1;
  } else if (endX < startX) {
    /* Swipe right */
    startX = endX;

    if (moved > 0)
      return;

    $('.carousel').interval = false;
    $('.carousel').carousel('pause');
    $('.carousel').carousel('next');
    moved = 1;
  }
}, false);

obj.addEventListener('mouseup', function(event) {
  pressed = false;
}, false);

/* Touch (swipe) navigation */
obj.addEventListener('touchstart', function(event) {
  if (event.targetTouches.length > 1)
    return;

  startX = event.targetTouches[0].pageX;
  startY = event.targetTouches[0].pageY;
  moved  = 0;
}, false);

obj.addEventListener('touchmove', function(event) {
  if (event.targetTouches.length > 1)
    return;

  endX = event.targetTouches[0].pageX;
  endY = event.targetTouches[0].pageY;

  if (endX > startX)
    absX = endX - startX;
  else
    absX = startX - endX;

  if (endY > startY)
    absY = endY - startY;
  else
    absY = startY - endY;

  if (absY > absX || absX <= 5 || absY > 5)
    return;

  event.preventDefault();

  if (endX > startX) {
    /* Swipe left */
    startX = endX;

    if (moved < 0)
      return;

    $('.carousel').interval = false;
    $('.carousel').carousel('pause');
    $('.carousel').carousel('prev');
    moved = -1;
  } else if (endX < startX) {
    /* Swipe right */
    startX = endX;

    if (moved > 0)
      return;

    $('.carousel').interval = false;
    $('.carousel').carousel('pause');
    $('.carousel').carousel('next');
    moved = 1;
  }
}, false);
