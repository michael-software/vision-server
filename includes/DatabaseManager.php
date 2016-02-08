<?php

	class DatabaseManager
	{
		private $db;
		
		private $host = 'localhost';
		private $username = 'root';
		private $password = '';
		private $database = 'vision';
		
		private $praefix = null;
		
		private $table = null;
		
		public static $table2 = '[{"type":"int","name":"id"},{"type":"varchar","name":"authtoken"},{"type":"timestamp","name":"timestamp","default":"current_timestamp"}]';
		public static $table3 = '[{"type":"int","name":"userid", "unique":"true"},{"type":"int","name":"group","default":"0"},{"type":"int","name":"access_files","default":"0"},{"type":"int","name":"stop_server","default":"0"},{"type":"int","name":"modify_users","default":"0"}]';
		public static $table4 = '[{"type":"int","name":"id"},{"type":"varchar","name":"permission_name"},{"type":"int","name":"value","default":"0"},{"type":"int","name":"user"}]';
		
		
		function __construct() 
		{
			$this->db = new mysqli($this->host, $this->username, $this->password, $this->database);
			
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
			
			$query = 'CREATE TABLE IF NOT EXISTS `' . $this->table . '` ( ';
			
				foreach($json as $dbelement) {
					if(strtoupper($dbelement->type) == strtoupper('int') || strtoupper($dbelement->type) == strtoupper('integer')) {
						if(strtoupper($dbelement->name) == strtoupper('id')) {
							$query .=  '`' . $dbelement->name . '` int(255) NOT NULL AUTO_INCREMENT UNIQUE';
						}
						else {
							$query .=  '`' . $dbelement->name . '` int(255) NOT NULL';
							
							if(!empty($dbelement->unique) && $dbelement->unique == "true") {
								$query .= ' UNIQUE';	
							}
						}
						
						if(!empty($dbelement->default) && is_numeric($dbelement->default)) {
							$query .= "DEFAULT '" . $dbelement->default . "'";
						}
						
						$query .= ',';
					} else if(strtoupper($dbelement->type) == strtoupper('varchar')) {
						$query .=  '`' . $dbelement->name . '` varchar(255) NOT NULL';
						
						$query .= ',';
					} else if(strtoupper($dbelement->type) == strtoupper('text')) {
						$query .=  '`' . $dbelement->name . '` TEXT NOT NULL';
						
						$query .= ',';
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
			if(!$this->isServerTable()) {
				$sql = 'SELECT * FROM ' . $this->table . ' WHERE user=\'' . $_SESSION['id'] . '\'';
			} else {
				$sql = 'SELECT * FROM ' . $this->table . ' WHERE 1=1';
			}
			//$sql = 'SELECT * FROM ' . $this->table . ' WHERE user=\'2\'';
			
			$result = $this->db->query($sql);
			$return = Array();
			$i = 0;
			
			while($row = $result->fetch_assoc()) {
				$return[$i] = $row;
				unset($return[$i]['user']);
				
				$i++;
			}
			
			return $return;
		}
		
		private function getValues1($pArray) {
			return $this->getValues2($pArray, 0);
		}
		
		private function getValues2($pArray, $pLimit) {
			if(!$this->isServerTable()) {
				$sql = 'SELECT * FROM ' . $this->table . ' WHERE user=\'' . $_SESSION['id'] . '\'';
			} else {
				$sql = 'SELECT * FROM ' . $this->table . ' WHERE 1=1';
			}
			
			$array = null;
			$types = '';
			
			if(!empty($pArray))
			foreach($pArray as $name=>$options) {
				if(!empty($options['operator'])) {
					$operator = $options['operator'];
					
					if($operator != '=' AND $operator != '>' AND $operator != '<') {
						$operator = '=';
					}
				} else {
					$operator = '=';
				}
				
				if(!empty($options['value'])) {
					$value = $options['value'];
				} else {
					$value = '';
				}
				
				if(!empty($options['type'])) {
					$type = $options['type'];
					
					if($options['type'] != 's' AND $options['type'] != 'i') {
						$type = 's';
					}
				} else {
					$type = 's';
				}
				
				$sql .= ' AND `' . $name . '`' . $operator . '?';
				$array[] = $value;
				$types .= $type;
			}
			
			if(!empty($pLimit)) {
				$sql .= ' Limit 0,'.$pLimit;
			}
			
			if ($stmt = $this->db->prepare($sql)) {
				//$stmt->bind_param($types, $pUsername);
				
				array_unshift($array, $types);
				call_user_func_array(array($stmt, "bind_param"), $this->refValues($array)); 
				
				$stmt->execute();
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
			$sql = 'UPDATE ' . $this->table . '';
			
			$array = null;
			$types = ''; /* TODO */
			
			foreach($pArray as $name=>$options) {
				if(!empty($options['operator'])) {
					$operator = $options['operator'];
					
					if($operator != '=' AND $operator != '>' AND $operator != '<') {
						$operator = '=';
					}
				} else {
					$operator = '=';
				}
				
				if(empty($name)) {
					continue;
				}
				
				if(!empty($options['value'])) {
					$value = $options['value'];
				} else {
					$value = '';
				}
				
				if(!empty($options['type'])) {
					$type = $options['type'];
					
					if($options['type'] != 's' AND $options['type'] != 'i') {
						$type = 's';
					}
				} else {
					$type = 's';
				}
				
				$sql .= ' SET `' . $name . '`=?';
				$array[] = $value;
				$types .= $type;
			}
			
			if($this->isServerTable()) {
				$sql .= ' WHERE 1=1';
			} else {
				$sql .= ' WHERE user=\'' . $_SESSION['id'] . '\'';
			}
			
			$oArray = null;
			$oTypes = ''; /* TODO */
			
			foreach($pOperator as $name=>$options) {
				if(!empty($options['operator'])) {
					$operator = $options['operator'];
					
					if($operator != '=' AND $operator != '>' AND $operator != '<') {
						$operator = '=';
					}
				} else {
					$operator = '=';
				}
				
				if(!empty($options['value'])) {
					$value = $options['value'];
				} else {
					$value = '';
				}
				
				if(!empty($options['type'])) {
					$type = $options['type'];
					
					if($options['type'] != 's' AND $options['type'] != 'i') {
						$type = 's';
					}
				} else {
					$type = 's';
				}
				
				$sql .= ' AND `' . $name . '`' . $operator . '?';
				$array[] = $value;
				$types .= $type;
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
			if(!empty($_SESSION['id'])) {
				$sql = 'INSERT INTO ' . $this->table . ' ( ';
				
				$array = null;
				$types = ''; /* TODO */
				
				$sqlTmp = '';
				
				foreach($pArray as $name=>$options) {
					if(empty($name)) {
						continue;
					}
					
					if(!empty($options['value'])) {
						$value = $options['value'];
					} else {
						$value = '';
					}
					
					if(!empty($options['type'])) {
						$type = $options['type'];
						
						if($options['type'] != 's' AND $options['type'] != 'i') {
							$type = 's';
						}
					} else {
						$type = 's';
					}
					
					$sql .= '`' . $name . '`,';
					$array[] = $value;
					$sqlTmp .= '?,';
					$types .= $type;
				}
				
				if(!$this->isServerTable()) {
					$sql .= '`user`)';
					$sql .= ' VALUES (' . $sqlTmp . '\'' . $_SESSION['id'] . '\')';
				} else {
					$sql    = substr($sql, 0, -1);
					$sqlTmp = substr($sqlTmp, 0, -1);
					
					$sql .= ') VALUES (' . $sqlTmp . ')';
				}
				
				if ($stmt = $this->db->prepare($sql)) {
					array_unshift($array, $types);
					
					call_user_func_array(array($stmt, "bind_param"), $this->refValues($array)); 
					
					$stmt->execute();
					$stmt->close();
					
					return true;
				}
			}
			return false;
		}

		public function insertOrUpdateValue($pValue, $pOperators) {
			if(!empty($_SESSION['id'])) {
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
				$userTable1 = $this->praefix.'_user';
				$userTable2 = $this->praefix.'_authtokens';
				$userTable3 = $this->praefix.'_user_permissions';
				$userTable4 = $this->praefix.'_custom_user_permissions';
			} else {
				$userTable1 = 'user';
				$userTable2 = 'authtokens';
				$userTable3 = 'user_permissions';
				$userTable4 = 'custom_user_permissions';
			}
			
			if($this->table == $userTable1 || $this->table == $userTable2 || $this->table == $userTable3 || $this->table == $userTable4) {
				return true;
			}
			
			return false;
		}
		
		function getErrors() {
			return $this->db->error;
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