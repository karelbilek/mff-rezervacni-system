<?php

function action_welcome() {
	list($f,$s) = HTML_welcome();
	$next = "counts_on_homepage";
	return array($f, $next, 0, "", $s);
}

?>
