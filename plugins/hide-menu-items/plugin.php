<?php

$hideMenuP = new Plugin('Hide Menu Items', 'CanyonCMS Team', 'Allows you to hide certain items from displaying on the menu.');

$hideMenuP->filter('content_setcontent', function ($x) {
	return sprintf("<i>%s</i>", $x);
});
?>
