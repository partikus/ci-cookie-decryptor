<?php

namespace App;

class SessionConfiguration
{
    public $sess_encrypt_cookie = false;
    public $sess_cookie_name = 'ci_session';
    public $cookie_prefix = '';
    public $cookie_secure = false;
    public $encryption_key = '';

    public static function fromArray(array $configuration)
    {
        $instance = new self;

        foreach ($configuration as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        return $instance;
    }
}
