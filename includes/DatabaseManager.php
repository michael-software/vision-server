<?php
require_once dirname(dirname(__FILE__)) . '/config.php';

	class DatabaseManager
	{
		private $db;
		
		private $praefix = null;
		
		private $table = null;
		private $tables = null;
		private $plugin = "";
		
		public static $table1  = '[{"type":"int","name":"id"},{"type":"varchar","name":"username"},{"type":"varchar","name":"digesta1"},{"type":"timestamp","name":"timestamp","default":"current_timestamp"}]'; // Login
		public static $table2  = '[{"type":"int","name":"id"},{"type":"varchar","name":"name"},{"type":"varchar","name":"authtoken"},{"type":"int","name":"user"},{"type":"timestamp","name":"timestamp","default":"current_timestamp"}]'; // Authtokens
		public static $table3  = '[{"type":"int","name":"userid", "unique":"true"},{"type":"int","name":"group","default":"0"},{"type":"int","name":"access_files","default":"0"},{"type":"int","name":"start_server","default":"0"},{"type":"int","name":"stop_server","default":"0"},{"type":"int","name":"modify_users","default":"0"},{"type":"int","name":"log_access","default":"0"},{"type":"int","name":"server_notify","default":"0"},{"type":"int","name":"server_config","default":"0"}]';
		public static $table4  = '[{"type":"int","name":"id"},{"type":"varchar","name":"permission_name"},{"type":"int","name":"value","default":"0"},{"type":"int","name":"user"}]'; // Custom permissions
		public static $table5  = '[{"type":"int","name":"id"},{"type":"int","name":"type","default":"0"},{"type":"varchar","name":"title"},{"type":"text","name":"text","default":""},{"type":"int","name":"server","default":"0"},{"type":"text","name":"action","default":""},{"type":"int","name":"timestamp","default":"0"},{"type":"text","name":"readed","default":""},{"type":"text","name":"icon"},{"type":"varchar","name":"plugin","default":""},{"type":"int","name":"user"}]';
		public static $table6  = '[{"type":"int","name":"id"},{"type":"varchar","name":"name"},{"type":"text","name":"value"}]';
		public static $table7  = '[{"type":"int","name":"id"},{"type":"text","name":"text"},{"type":"varchar","name":"plugin"},{"type":"int","name":"timestamp"},{"type":"int","name":"user","default":"0"}]';
		public static $table8  = '[{"type":"int","name":"id"},{"type":"varchar","name":"name","default":""},{"type":"text","name":"value"},{"type":"varchar","name":"plugin","default":""},{"type":"varchar","name":"authtoken","default":""},{"type":"int","name":"timestamp"}]'; // Temporary table
		public static $table9  = '[{"type":"int","name":"id"},{"type":"varchar","name":"plugin","default":"file"},{"type":"text","name":"views"},{"type":"text","name":"parameter"},{"type":"varchar","name":"password","default":""},{"type":"int","name":"user"}]'; // Sharekey table
		public static $table10 = '[{"type":"int","name":"id"},{"type":"varchar","name":"name","default":""},{"type":"text","name":"value"},{"type":"varchar","name":"plugin","default":""}]'; // PluginStorage table
		public static $table11  = '[{"type":"int","name":"id"},{"type":"varchar","name":"name"},{"type":"varchar","name":"signature"},{"type":"int","name":"user"},{"type":"int","name":"refused","default":"0"},{"type":"timestamp","name":"timestamp","default":"current_timestamp"}]'; // JWT Tokens
		public static $table12  = '[{"type":"int","name":"id"},{"type":"text","name":"key"},{"type":"varchar","name":"plugin"},{"type":"int","name":"user"}]'; // Keys


		function __construct() 
		{
			global $conf;
			
			$this->db = new mysqli($conf['db_host'], $conf['db_username'], $conf['db_password'], $conf['db_database']);
			
			if(!empty($this->praefix)) {
				$this->praefix .= '_';
			} else {
				$this->praefix = '';
			}
			
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this,$f='__construct'.$i)) {
				call_user_func_array(array($this,$f),$a);
			}
		}
		
		function openTable($plugin, $json) {
			$this->table = $this->praefix . $plugin;
			
			if(is_string($json)) {
				$json = json_decode($json);
			}
			
			$query = 'CREATE TABLE IF NOT EXISTS `' . $this->table . '` ( ';
			
				if(!empty($json))
				foreach($json as $dbelement) {
					if(strtoupper($dbelement->type) == strtoupper('int') || strtoupper($dbelement->type) == strtoupper('integer')) {
						if(strtoupper($dbelement->name) == strtoupper('id')) {
							$query .=  '`' . $dbelement->name . '` int(255) NOT NULL AUTO_INCREMENT UNIQUE';
						} else {
							$query .=  '`' . $dbelement->name . '` int(255) NOT NULL';
							
							if(!empty($dbelement->unique) && $dbelement->unique == "true") {
								$query .= ' UNIQUE';	
							}
						}
						
						if((!empty($dbelement->default) && is_numeric($dbelement->default)) || (isset($dbelement->default) && $dbelement->default !== null && $dbelement->default == 0)) {
							$query .= " DEFAULT '" . $dbelement->default . "'";
						}
						
						$query .= ',';
					} else if(strtoupper($dbelement->type) == strtoupper('varchar')) {
						$query .=  '`' . $dbelement->name . '` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
						
						if( !empty($dbelement->default) && is_string($dbelement->default) ) {
							$query .= " DEFAULT '" . $this->escape($dbelement->default) . "'";
						} else if (isset($dbelement->default) && $dbelement->default !== null && $dbelement->default == "") {
							$query .= " DEFAULT ''";
						}
						
						$query .= ',';
					} else if(strtoupper($dbelement->type) == strtoupper('text')) {
						$query .=  '`' . $dbelement->name . '` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,';
					} else if(strtoupper($dbelement->type) == strtoupper('timestamp')) {
						$query .=  '`' . $dbelement->name . '` timestamp NOT NULL';
						
						if(strtoupper($dbelement->default) == strtoupper('current_timestamp') || strtoupper($dbelement->default) == strtoupper('current_time')) {
							$query .= ' DEFAULT CURRENT_TIMESTAMP';
						} else if(strtoupper($dbelement->default) == strtoupper('empty_string')) {
							$query .= " DEFAULT ''";
						}
						
						$query .= ',';
					}
				}
				
				if(!$this->isServerTable()) {
					$query .=  '`user` int(255) NOT NULL';
				} else {
					$query = substr($query, 0, -1);
				}
				
				$query .= ');';
				
				$this->db->query($query);
				
				if($this->db->errno){
					die($this->db->error);
				}
		}

		function openTables($plugin, $json, $default=0) {
			$this->table = $this->praefix . $plugin;
			$this->plugin = $plugin;
			
			if(is_string($json)) {
				$json = json_decode($json);
			}
			$this->tables = $json;
			
			if(empty($default)) {
				$this->openTable($plugin, $this->tables[$default]);
			} else {
				$this->openTable($plugin . '_' . $default, $this->tables[$default]);
			}
		}

		function selectTable($id) {
			if(!empty($this->tables) && !empty($this->tables[$id])) {
				if(!empty($id)) {
					$this->openTable($this->plugin . '_' . $id, $this->tables[$id]);
				} else {
					$this->openTable($this->plugin, $this->tables[0]);
				}
			}
		}

		private function getValue($options) {
			if(is_array($options)) {
				if(!empty($options['value'])) {
					return $options['value'];
				} else if( isset($options['value']) && $options['value'] == 0 ) {
					return 0;
				}
			} else if(is_string($options)) {
				return $options;
			} else if(is_int($options)) {
				return $options;
			}
			
			return '';
		}

		private function getType($options) {
			if(is_array($options)) {
				if(!empty($options['type'])) {
					$type = strtoupper($options['type']);
					
					if($type == 'I' || $type == 'INT' || $type == 'INTEGER' ) {
						return 'i';
					}
				}
			} else if(is_string($options)) {
				return 's';
			} else if(is_int($options)) {
				return 'i';
			}
			
			return 's';
		}
		
		public function getOperator($options) {
			if(!empty($options['operator'])) {
				$operator = $options['operator'];
				
				if($operator == '>') {
					return '>';
				} else if($operator == '<') {
					return '<';
				}
			}
			
			return '=';
		}

		function getValues() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this,$f='getValues'.$i)) {
				$return = call_user_func_array(array($this,$f),$a);
				return $return;
			}
		}

		private function getValues0() {
			global $loginManager;
			
			if(!$this->isServerTable()) {
				$sql = 'SELECT * FROM ' . $this->table . ' WHERE user=\'' . $loginManager->getId() . '\'';
			} else {
				$sql = 'SELECT * FROM ' . $this->table . ' WHERE 1=1';
			}
			
			$result = $this->db->query($sql);
			$return = Array();
			$i = 0;
			
			if(!empty($result)) {
				while($row = $result->fetch_assoc()) {
					$return[$i] = $row;
					
					if(!$this->isServerTable())
					unset($return[$i]['user']);
					
					$i++;
				}
			}
			
			return $return;
		}
		
		private function getValues1($pArray) {
			return $this->getValues2($pArray, 0);
		}
		
		private function getValues2($pArray, $pLimit) {
			global $loginManager;
			
			if(!$this->isServerTable()) {
				$sql = 'SELECT * FROM ' . $this->table . ' WHERE user=\'' . $loginManager->getId() . '\'';
			} else {
				$sql = 'SELECT * FROM ' . $this->table . ' WHERE 1=1';
			}
			
			$array = null;
			$types = '';
			
			if(!empty($pArray) && is_array($pArray)) {
				if(!empty($pArray[0]) && !empty($pArray[1]) && !empty($pArray[2]) && is_string($pArray[0]) && is_string($pArray[1]) && is_array($pArray[2])) {
					$sql .= ' AND ' . $pArray[0];
					$types = $pArray[1];
					$array = $pArray[2];
				} else {
					foreach($pArray as $name=>$options) {
						if(is_numeric($name) && !empty($options['name'])) {
							$name = $options['name'];
						}

						$operator = $this->getOperator($options);
						$value = $this->getValue($options);
						$type = $this->getType($options);
						
						$array[] = $value;
						$types .= $type;
						
						
						if(!empty($options['or']) && is_array($options['or']) && !empty($options['or']['name'])) {
							$operatorOr = $this->getOperator($options['or']);
							$valueOr = $this->getValue($options['or']);
							$typeOr = $this->getType($options['or']);
							
							$array[] = $valueOr;
							$types .= $typeOr;
							
							$sql .= ' AND (`' . $name . '`' . $operator . '? OR `' . $options['or']['name'] . '`' . $operatorOr . '?)';
						} else {
							$sql .= ' AND `' . $name . '`' . $operator . '?';
						}
					}
				}
			}
			
			if(!empty($pLimit) && is_int($pLimit)) {
				$sql .= ' Limit 0,'.$pLimit;
			}
			
			if(!empty($pLimit) && is_array($pLimit)) { //pLimit is an order-statement
				$string = '';
				
				foreach($pLimit as $statement) {
					if(!empty($statement['name'])) {
						$string .= $statement['name'];
						
						if(!empty($statement['desc'])) {
							$string .= ' DESC,';
						} else if(!empty($statement['asc'])) {
							$string .= ' ASC,';
						} else {
							$string .= ' ASC,';
						}
					}
				}
				
				if(!empty($string) && $string != '') {
					$string = rtrim($string, ",");
					$sql .= ' ORDER BY ' . $string;
				}
			}
			
			if ($stmt = $this->db->prepare($sql)) {
				//$stmt->bind_param($types, $pUsername);
				
				if(!empty($array)) {
					array_unshift($array, $types);
					call_user_func_array(array($stmt, "bind_param"), $this->refValues($array));
				}
				
				$stmt->execute();
				
				if($this->db->errno) {
					die($this->db->error);
				}
				$result = $stmt->get_result();
				
				$i = 0;
				$return = null;
				
				while($row = $result->fetch_assoc()) {
					$return[$i] = $row;
					//unset($return[$i]['user']);  /* TODO */
					
					$i++;
				}
				
				if(!empty($return[0]) && $pLimit == 1) {
					$return = $return[0];
				}
				
				$stmt->close();
				
				if($return != null)
				return $return;
			}
			
			return null;
		}

		function setValue() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this,$f='setValue'.$i)) {
				$return = call_user_func_array(array($this,$f),$a);
				return $return;
			}
		}
		
		private function setValue2($pArray, $pOperator) {
			global $loginManager;
			
			$sql = 'UPDATE ' . $this->table . '';
			
			$array = null;
			$types = ''; /* TODO */
			
			$first = TRUE;
			
			foreach($pArray as $name=>$options) {
				if(empty($name)) {
					continue;
				}
				
				if($first) {
					$sql .= ' SET';
					$first = FALSE;
				}
				
				$operator = $this->getOperator($options);
				$value = $this->getValue($options);
				$type = $this->getType($options);
				
				$sql .= ' `' . $name . '`=?,';
				$array[] = $value;
				$types .= $type;
			}
			$sql = rtrim($sql, ',');
			
			if($this->isServerTable()) {
				$sql .= ' WHERE 1=1';
			} else {
				$sql .= ' WHERE user=\'' . $loginManager->getId() . '\'';
			}
			
			$oArray = null;
			$oTypes = ''; /* TODO */
			
			foreach($pOperator as $name=>$options) {
				$operator = $this->getOperator($options);
				$value = $this->getValue($options);
				$type = $this->getType($options);
				
				$array[] = $value;
				$types .= $type;
				
				
				if(!empty($options['or']) && is_array($options['or']) && !empty($options['or']['name'])) {
					$operatorOr = $this->getOperator($options['or']);
					$valueOr = $this->getValue($options['or']);
					$typeOr = $this->getType($options['or']);
					
					$array[] = $valueOr;
					$types .= $typeOr;
					
					$sql .= ' AND (`' . $name . '`' . $operator . '? OR `' . $options['or']['name'] . '`' . $operatorOr . '?)';
				} else {
					$sql .= ' AND `' . $name . '`' . $operator . '?';
				}
			}
			
			if ($stmt = $this->db->prepare($sql)) {
				
				array_unshift($array, $types);
				
				call_user_func_array(array($stmt, "bind_param"), $this->refValues($array)); 
				
				$stmt->execute();
				$stmt->close();
				
				return true;
			}
			
			return false;
		}

		function insertValue() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this,$f='insertValue'.$i)) {
				return call_user_func_array(array($this,$f),$a);
			}
		}
		
		private function insertValue1($pArray) {
			global $loginManager;
			
				$sql = 'INSERT INTO ' . $this->table . ' ( ';
				
				$array = null;
				$types = ''; /* TODO */
				$userIsSet = FALSE;
				
				$sqlTmp = '';
				
				foreach($pArray as $name=>$options) {
					if(empty($name)) {
						continue;
					}
					
					$value = $this->getValue($options);
					$type = $this->getType($options);
					
					if($name == 'user') $userIsSet = TRUE;
					
					$sql .= '`' . $name . '`,';
					$array[] = $value;
					$sqlTmp .= '?,';
					$types .= $type;
				}
				
				if(!$this->isServerTable() && !$userIsSet) {
					if(empty($loginManager->getId()))
						die("Benutzer nicht angemeldet.");
					
					$sql .= '`user`)';
					$sql .= ' VALUES (' . $sqlTmp . '\'' . $loginManager->getId() . '\')';
				} else {
					$sql    = substr($sql, 0, -1);
					$sqlTmp = substr($sqlTmp, 0, -1);
					
					$sql .= ') VALUES (' . $sqlTmp . ')';
				}
				
				if ($stmt = $this->db->prepare($sql)) {
					array_unshift($array, $types);
					
					call_user_func_array(array($stmt, "bind_param"), $this->refValues($array)); 
					
					$stmt->execute();
					
					if($this->db->errno) {
						die($this->db->error);
					}
					
					$stmt->close();
					
					return true;
				} else {
					die($this->db->error);
				}
			return false;
		}

		public function insertOrUpdateValue($pValue, $pOperators) {
			global $loginManager;
			
			if(!empty($loginManager->getId())) {
				$return = $this->getValues($pOperators, 1);
				
				if(!empty($return)) {
					if($this->setValue($pValue, $pOperators)) {
						return true;
					}
				} else {
					if($this->insertValue(array_merge($pValue, $pOperators))) {
						return true;
					}
				}
			}
			
			return false;
		}

		private function refValues($arr) {
			$refs = array();
			
			foreach ($arr as $key => $value) {
				$refs[$key] = &$arr[$key];
			}
			
			return $refs;
		}
		
		function getInsertId() {
			return $this->db->insert_id;
		}
		
		private function isServerTable() {
			if(!empty($this->praefix)) {
				$userTable[] = $this->praefix.'_users';
				$userTable[] = $this->praefix.'_authtokens';
				$userTable[] = $this->praefix.'_user_permissions';
				$userTable[] = $this->praefix.'_custom_user_permissions';
				$userTable[] = $this->praefix.'_notifications';
				$userTable[] = $this->praefix.'_logs';
				$userTable[] = $this->praefix.'_share';
				$userTable[] = $this->praefix.'_jwt';
				$userTable[] = $this->praefix.'_keys';
			} else {
				$userTable[] = 'users';
				$userTable[] = 'authtokens';
				$userTable[] = 'user_permissions';
				$userTable[] = 'custom_user_permissions';
				$userTable[] = 'notifications';
				$userTable[] = 'logs';
				$userTable[] = 'share';
				$userTable[] = 'jwt';
				$userTable[] = 'keys';
			}
			
			if( in_array($this->table, $userTable) ) {
				return true;
			}
			
			if(constant('WEBSOCKET') == 1) {
				return true;
			}
			
			return false;
		}
		
		function getErrors() {
			return $this->db->error;
		}
		
		function remove() {
			$a = func_get_args(); 
			$i = func_num_args(); 
			
			if (method_exists($this,$f='remove'.$i)) {
				$return = call_user_func_array(array($this,$f),$a);
				return $return;
			}
		}
		
		private function remove1($pArray) {
			global $loginManager;
			
			if(!$this->isServerTable()) {
				$sql = 'DELETE FROM ' . $this->table . ' WHERE user=\'' . $loginManager->getId() . '\'';
			} else {
				$sql = 'DELETE FROM ' . $this->table . ' WHERE 1=1';
			}
			
			$array = null;
			$types = '';
			
			if(!empty($pArray))
			foreach($pArray as $name=>$options) {
				$operator = $this->getOperator($options);
				$value = $this->getValue($options);
				$type = $this->getType($options);
				
				$sql .= ' AND `' . $name . '`' . $operator . '?';
				$array[] = $value;
				$types .= $type;
			}
			
			if ($stmt = $this->db->prepare($sql)) {
				//$stmt->bind_param($types, $pUsername);
				
				array_unshift($array, $types);
				call_user_func_array(array($stmt, "bind_param"), $this->refValues($array)); 
				
				$stmt->execute();
				$result = $stmt->get_result();
				
				return $this->db->affected_rows;
			}
			
			return null;
		}

		public function escape($pString) {
			$pString = str_replace("'", "''", $pString);
			return $pString;
		}
	}

/*
		function install() {
			$query1 = "CREATE TABLE IF NOT EXISTS `users`(
					`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`username` VARCHAR(100) NOT NULL,
					`digesta1` TEXT NOT NULL,
					`level` INT NOT NULL DEFAULT '0',
					UNIQUE (`username`));";
					
			$query2 = "INSERT IGNORE INTO `users` (
					`username`,`digesta1`,`level`)
					VALUES ('admin','87fd274b7b6c01e48d7c2f965da8ddf7','10');";
					
			$query3 = "CREATE TABLE IF NOT EXISTS principals (
					id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					uri VARBINARY(200) NOT NULL,
					email VARBINARY(80),
					displayname VARCHAR(80),
					UNIQUE(uri));";
					
			$query4 = "CREATE TABLE IF NOT EXISTS groupmembers (
					id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					principal_id INTEGER UNSIGNED NOT NULL,
					member_id INTEGER UNSIGNED NOT NULL,
					UNIQUE(principal_id, member_id));";
					
			$query5 = "INSERT INTO principals (uri,email,displayname) VALUES
					('principals/admin', 'admin@example.org','Administrator'),
					('principals/admin/calendar-proxy-read', null, null),
					('principals/admin/calendar-proxy-write', null, null);";
					
			$query6 = "CREATE TABLE IF NOT EXISTS addressbooks (
					id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					principaluri VARBINARY(255),
					displayname VARCHAR(255),
					uri VARBINARY(200),
					description TEXT,
					synctoken INT(11) UNSIGNED NOT NULL DEFAULT '1',
					UNIQUE(principaluri(100), uri(100))
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

			$query7 = "CREATE TABLE IF NOT EXISTS cards (
					id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					addressbookid INT(11) UNSIGNED NOT NULL,
					carddata MEDIUMBLOB,
					uri VARBINARY(200),
					lastmodified INT(11) UNSIGNED,
					etag VARBINARY(32),
					size INT(11) UNSIGNED NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
					
			$query8 = "CREATE TABLE addressbookchanges (
					id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					uri VARBINARY(200) NOT NULL,
					synctoken INT(11) UNSIGNED NOT NULL,
					addressbookid INT(11) UNSIGNED NOT NULL,
					operation TINYINT(1) NOT NULL,
					INDEX addressbookid_synctoken (addressbookid, synctoken)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			
			$this->db->query($query1);
			$this->db->query($query2);
			$this->db->query($query3);
			$this->db->query($query4);
			$this->db->query($query5);
			$this->db->query($query6);
			$this->db->query($query7);
			$this->db->query($query8);
		}
		
		function insert($pNamesValues) {
			$query[0] = Array();
			$query[1] = Array();
			$query[2] = Array();
			$query[3] = Array();
			
			$i = 0;
			
			if($this->dbnames === NULL) {
				return true;
			}
			
			foreach($pNamesValues as $pNameValue) {
				foreach($this->dbnames as $dbname) {
					if($dbname[0] == $pNameValue[0]) {
						$query[0][$i] = $pNameValue[0];
						$query[1][$i] = &$pNameValue[1];
						
						if(strtoupper($dbname[1]) == strtoupper('int')) {
							$query[2][$i] = 'i';
						}
						else if(strtoupper($dbname[1]) == strtoupper('varchar')) {
							$query[2][$i] = 's';
						}
						
						$query[3][$i] = '?';
						
						$i++; 
					}
				}
			}
			
			$length = count($query[0]);
			
			$query[0][$length] = 'user';
			$query[1][$length] = '1';
			$query[2][$length] = 'i';
			$query[3][$length] = '?';

			array_unshift($query[1], implode('', $query[2]));
			
			$sql = 'INSERT INTO ' . $this->table . ' (' . implode(",", $query[0]) . ') VALUES (' . implode(",", $query[3]) . ')';
			$stmt = $this->db->prepare($sql);
			
			call_user_func_array(array($stmt, "bind_param"), $this->refValues($query[1])); 
			mysqli_stmt_execute($stmt);
		}
		
		function getLogin($pUsername) {
			$query = 'SELECT * FROM users WHERE UPPER(username)=UPPER(?)';
			
			if ($stmt = $this->db->prepare($query)) {
				$stmt->bind_param("s", $pUsername);
				$stmt->execute();
				$result = $stmt->get_result();
				
				$return = $result->fetch_array(MYSQLI_ASSOC);
				
				$stmt->close();
				
				return $return;
			}
			
			return null;
		}

		function getValues1($pString) {
			$pString = str_replace(Array('WHERE', 'where'), '', $pString);
			
			$sql = 'SELECT * FROM ' . $this->table . ' WHERE user=1 AND' . $pString;
			
			$result = $this->db->query($sql);
			
			$return = Array();
			$i = 0;
			
			while($row = $result->fetch_assoc()) {
				foreach ($this->dbnames as $dbname) {
					$return[$i] = $row;
					unset($return[$i]['user']);
				}
				
				$i++;
			}
			
			return $return;
		}
		
		function close() {
			$this->db->close();
			unset($this->db);
		}
	}
*/
?>