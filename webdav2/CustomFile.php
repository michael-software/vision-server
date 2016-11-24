<?php

namespace MyServer;

class ACLFile extends \Sabre\DAV\FS\File implements \Sabre\DAVACL\IACL {

    use ACLTrait;

    function __construct($path, $owner) {

        parent::__construct($path);
        $this->owner = $owner;

    }

}

?>