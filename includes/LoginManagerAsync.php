<?php


class LoginManagerAsync {
    private $userid;

    public function __construct($userid) {
        if(constant('WEBSOCKET') == 1)
            $this->userid = $userid;
    }

    public function getId() {
        return $this->userid;
    }
}

?>