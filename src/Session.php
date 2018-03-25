<?php

namespace App;

class Session
{
    /**
     * @var Encrypt
     */
    private $encrypt;

    public function __construct(Encrypt $encrypt)
    {
        $this->encrypt = $encrypt;
    }

    public function decode(SessionConfiguration $configuration, $session)
    {
        if ($configuration->sess_encrypt_cookie == true) {
            $session = $this->encrypt->decode($session, $configuration->encryption_key);
        } else {
            // encryption was not used, so we need to check the md5 hash
            $hash = substr($session, strlen($session) - 32); // get last 32 chars
            $session = substr($session, 0, strlen($session) - 32);
            // Does the md5 hash match?  This is to prevent manipulation of session data in userspace
            if ($hash !== md5($session . $configuration->encryption_key)) {
                return false;
            }
        }

        // Unserialize the session array
        return $this->unserializeData($session);
    }

    public function encode(SessionConfiguration $configuration, $data)
    {
        $cookieData = $this->serialize($data);

        if ($configuration->sess_encrypt_cookie == true) {
            $cookieData = $this->encrypt->encode($cookieData, $configuration->encryption_key);
        } else {
            // if encryption is not used, we provide an md5 hash to prevent userside tampering
            $cookieData = $cookieData . md5($cookieData . $configuration->encryption_key);
        }

        return $cookieData;
    }

    private function serialize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    $data[$key] = str_replace('\\', '{{slash}}', $val);
                }
            }
        } else {
            if (is_string($data)) {
                $data = str_replace('\\', '{{slash}}', $data);
            }
        }

        return serialize($data);
    }

    private function stripSlashes($str)
    {
        if (!is_array($str)) {
            return stripslashes($str);
        }

        foreach ($str as $key => $val) {
            $str[$key] = strip_slashes($val);
        }

        return $str;
    }

    private function unserializeData($data)
    {
        $data = @unserialize($this->stripSlashes($data));

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    $data[$key] = str_replace('{{slash}}', '\\', $val);
                }
            }

            return $data;
        }

        return (is_string($data)) ? str_replace('{{slash}}', '\\', $data) : $data;
    }
}
