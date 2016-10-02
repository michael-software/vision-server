<?php

class ConfigEditor {
	private $filepath;
	private $currentValues;
	private $defaultValues;
	
	function __construct($file) {
		$this->initDefaultValues();
		
		if(file_exists($file) && is_file($file)) {
			$this->filepath = $file;
		}
	}
	
	public function setValue($key, $value) {
		if(!empty($this->currentValues[$key])) {
			$this->currentValues[$key]['value'] = $value;
		}
	}
	
	public function save() {
		if(!empty($this->filepath)) {
			$handle = fopen ($this->filepath, 'w');
			
			fwrite ($handle, '<?php' . PHP_EOL . PHP_EOL);
			
			foreach($this->currentValues as $key=>$value) {
				$inhalt = '';
				
				if($value['type'] == "STRING") {
					if(!is_string($value['value'])) {
						$value['value'] = $this->defaultValues[$key]['value'];
					}
					
					$inhalt .= '$conf[\'' . $key . '\'] = "' . $value['value'] . '";' . PHP_EOL;
				} else if($value['type'] == "INTEGER") {
					if(!is_numeric($value['value'])) {
						$value['value'] = $this->defaultValues[$key]['value'];
					}
						
					$inhalt .= '$conf[\'' . $key . '\'] = ' . $value['value'] . ';' . PHP_EOL;
				} else if($value['type'] == "BOOLEAN") {
					if(strtolower($value['value']) == "true") {
						$inhalt .= '$conf[\'' . $key . '\'] = true;' . PHP_EOL;
					} else if(strtolower($value['value']) == "false") {
						$inhalt .= '$conf[\'' . $key . '\'] = false;' . PHP_EOL;
					} else {
						$inhalt .= '$conf[\'' . $key . '\'] = ' . strtolower($this->defaultValues[$key]['value']) . PHP_EOL;
					}
				} else if($value['type'] == "COLOR") {
					if(!is_string($value['value'])) {
						$value['value'] = $this->defaultValues[$key]['value'];
					}
					
					$inhalt .= '$conf[\'' . $key . '\'] = "' . $value['value'] . '";' . PHP_EOL;
				} else if($value['type'] == "ARRAY") {
					if(!is_array($value['value'])) {
						$value['value'] = $this->defaultValues[$key]['value'];
					}
					
					$inhalt .= '$conf[\'' . $key . '\'] = ' . $this->parseArray($value['value']) . ';' . PHP_EOL;
				}
				
				fwrite ($handle, $inhalt);
			}
			
			fwrite ($handle, PHP_EOL . '?>');
			
			
			fclose ($handle);
		}
	}
	
	public function loadConfig() {
		if(!empty($this->filepath)) {
			
			include $this->filepath;
			
			if(!empty($conf)) {
				$this->currentValues = $this->defaultValues;
				
				foreach($conf as $key=>$value) {
					$this->currentValues[$key]['value'] = $value;
				}
			}
			
			return $this->currentValues;
		}
	}
	
	public function getTmpConfig() {
		return $this->currentValues;
	}
	
	private function initDefaultValues() {
		/* Database Settings */
		$this->defaultValues['db_host']['type']        = "STRING";
		$this->defaultValues['db_host']['value']       = "localhost";
		$this->defaultValues['db_host']['description'] = "Datenbank Server";
		
		$this->defaultValues['db_username']['type']        = "STRING";
		$this->defaultValues['db_username']['value']       = "root";
		$this->defaultValues['db_username']['description'] = "Benutzername für Datenbankserver";
		
		$this->defaultValues['db_password']['type']        = "STRING";
		$this->defaultValues['db_password']['value']       = "";
		$this->defaultValues['db_password']['description'] = "Kennwort für Datenbankserver";
		
		$this->defaultValues['db_database']['type']        = "STRING";
		$this->defaultValues['db_database']['value']       = "vision";
		$this->defaultValues['db_database']['description'] = "Datenbank auf Datenbankserver";
		
		
		/* Server Settings */
		$this->defaultValues['root_enabled']['type']        = "BOOLEAN";
		$this->defaultValues['root_enabled']['value']       = TRUE;
		$this->defaultValues['root_enabled']['description'] = "Aktiviert/Deaktiviert den Root-Benutzer";
		
		$this->defaultValues['root_password']['type']        = "STRING";
		$this->defaultValues['root_password']['value']       = uniqid();
		$this->defaultValues['root_password']['description'] = "Kennwort für den Root-Benutzer";
		
		$this->defaultValues['dav_realm']['type']        = "STRING";
		$this->defaultValues['dav_realm']['value']       = "SabreDAV";
		$this->defaultValues['dav_realm']['description'] = "SabreDAV Realm";
		
		$this->defaultValues['baseuri']['type']        = "STRING";
		$this->defaultValues['baseuri']['value']       = "/";
		$this->defaultValues['baseuri']['description'] = "-";
		
		$this->defaultValues['serverurl']['type']        = "STRING";
		$this->defaultValues['serverurl']['value']       = "";
		$this->defaultValues['serverurl']['description'] = "Die Haupturl des Servers.";
		
		
		$this->defaultValues['fgcolor']['type']        = "COLOR";
		$this->defaultValues['fgcolor']['value']       = "#FF0000";
		$this->defaultValues['fgcolor']['description'] = "Standard Vordergrundfarbe";
		
		$this->defaultValues['bgcolor']['type']        = "COLOR";
		$this->defaultValues['bgcolor']['value']       = "#F5F5F5";
		$this->defaultValues['bgcolor']['description'] = "Standard Hintergrundfarbe";
		
		
		$this->defaultValues['ws_enabled']['type']        = "BOOLEAN";
		$this->defaultValues['ws_enabled']['value']       = FALSE;
		$this->defaultValues['ws_enabled']['description'] = "Aktiviert/Deaktiviert WebSocket-Verbindung (nach Einrichtung)";
		
		$this->defaultValues['ws_port']['type']        = "INTEGER";
		$this->defaultValues['ws_port']['value']       = 9000;
		$this->defaultValues['ws_port']['description'] = "Port für den WebSocket-Server";
		
		$this->defaultValues['ws_interval']['type']        = "INTEGER";
		$this->defaultValues['ws_interval']['value']       = 15;
		$this->defaultValues['ws_interval']['description'] = "Intervall, in dem der WebSocket-Server nach neuen Nachrichten suchen soll (in Sekunden)";
		
		$this->defaultValues['server_secure_hash']['type']        = "STRING";
		$this->defaultValues['server_secure_hash']['value']       = md5(uniqid());
		$this->defaultValues['server_secure_hash']['description'] = "Sicherheitsschlüssel für Verbindung zu WebSocket-Server";
		
		$this->defaultValues['server_interval']['type']        = "INTEGER";
		$this->defaultValues['server_interval']['value']       = 60;
		$this->defaultValues['server_interval']['description'] = "Intervall, in dem der WebSocket-Server Befehle ausführen soll (in Sekunden)";
		
		
		$this->defaultValues['login_sync_server']['type']        = "ARRAY";
		$this->defaultValues['login_sync_server']['value']       = array();
		$this->defaultValues['login_sync_server']['description'] = "Synchronisationsserver für Login. Überträgt die Login-Daten von diesem Server auf einen anderen.";
		
		$this->defaultValues['remote_secure_key']['type']        = "STRING";
		$this->defaultValues['remote_secure_key']['value']       = md5(uniqid());
		$this->defaultValues['remote_secure_key']['description'] = "Sicherheitsschlüssel für den externen Zugriff.";
	}

	private function parseArray($array) {
		if(is_array($array)) {
			$return = "array(";
			
			foreach($array as $key=>$value) {
				$return .= '"' . $key . '"=>';
				
				if(is_string($value)) {
					$return .= '"' . $value . '"';
				} else if(is_array($value)) {
					$return .= $this->parseArray($value);
				} else {
					$return .= "";
				}
			}
			
			$return .= ")";
			
			return $return;
		} else if (is_string($array)) {
			return $array;
		}
		
		return "";
	}
}

?>