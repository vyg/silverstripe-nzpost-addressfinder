<?php

class NZPostApiSession {

    const SESSION = 'nz_post_address';

	/**
	 * @var float
	 */
	private static $default_expiry = 0.03125;

    /**
     * @var string
     */
    private static $encryption_key = 'ea9f854a0055bd6fa0286013edfea71c4b2f3bcf48d6038ff992bff21bad7749';

    /**
     * encrypt the cookie data
     * Note it will json encode the data first.
     *
     * @param  object $data Data to encrypt
     * @return string encryoted data
     */
    public function encrypt($data)
    {
        $json = json_encode($data);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($json, 'aes-256-cbc', self::$encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * encrypt the cookie data
     * Note it will json encode the data first.
     *
     * @param  object $data Data to encrypt
     * @return string encryoted data
     */
    public function decrypt($encrypted)
    {
        list($encrypted, $iv) = explode('::', base64_decode($encrypted), 2);
        $json = openssl_decrypt($encrypted, 'aes-256-cbc', self::$encryption_key, 0, $iv);
        $data = json_decode($json, true);

        if($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $data = array();
        }

        return $data;
    }


    public function getCookie()
    {
        $encrypted = Cookie::get(NZPostApiSession::SESSION);
        if($encrypted) {
			$data = $this->decrypt($encrypted);
		} else {
			$data = array();
		}

        // var_dump($data);
        return $data;
    }

    public function setCookie($new, $expiry = null)
    {
        $old = $this->getCookie();
		// merge in the new data
		if(!$old || !is_array($old) || empty($old)) {
			$old = array();
		}

		$data = array_merge($old, $new);
        $encrypted = $this->encrypt($data);

        if (!$expiry) {
            $expiry = self::$default_expiry;
        }

        Cookie::set(NZPostApiSession::SESSION, $encrypted, $expiry);
    }

    /**
     * get a specific value from the cookie given a key
     *
     * @param  String $key
     * @return Mixed Value for the key
     */
    public function getValue($key)
    {
        $data = $this->getCookie();
        if(isset($data[$key])) {
            return $data[$key];
        }
        return null;
    }

    /**
     * set a value in the cookie given a key and value
     * @param string $key
     * @param mixed $value
     */
    public function setValue($key, $value)
    {
        $data = $this->getCookie();
        $data[$key] = $value;
        $this->setCookie($data);
    }

    /**
     * Get the bearer token from the cookie
     *
     * @return String bearer token
     */
    public function getToken()
    {
        return $this->getValue('token');
    }

    /**
     * Set the bearer token in the cookie
     *
     * @return String bearer token
     */
    public function setToken($token, $expiry=null)
    {
        if(!$expiry) {
            $expiry = self::$default_expiry;
        }

        // forcing an expiry will delete the token, and this could happen during
        // the user session, so need to get its data then reset it
        $this->setCookie(array('token' => $token), $expiry);
    }


}
