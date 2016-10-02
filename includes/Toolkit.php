<?php

class Toolkit {

	/* THANKS TO:
	 * http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
	 */
	
	
	static function hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);
		
		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		
		return array($r, $g, $b);
	}
	
	static function rgb2hex($rgb) {
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
		
		return $hex; // returns the hex value including the number sign (#)
	}
	
	static function rgbDarker($rgb, $rate) {
		$rgb[0] = $rgb[0] - round( $rgb[0] * $rate );
		$rgb[1] = $rgb[1] - round( $rgb[1] * $rate );
		$rgb[2] = $rgb[2] - round( $rgb[2] * $rate );
		
		if($rgb[0] < 0) $rgb[0] = 0;
		if($rgb[1] < 0) $rgb[1] = 0;
		if($rgb[2] < 0) $rgb[2] = 0;
		
		return $rgb;
	}
	
	static function hexDarker($hex, $rate) {
		$rgb = Toolkit::hex2rgb($hex);
		return Toolkit::rgb2hex( Toolkit::rgbDarker($rgb, $rate) );
	}
	
	static function rgbComplementary($rgb) {
		$rgb[0] = ($rgb[0] + 127) % 256;
		$rgb[1] = ($rgb[1] + 127) % 256;
		$rgb[2] = ($rgb[2] + 127) % 256;
		
		return $rgb;
	}
	
	static function hexComplementary($hex) {
		$rgb = Toolkit::hex2rgb($hex);
		return Toolkit::rgb2hex( Toolkit::rgbComplementary($rgb) );
	}
	
	static function rgbContrastColor($rgb) {
        $y = (299 * $rgb[0] + 587 * $rgb[1] + 114 * $rgb[2]) / 1000;
        return $y >= 128 ? array(0, 0, 0) : array(255, 255, 255);
    }
	
	static function hexContrastColor($hex) {
        $rgb = Toolkit::hex2rgb($hex);
		return Toolkit::rgb2hex( Toolkit::rgbContrastColor($rgb) );
    }
}

?>