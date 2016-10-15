<?php


class JwtManager {
    private $privateKey = "1234567891234567";
	private $iv = "1234567891234567";

    public function validateJwt($jwt, $secret) {
        $jwt = explode('.', $jwt);

        if(count($jwt) != 3) {
            return 0;
        }

        $signature = $this->base64url_encode(   hash_hmac('sha256', $jwt[0] . "." . $jwt[1], $secret, true)   );

        if($jwt[2] == $signature) {
            $jwt[1] = json_decode( base64_decode($jwt[1]) );

            if(!empty($jwt[1]) && !empty($jwt[1]->exp)) {
                if($jwt[1]->exp > time()) {
                    return 1;
                } else {
                    return 2;
                }
            }
        }

        return 0;
    }

    private function base64url_encode($data) { 
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	} 

	private function base64url_decode($data) { 
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
	}

    private function getHeader() {
        $header['alg'] = "HS256";
		$header['typ'] = "JWT";
        return (Object) $header;
    }

    private function jwtEncode($object) {
        return $this->base64url_encode(json_encode((Object) $object));
    }


    public function createJwt($pUsername, $_sek = null, $secret, $pPayload = array(), $time = 60) {
        /* AUTHTOKEN */
		
		$header = $this->jwtEncode($this->getHeader());


		$payload['exp'] = time() + $time*60;
		$payload['name'] = $pUsername;

        if(!empty($_sek))
		    $payload['_sek'] = $_sek;

        $payload['jti'] = uniqid();

        $payload = array_merge($pPayload, $payload);

		$payload = $this->jwtEncode($payload);

        return $this->signate($header, $payload, $secret);
    }

    public function getJwtData($jwt, $secret = null) {
        if(!empty($secret)) {
            if($this->validateJwt($jwt, $secret) != 1) {
                return null;
            }
        }

        $jwt = explode('.', $jwt);
        $json = json_decode( $this->base64url_decode($jwt[1]) );

        return $json;
    }

    private function signate($header, $payload, $secret) {
        $signature = hash_hmac('sha256', $header . "." . $payload, $secret, true);

		return $header . '.' . $payload . '.' . $this->base64url_encode($signature);
    }

    public function setData($jwt, $array, $secret) {
        $data = $this->getJwtData($jwt);

        if(!empty($array['name'])) {
            unset($array['name']);
        }

        if(!empty($array['exp'])) {
            unset($array['exp']);
        }

        $array = array_merge((array) $data, $array);

        $payload = $this->jwtEncode( $array );
        $header = $this->jwtEncode( $this->getHeader() );

        return $this->signate($header, $payload, $secret);
    }

    public function getSignature($jwt) {
        return explode('.', $jwt)[2];
    }
}

?>