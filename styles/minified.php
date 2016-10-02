<?php
	require_once dirname(dirname(__FILE__)) . '/config.php';
	require_once dirname(dirname(__FILE__)) . '/includes/Toolkit.php';
	
	header("Content-Type: text/css");
	header("X-Content-Type-Options: nosniff");

	$cssFiles = array(
      "general.css",
      "desktop.css",
      "mobile.css",
    );

    $buffer = "";
    foreach ($cssFiles as $cssFile) {
      $buffer .= file_get_contents($cssFile);
    }
    $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
    $buffer = str_replace(': ', ':', $buffer);
    $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
	
	/* REPLACE COLORS */
	if(!empty($_GET['fgcolor'])) {
		$fgcolor = $_GET['fgcolor'];
	} else {
		$fgcolor = $conf['fgcolor'];
	}
	$buffer = str_replace('#FF0000', $fgcolor, $buffer);
	
	if(!empty($_GET['bgcolor'])) {
		$bgcolor = $_GET['bgcolor'];
	} else {
		$bgcolor = $conf['bgcolor'];
	}
	$buffer = str_replace('#FBFBFB', $bgcolor, $buffer);
	$buffer = str_replace('#888888', Toolkit::hexDarker($bgcolor, 0.3), $buffer);
	$buffer = str_replace('__color_menu_links__', Toolkit::hexContrastColor($bgcolor), $buffer);
	/*   /REPLACE COLORS   */
	
    echo($buffer);
?>