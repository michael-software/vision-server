<?php

function getHost($url) {
    return parse_url ( $url, PHP_URL_HOST );
}

function getPort($url) {
    return parse_url ( $url, PHP_URL_PORT );
}


function httpRequest($host, $port, $method, $path, $params=null) {
    // Params are a map from names to values
    $paramStr = "";
    if(!empty($params) && is_array($params))
    foreach ($params as $name=>$val) {
        $paramStr .= $name . "=";
        $paramStr .= urlencode($val);
        $paramStr .= "&";
    }

    // Assign defaults to $method and $port, if needed
    if (empty($method)) {
        $method = 'GET';
    }
    $method = strtoupper($method);
    if (empty($port)) {
        $port = 80; // Default HTTP port
    }

    // Create the connection
    $sock = fsockopen($host, $port);
    if ($method == "GET") {
        $path .= "?" . $paramStr;
    }
    fputs($sock, "$method $path HTTP/1.1\r\n");
    fputs($sock, "Host: $host\r\n");
    fputs($sock, "Content-type: " .
                "application/x-www-form-urlencoded\r\n");
    if ($method != "GET") {
        fputs($sock, "Content-length: " . 
                    strlen($paramStr) . "\r\n");
    }
    fputs($sock, "Connection: close\r\n\r\n");
    if ($method != "GET") {
        fputs($sock, $paramStr);
    }

    // Buffer the result
    $result = "";
    while (!feof($sock)) {
        $result .= fgets($sock,1024);
    }

    fclose($sock);
    return parseHttpResult($result);
}

function parseHttpResult($string) {
    if(!empty($string) && is_string($string)) {
        $array = explode("\n", $string);

        $head = [];
        $body = [];

        $isHead = true;

        if(!empty($array) && is_array($array))
        for($i = 0, $z = count($array); $i < $z; $i++) {
            if($isHead && $array[$i] == "\r") {
                $isHead = false;
            } else if($isHead) {
                $head[] = $array[$i];
            } else {
                $body[] = $array[$i];
            }
        }
        
        return array( "header"=>$head, "body"=>implode("\n", $body) );
        
    }

    return null;
}

?>