<?php

class WOL {
	static function send($broadcast, $mac) {
		$mac_array = split(':', $mac);
		$hwaddr = '';
		
		foreach($mac_array AS $octet) {
			$hwaddr .= chr(hexdec($octet));
		}
		
		// Create Magic Packet
		$packet = '';
		
		for ($i = 1; $i <= 6; $i++) {
			$packet .= chr(255);
		}
		
		for ($i = 1; $i <= 16; $i++) {
			$packet .= $hwaddr;
		}
		
		$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if ($sock) {
			$options = socket_set_option($sock, 1, 6, true);
			
			if ($options >=0) {
				echo "sended";
				$e = socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, 7);
				socket_close($sock);
			}
		}
	}
}

?>