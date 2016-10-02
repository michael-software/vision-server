<?php

$command = $pluginManager->getCommand(0);

if(!empty($command)) {
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
		
		$pluginManager->databaseManager->setValue($array, array('id'=>$command));
		
		$pluginManager->redirect( $pluginManager );
	} else {
		$contact = $pluginManager->databaseManager->getValues(array('id'=>array('value'=>$command)), 1);
		
		$jUI->add( new JUI\Heading('Kontakt bearbeiten') );
		
		$firstname = new JUI\Input('firstname');
		$firstname->setLabel('Vorname: ');
		$firstname->setValue($contact);
		$jUI->add($firstname);
		
		$jUI->nline();
		
		$lastname = new JUI\Input('lastname');
		$lastname->setLabel('Nachname: ');
		$lastname->setValue($contact);
		$jUI->add($lastname);
		
		$jUI->nline();
		$jUI->nline();
		
		$telephone = new JUI\Input('telephone');
		$telephone->setLabel('Telefon: ');
		$telephone->setValue($contact);
		$jUI->add($telephone);
		
		$jUI->nline();
		
		$mobile = new JUI\Input('mobile');
		$mobile->setLabel('Mobil: ');
		$mobile->setValue($contact);
		$jUI->add($mobile);
		
		$jUI->nline();
		$jUI->nline();
		
		$email = new JUI\Input('email');
		$email->setLabel('E-Mail: ');
		$email->setValue($contact);
		$jUI->add($email);
		
		$jUI->nline();
		$jUI->nline();
		
		$address = new JUI\Input('address');
		$address->setLabel('Straße/Nr.: ');
		
		$city = new JUI\Input('city');
		$city->setLabel('Stadt: ');
		
		$zipcode = new JUI\Input('zipcode');
		$zipcode->setLabel('PLZ: ');
		
		$state = new JUI\Input('state');
		$state->setLabel('Bundesland: ');
		
		
		$country = new JUI\Input('country');
		$country->setLabel('Land: ');
		
		$addressArray = json_decode($contact['address']);
		
		if(!empty($addressArray)) {
			if(!empty($addressArray->address)) {
				$address->setValue($addressArray->address);
			}
			
			if(!empty($addressArray->city)) {
				$city->setValue($addressArray->city);
			}
			
			if(!empty($addressArray->zipcode)) {
				$zipcode->setValue($addressArray->zipcode);
			}
			
			if(!empty($addressArray->state)) {
				$state->setValue($addressArray->state);
			}
			
			if(!empty($addressArray->country)) {
				$country->setValue($addressArray->country);
			}
		}
		
		$jUI->add($address);
		$jUI->nline();
		$jUI->add($city);
		$jUI->add($zipcode);
		$jUI->nline();
		$jUI->add($state);
		$jUI->nline();
		$jUI->add($country);
		
		$jUI->nline();
		$jUI->nline();
		
		$birthday = new JUI\Input('birthday');
		$birthday->setLabel('Geburtstag: ');
		$birthday->setPreset(JUI\Input::DATE);
		$birthday->setValue($contact);
		$jUI->add($birthday);
		
		$jUI->nline();
		$jUI->nline();
		
		$jUI->add( new JUI\Button('Speichern', TRUE) );
	}
}

?>