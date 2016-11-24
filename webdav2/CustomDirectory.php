<?php

namespace MyServer;

use Sabre\DAV\Exception\NotFound as NotFound;

class ACLDirectory extends \Sabre\DAV\FS\Directory implements \Sabre\DAVACL\IACL {

    use ACLTrait;

    function __construct($path, $owner) {
        parent::__construct($path);
        $this->owner = $owner;
    }

    function getChild($name) {

        $path = $this->path . '/' . $name;

        if (!file_exists($path)) throw new NotFound('File with name ' . $path . ' could not be located');

        if (is_dir($path)) {

            return new ACLDirectory($path, $this->owner);

        } else {

            return new ACLFile($path, $this->owner);

        }
    }

    function getChildren() {

        $result = [];
        foreach(scandir($this->path) as $file) {

            if ($file==='.' || $file==='..' || !$this->isVisible($this->path . '/' . $file)) {
                continue;
            }

            $result[] = $this->getChild($file);
        }

        return $result;

    }


    private function isVisible($path) {
        $name = $this->getBaseName($path);

        if(preg_match("/\/\.[^\/]/", '/' . $path)) {
            return false;
        }

        if(substr($name, 0, 1) == '.') {
            return false;
        }

        return true;
    }

    private function getBaseName($path) {
        return pathinfo($path, PATHINFO_BASENAME);
    }

}

?>