<?php

class WebSocketUser {

  public $socket;
  public $id;
  public $headers = array();
  public $handshake = false;
  public $ip;
  public $dektop = false;
  
  public $authtoken = "";
  public $userid = null;
  public $allowedServerNotify = false;
  public $group = 4;

  public $handlingPartialPacket = false;
  public $partialBuffer = "";

  public $sendingContinuous = false;
  public $partialMessage = "";
  
  public $hasSentClose = false;

  function __construct($id, $socket) {
    $this->id = $id;
    $this->socket = $socket;
	
	socket_getpeername($socket, $address, $port);
	
	$this->ip = $address . ':' . $port;
  }
}