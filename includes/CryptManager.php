<?php

require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Exception/CryptoException.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Exception/BadFormatException.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Exception/EnvironmentIsBrokenException.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Exception/IOException.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Exception/WrongKeyOrModifiedCiphertextException.php';

require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Core.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Crypto.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/DerivedKeys.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Encoding.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/File.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/Key.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/KeyOrPassword.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/KeyProtectedByPassword.php';
require_once dirname(dirname(__FILE__)) . '/libs/php-encryption-master/src/RuntimeTests.php';

class CryptManager {
    function encryptAES($plaintext, &$key, $password=null) {
        if($key instanceof Defuse\Crypto\KeyProtectedByPassword && !empty($password) && is_string($password)) {
            $key = $key->unlockKey($password);

            return Defuse\Crypto\Crypto::encrypt($plaintext, $key, false);
        } else if($key instanceof Defuse\Crypto\Key) {
            return Defuse\Crypto\Crypto::encrypt($plaintext, $key, false);
        }

        return null;
    }

    function getPwKey($password) {
        return Defuse\Crypto\KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
    }

    function saveKey($key, $override=false) {

    }

    function unlockAsciiKey($key, $password) {
        return Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($key)->unlockKey($password);
    }

    function decryptAES($data, &$key, $password=null) {
        if($key instanceof Defuse\Crypto\KeyProtectedByPassword && !empty($password) && is_string($password)) {
            $key = $key->unlockKey($password);

            return Defuse\Crypto\Crypto::decrypt($data, $key, false);
        } else if($key instanceof Defuse\Crypto\Key) {
            return Defuse\Crypto\Crypto::decrypt($data, $key, false);
        }

        return null;
    }

    function getAes256Hash($key) {
        return hash('sha256', $key);
    }

    function getAesIv() {
        $wasItSecure = false;
        $iv = openssl_random_pseudo_bytes(4, $wasItSecure);
        if ($wasItSecure) {
            return $iv;
        }

        return null;
    }
}

?>