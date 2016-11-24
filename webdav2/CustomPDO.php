<?php

namespace MyServer;

use Sabre\DAVACL\PrincipalBackend\PDO;

class CustomPDO extends PDO {
    function __construct(\PDO $pdo) {
        parent::__construct($pdo);

        $this->fieldMap['userid'] =  [
            'dbField' => 'userid'
        ];
    }
}

?>