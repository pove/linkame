<?php

class Security {

    private $settings;

    // constructor
    function __construct($secsettings) {
        $this->settings = $secsettings;
    }

    function getToken($length)
    {
        $token = "";
        
        // Check for compatibility
        if (version_compare(phpversion(), '7', '<')) {
            $token = random_bytes_compat($length * 2);
        }
        else
        {
            $bytes = random_bytes($length);
            $token = strtoupper(bin2hex($bytes));
        }

        return $token;
    }

    function aKeyFromDevice($device)
    {
        return $device . substr($this->settings['akey'], 10);
    }

    function encryption($data, $device)
    {
        $iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
        $ciphertext = mcrypt_encrypt(
            MCRYPT_RIJNDAEL_128,
            $this->settings['ekey'],
            json_encode($data),
            'ctr',
            $iv
        );

        $akey = $this->aKeyFromDevice($device);
        
        $hmac = hash_hmac('sha256', $iv.$ciphertext, $akey, true);
        return base64_encode(
                $hmac.$iv.$ciphertext
            );
    }

    function decryption($data, $device)
    {
        $decoded = base64_decode($data);
        $hmac = mb_substr($decoded, 0, 32, '8bit');
        $iv = mb_substr($decoded, 32, 16, '8bit');
        $ciphertext = mb_substr($decoded, 48, null, '8bit');        
        
        $akey = $this->aKeyFromDevice($device);

        $calculated = hash_hmac('sha256', $iv.$ciphertext, $akey, true);
        
        if (hash_equals($hmac, $calculated)) {
            $decrypted = rtrim(
                mcrypt_decrypt(
                    MCRYPT_RIJNDAEL_128,
                    $this->settings['ekey'],
                    $ciphertext,
                    'ctr',
                    $iv
                ),
                "\0"
            );        
            return json_decode($decrypted, true);
        }
    }
}