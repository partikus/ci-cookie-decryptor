<?php

namespace App;

class Encrypt
{
    private $encryptionKey;

    /**
     * Type of hash operation
     * @var string
     */
    protected $hashType = 'sha1';

    /**
     * Flag for the existence of mcrypt
     * @var bool
     */
    protected $mcrytpExists = false;

    /**
     * Current cipher to be used with mcrypt
     * @var string
     */
    protected $mcryptCipher;

    /**
     * Method for encrypting/decrypting data
     * @var int
     */
    protected $mcryptMode;

    /**
     * Initialize Encryption class
     * @return    void
     */
    public function __construct()
    {
        if (($this->mcrytpExists = function_exists('mcrypt_encrypt')) === false) {
            new \RuntimeException('The Encrypt library requires the Mcrypt extension.');
        }
    }

    /**
     * Fetch the encryption key
     *
     * Returns it as MD5 in order to have an exact-length 128 bit key.
     * Mcrypt is sensitive to keys that are not the correct length
     *
     * @param    string
     * @return    string
     */
    public function getKey($key = '')
    {
        if ($key === '') {
            if ($this->encryptionKey !== '') {
                return $this->encryptionKey;
            }

            if (!self::strlen($key)) {
                new \RuntimeException('In order to use the encryption class requires that you set an encryption key in your config file.');
            }
        }

        return md5($key);
    }

    /**
     * Encode
     *
     * Encodes the message string using bitwise XOR encoding.
     * The key is combined with a random hash, and then it
     * too gets converted using XOR. The whole thing is then run
     * through mcrypt using the randomized key. The end result
     * is a double-encrypted message string that is randomized
     * with each call to this function, even if the supplied
     * message and key are the same.
     *
     * @param    string    the string to encode
     * @param    string    the key
     * @return    string
     */
    public function encode($string, $key = '')
    {
        return base64_encode($this->mcrypt_encode($string, $this->getKey($key)));
    }

    public function decode($string, $key = '')
    {
        if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string) OR base64_encode(base64_decode($string)) !== $string) {
            return false;
        }

        return $this->mcryptDecode(base64_decode($string), $this->getKey($key));
    }

    /**
     * Encode from Legacy
     *
     * Takes an encoded string from the original Encryption class algorithms and
     * returns a newly encoded string using the improved method added in 2.0.0
     * This allows for backwards compatibility and a method to transition to the
     * new encryption algorithms.
     *
     * For more details, see https://codeigniter.com/user_guide/installation/upgrade_200.html#encryption
     *
     * @param    string
     * @param    int        (mcrypt mode constant)
     * @param    string
     * @return    string
     */
    public function encode_from_legacy($string, $legacy_mode = MCRYPT_MODE_ECB, $key = '')
    {
        if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string)) {
            return false;
        }

        // decode it first
        // set mode temporarily to what it was when string was encoded with the legacy
        // algorithm - typically MCRYPT_MODE_ECB
        $current_mode = $this->getMode();
        $this->setMode($legacy_mode);

        $key = $this->getKey($key);
        $dec = base64_decode($string);
        if (($dec = $this->mcryptDecode($dec, $key)) === false) {
            $this->setMode($current_mode);
            return false;
        }

        $dec = $this->xorDecode($dec, $key);

        // set the mcrypt mode back to what it should be, typically MCRYPT_MODE_CBC
        $this->setMode($current_mode);

        // and re-encode
        return base64_encode($this->mcrypt_encode($dec, $key));
    }

    protected function xorDecode($string, $key)
    {
        $string = $this->xorMerge($string, $key);

        $dec = '';
        for ($i = 0, $l = self::strlen($string); $i < $l; $i++) {
            $dec .= ($string[$i++] ^ $string[$i]);
        }

        return $dec;
    }

    protected function xorMerge($string, $key)
    {
        $hash = $this->hash($key);
        $str = '';

        for ($i = 0, $ls = self::strlen($string), $lh = self::strlen($hash); $i < $ls; $i++) {
            $str .= $string[$i] ^ $hash[($i % $lh)];
        }

        return $str;
    }

    public function mcrypt_encode($data, $key)
    {
        $initSize = mcrypt_get_iv_size($this->getCipher(), $this->getMode());
        $initVect = mcrypt_create_iv($initSize, MCRYPT_DEV_URANDOM);

        return $this->addCipherNoise($initVect . mcrypt_encrypt($this->getCipher(), $key, $data, $this->getMode(), $initVect), $key);
    }

    public function mcryptDecode($data, $key)
    {
        $data = $this->removeCipherNoise($data, $key);
        $init_size = mcrypt_get_iv_size($this->getCipher(), $this->getMode());

        if ($init_size > self::strlen($data)) {
            return false;
        }

        $init_vect = self::substr($data, 0, $init_size);
        $data = self::substr($data, $init_size);

        return rtrim(mcrypt_decrypt($this->getCipher(), $key, $data, $this->getMode(), $init_vect), "\0");
    }

    protected function addCipherNoise($data, $key)
    {
        $key = $this->hash($key);
        $str = '';

        for ($i = 0, $j = 0, $ld = self::strlen($data), $lk = self::strlen($key); $i < $ld; ++$i, ++$j) {
            if ($j >= $lk) {
                $j = 0;
            }

            $str .= chr((ord($data[$i]) + ord($key[$j])) % 256);
        }

        return $str;
    }

    protected function removeCipherNoise($data, $key)
    {
        $key = $this->hash($key);
        $str = '';

        for ($i = 0, $j = 0, $ld = self::strlen($data), $lk = self::strlen($key); $i < $ld; ++$i, ++$j) {
            if ($j >= $lk) {
                $j = 0;
            }

            $temp = ord($data[$i]) - ord($key[$j]);

            if ($temp < 0) {
                $temp += 256;
            }

            $str .= chr($temp);
        }

        return $str;
    }

    public function setCipher($cipher)
    {
        $this->mcryptCipher = $cipher;
        return $this;
    }

    public function setMode($mode)
    {
        $this->mcryptMode = $mode;
        return $this;
    }

    protected function getCipher()
    {
        if ($this->mcryptCipher === NULL) {
            return $this->mcryptCipher = MCRYPT_RIJNDAEL_256;
        }

        return $this->mcryptCipher;
    }

    protected function getMode()
    {
        if ($this->mcryptMode === NULL) {
            return $this->mcryptMode = MCRYPT_MODE_CBC;
        }

        return $this->mcryptMode;
    }

    public function setHash($type = 'sha1')
    {
        $this->hashType = in_array($type, hash_algos()) ? $type : 'sha1';
    }

    public function hash($str)
    {
        return hash($this->hashType, $str);
    }

    public function setEncryptionKey($encryptionKey)
    {
        $this->eencryptionKey = $encryptionKey;
    }

    protected static function strlen($str)
    {
        return defined('MB_OVERLOAD_STRING')
            ? mb_strlen($str, '8bit')
            : strlen($str);
    }

    protected static function substr($str, $start, $length = NULL)
    {
        if (defined('MB_OVERLOAD_STRING')) {
            // mb_substr($str, $start, null, '8bit') returns an empty
            // string on PHP 5.3
            isset($length) OR $length = ($start >= 0 ? self::strlen($str) - $start : -$start);
            return mb_substr($str, $start, $length, '8bit');
        }

        return isset($length)
            ? substr($str, $start, $length)
            : substr($str, $start);
    }
}
