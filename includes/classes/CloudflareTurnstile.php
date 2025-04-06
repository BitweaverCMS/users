<?php

class CloudflareTurnstileValidator {
    private $secretKey;
    private $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct($secretKey) {
        $this->secretKey = $secretKey;
    }

    public function validate($responseToken, $remoteIp = null) {
        // Prepare POST data
        $data = [
            'secret' => $this->secretKey,
            'response' => $responseToken
        ];

        if ($remoteIp) {
            $data['remoteip'] = $remoteIp;
        }

        // Initialize cURL
        $ch = curl_init($this->verifyUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);

		$ret = array();

        // Decode JSON response
        $result = json_decode($response, true);

        if (!$result) {
            $ret = [
                'success' => false,
                'error_codes' => array( 'Invalid response from verification server ('.$httpCode.')' )
            ];
        } else {
			$ret = [
				'success' => $result['success'],
				'timestamp' => $result['challenge_ts'] ?? null,
				'hostname' => $result['hostname'] ?? null,
				'error_codes' => $result['error-codes'] ?? []
			];
	    }

		return $ret;
	}
}

// Example usage:
/*
$validator = new TurnstileValidator('YOUR_SECRET_KEY_HERE');
$result = $validator->validate($_POST['cf-turnstile-response'], $_SERVER['REMOTE_ADDR']);

if ($result['success']) {
    echo "Verification successful!";
} else {
    echo "Verification failed: " . implode(', ', $result['error_codes']);
}
*/
