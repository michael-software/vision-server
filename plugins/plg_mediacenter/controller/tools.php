<?php

function encode($string) {
	return str_replace ('/', '-s-', $string);
}

function decode($string) {
	return str_replace ('-s-', '/', $string);
}

?>