<?php

function createVCard($array) {
	$return = 'BEGIN:VCARD' . PHP_EOL;
	$return .= 'VERSION:2.1' . PHP_EOL;
	
	$return .= getName($array) . PHP_EOL;
	$return .= getFormatedName($array) . PHP_EOL;
	$return .= getPhoto($array);
	$return .= getTelephonePrivate($array);
	$return .= getMobilePrivate($array);
	//$return .= 'TEL;WORK;VOICE:(0221) 9999123' . PHP_EOL;
	//$return .= 'TEL;HOME;VOICE:(0221) 1234567' . PHP_EOL;
	$return .= getAddress($array);
	$return .= getEmail($array);
	$return .= date('Y-m-d H:i:s') . PHP_EOL;
	
	$return .= 'END:VCARD' . PHP_EOL;
	
	return $return;
}

function getName($array) {
	$return = 'N:';
	
	if(!empty($array['lastname'])) {
		$return .= $array['lastname'];
	}
	
	$return .= ';';
	
	if(!empty($array['firstname'])) {
		$return .= $array['firstname'];
	}
	
	$return .= ';';
	
	if(!empty($array['middlename'])) {
		$return .= $array['middlename'];
	}
	
	$return .= ';';
	
	if(!empty($array['praefixname'])) {
		$return .= $array['praefixname'];
	}
	
	$return .= ';';
	
	if(!empty($array['postfixname'])) {
		$return .= $array['postfixname'];
	}
	
	return $return;
}

function getFormatedName($array) {
	$return = 'FN:';
	
	if(!empty($array['praefixname'])) {
		$return .= $array['praefixname'] . ' ';
	}
	
	if(!empty($array['firstname'])) {
		$return .= $array['firstname'] . ' ';
	}

	if(!empty($array['middlename'])) {
		$return .= $array['middlename'] . ' ';
	}

	if(!empty($array['lastname'])) {
		$return .= $array['lastname'] . ' ';
	}
	
	if(!empty($array['postfixname'])) {
		$return .= $array['postfixname'] . ' ';
	}
	
	return trim($return);
}

function getPhoto($array) {
	if(!empty($array['photo'])) {
		return 'PHOTO;' . $array['photo'] .PHP_EOL;
	}
}

function getTelephonePrivate($array) {
	if(!empty($array['telephone'])) {
		return 'TEL;HOME;VOICE:' . $array['telephone'] . PHP_EOL;
	}
		
	if(!empty($array['telephone_home'])) {
		return 'TEL;HOME;VOICE:' . $array['telephone_home'] . PHP_EOL;
	}
}

function getMobilePrivate($array) {
	if(!empty($array['mobile'])) {
		return 'TEL;TYPE=cell:' . $array['mobile'] . PHP_EOL;
	}
}

function getAddress($array) {
	if(!empty($array['address'])) {
		$address = json_decode($array['address']);
		
		if(!empty($address)) {
			$return = 'ADR;HOME:;;';
			
			if(!empty($address->street)) {
				$return .= $address->street;
			}
			
			$return .= ';';
			
			if(!empty($address->city)) {
				$return .= $address->city;
			}
			
			$return .= ';';
			
			if(!empty($address->state)) {
				$return .= $address->state;
			}
			
			$return .= ';';
			
			if(!empty($address->zipcode)) {
				$return .= $address->zipcode;
			}
			
			$return .= ';';
			
			if(!empty($address->country)) {
				$return .= $address->country;
			}
			
			return $return . PHP_EOL;
		}
	}
}

function getEmail($array) {
	if(!empty($array['email'])) {
		return 'EMAIL;PREF;INTERNET:' . $array['email'] . PHP_EOL;
	}
}

?>