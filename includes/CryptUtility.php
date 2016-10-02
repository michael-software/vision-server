<?php
 
  /** 
   * Class with Encrypt- and Decrypt-Functions
   */
  class CryptUtility {
     
    // Data representation
    public static $DATA_AS_IS = 0;
    public static $DATA_AS_BASE64 = 1;
    public static $DATA_AS_HEX = 2;
     
    /**
     * Adds pkcs5 padding
     * @return Given text with pkcs5 padding
     * @param string $data
     *   String to pad
     * @param integer $blocksize
     *   Blocksize used by encryption
     */
    private static function pkcs5Pad($data, $blocksize){
         
        $pad = $blocksize - (strlen($data) % $blocksize);
        $returnValue = $data . str_repeat(chr($pad), $pad);
         
        return $returnValue;
    }
     
    /**
     * Removes padding
     * @return Given text with removed padding characters
     * @param string $data
     *   String to unpad
     */
    private static function pkcs5Unpad($data) {
       
      $pad = ord($data{strlen($data)-1});
      if ($pad > strlen($data)) return false;
      if (strspn($data, chr($pad), strlen($data) - $pad) != $pad) return false;
       
      return substr($data, 0, -1 * $pad);
    }
     
    /**
     * Encrypts a string with the Advanced Encryption Standard.
     * 
     * The used algorythm (cipher) is MCRYPT_RIJNDAEL_128 and the mode is 'cbc' (cipher block chaining).
     * 
     * @return Encrypted text as hexadecimal representation
     * @param string $data
     *   String to encrypt
     * @param string $key
     *   Key
     * @param string $iv
     *   Initialization vector (IV) - 16 char
     * @param  integer $dataAs [optional]
     *   Encode data after encryption as (CryptUtility::$DATA_AS_*) - Default CryptUtility::$DATA_AS_IS
     */
    public static function aesEncrypt($data, $key, $iv, $dataAs = 0) {
           
      $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
      $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
       
      // Add padding to String
      $data = CryptUtility::pkcs5Pad($data, $size);
      $length = strlen($data);
         
      mcrypt_generic_init($cipher, $key, $iv);
       
      $data = mcrypt_generic($cipher,$data);
       
      if($dataAs == CryptUtility::$DATA_AS_HEX) {
        $data = bin2hex($data);
      } else if ($dataAs == CryptUtility::$DATA_AS_BASE64) {
        $data = base64_encode($data);
      }
       
      mcrypt_generic_deinit($cipher);
       
      return $data;
    }
     
    /**
     * Decrypts a string with the Advanced Encryption Standard.
     * 
     * The used algorythm (cipher) is MCRYPT_RIJNDAEL_128 and the mode is 'cbc' (cipher block chaining).
     * 
     * @return Decrypted text
     * @param string $data
     *   String to decrypt as hexadecimal representation
     * @param string $key
     *   Key
     * @param string $iv
     *   Initialization vector (IV) - 16 char
     * @param  integer $dataAs [optional]
     *   Decode data before decryption as (CryptUtility::$DATA_AS_*) - Default CryptUtility::$DATA_AS_IS
     */
    public static function aesDecrypt($data, $key, $iv, $dataAs = 0) {
       
      $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
      $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
       
      mcrypt_generic_init($cipher, $key, $iv);
       
      if($dataAs == CryptUtility::$DATA_AS_HEX) {
        // pack() is used to convert hex string to binary
        $data = pack('H*', $data);
      } else if ($dataAs == CryptUtility::$DATA_AS_BASE64) {
        $data = base64_decode($data);
      }
       
      $data = mdecrypt_generic($cipher, $data);
      mcrypt_generic_deinit($cipher);
       
      return CryptUtility::pkcs5Unpad($data);
    }
	
	private static function crypto_rand_secure($min, $max) {
		if(function_exists('openssl_random_pseudo_bytes')) {
		    $range = $max - $min;
		    if ($range < 1) return $min; // not so random...
		    $log = ceil(log($range, 2));
		    $bytes = (int) ($log / 8) + 1; // length in bytes
		    $bits = (int) $log + 1; // length in bits
		    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		    do {
		        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
		        $rnd = $rnd & $filter; // discard irrelevant bits
		    } while ($rnd >= $range);
		    return $min + $rnd;
		} else {
			return mt_rand($min, $max); /* UNSECURE */
		}
	}

	public static function getSecureKey() {
		$length = 64;
	    $token = "";
	    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	    $codeAlphabet.= "0123456789";
	    $max = strlen($codeAlphabet) - 1;
		
	    for ($i=0; $i < $length; $i++) {
	        $token .= $codeAlphabet[CryptUtility::crypto_rand_secure(0, $max)];
	    }
		
		return $token;
	}
  
  }
 
?>