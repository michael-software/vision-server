<?php

if(!empty($_POST['firstname']) && !empty($_POST['lastname'])) {
	$array['firstname']['value'] = $_POST['firstname'];
	$array['lastname']['value']  = $_POST['lastname'];
	
	if(!empty($_POST['telephone'])) {
		$array['telephone']['value'] = $_POST['telephone'];
	}
	
	if(!empty($_POST['mobile'])) {
		$array['mobile']['value'] = $_POST['mobile'];
	}
	
	if(!empty($_POST['email'])) {
		$array['email']['value'] = $_POST['email'];
	}
	
	if(!empty($_POST['address'])) {
		$address['address'] = $_POST['address'];
		
		if(!empty($_POST['city'])) {
			$address['city'] = $_POST['city'];
		}
		
		if(!empty($_POST['zipcode'])) {
			$address['zipcode'] = $_POST['zipcode'];
		}
		
		if(!empty($_POST['state'])) {
			$address['state'] = $_POST['state'];
		}
		
		if(!empty($_POST['country'])) {
			$address['country'] = $_POST['country'];
		}
		
		$array['address']['value'] = json_encode($address);
	}
	
	if(!empty($_POST['birthday'])) {
		$array['birthday']['value'] = $_POST['birthday'];
	}
	
	$pluginManager->databaseManager->insertValue($array);
	
	$pluginManager->redirect( $pluginManager );
} else {
	$jUI->add( new JUI\Heading('Kontakt hinzufügen') );
	
	$firstname = new JUI\Input('firstname');
	$firstname->setLabel('Vorname: ');
	$jUI->add($firstname);
	
	$jUI->nline();
	
	$lastname = new JUI\Input('lastname');
	$lastname->setLabel('Nachname: ');
	$jUI->add($lastname);
	
	$jUI->nline();
	$jUI->nline();
	
	$telephone = new JUI\Input('telephone');
	$telephone->setLabel('Telefon: ');
	$jUI->add($telephone);
	
	$jUI->nline();
	
	$mobile = new JUI\Input('mobile');
	$mobile->setLabel('Mobil: ');
	$jUI->add($mobile);
	
	$jUI->nline();
	$jUI->nline();
	
	$email = new JUI\Input('email');
	$email->setLabel('E-Mail: ');
	$jUI->add($email);
	
	$jUI->nline();
	$jUI->nline();
	
	$address = new JUI\Input('address');
	$address->setLabel('Straße/Nr.: ');
	$jUI->add($address);
	
	$jUI->nline();
	
	$city = new JUI\Input('city');
	$city->setLabel('Stadt: ');
	$jUI->add($city);
	
	$zipcode = new JUI\Input('zipcode');
	$zipcode->setLabel('PLZ: ');
	$jUI->add($zipcode);
	
	$jUI->nline();
	
	$state = new JUI\Input('state');
	$state->setLabel('Bundesland: ');
	$jUI->add($state);
	
	$jUI->nline();
	
	$country = new JUI\Input('country');
	$country->setLabel('Land: ');
	$jUI->add($country);
	
	$jUI->nline();
	$jUI->nline();
	
	$birthday = new JUI\Input('birthday');
	$birthday->setLabel('Geburtstag: ');
	$birthday->setPreset(JUI\Input::DATE);
	$jUI->add($birthday);
	
	$jUI->nline();
	$jUI->nline();
	
	$jUI->add( new JUI\Button('Erstellen', TRUE) );
}
?>