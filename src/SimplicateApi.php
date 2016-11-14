<?php

class SimplicateApi {

	/**
	 * @var
	 */
	public $authenticationKey;
	/**
	 * @var
	 */
	public $authenticationSecret;
	/**
	 * @var string
	 */
	public $apiUrl;

	/**
	 * SimplicateApi constructor.
	 *
	 * @param $domain
	 * @param $key
	 * @param $secret
	 */
	public function __construct($domain, $key, $secret){
		$this->authenticationKey = $key;
		$this->authenticationSecret = $secret;
		$this->apiUrl	= "https://{$domain}/api/v2";
	}

	/**
	 * @param $method
	 * @param $url
	 * @param null $payload
	 *
	 * @return array|mixed|object
	 */
	public function makeApiCall($method, $url, $payload = NULL) {
		$headers = [
			"User-Agent: SimplicateWordpress",
			"Authentication-Key: ".$this->authenticationKey,
			"Authentication-Secret: ".$this->authenticationSecret,
			"Accept: application/json",
		];

		$endpoint = $this->apiUrl . $url;

		$curl = curl_init($endpoint);

		switch(strtoupper($method)) {
			case "POST":
				$headers[] = "Content-Type: application/json";
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
				break;
			case "DELETE":
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			default:
				break;
		}

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($curl);

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if (self::isFailure($httpCode)) {
			return array('errorNumber' => $httpCode,
			             'error' => 'Request  returned HTTP error '.$httpCode,
			             'request_url' => $url);
		}

		$curl_errno = curl_errno($curl);
		$curl_err = curl_error($curl);

		if ($curl_errno) {
			$msg = $curl_errno.": ".$curl_err;
			curl_close($curl);
			return array('errorNumber' => $curl_errno,
			             'error' => $msg);
		}
		else {
			error_log("Response: ".$response);
			curl_close($curl);
			return json_decode($response, true);
		}
	}

	/**
	 * Tests the connection.
	 *
	 * @return array|mixed|object
	 */
	public function testConnection() {

		return $this->makeApiCall('GET','/base/test');

	}

	/**
	 * Create a Person.
	 *
	 * @param array $person
	 *
	 * @return array|mixed|object
	 */
	public function createPerson( $person = [] ) {

		$person = json_encode($person);
		return $this->makeApiCall('POST','/crm/person', $person);

	}

	/**
	 * Create an Organization.
	 *
	 * @param array $organization
	 *
	 * @return array|mixed|object
	 */
	public function createOrganization( $organization = [] ) {

		$organization = json_encode( $organization );
		return $this->makeApiCall('POST','/crm/organization', $organization);

	}

	/**
	 * @param array $sales
	 *
	 * @return array|mixed|object
	 */
	public function createSales( $sales = [] ) {

		$sales = json_encode( $sales );
		return $this->makeApiCall('POST', '/sales/sales', $sales);

	}

	/**
	 * Simplistic check for failure HTTP status
	 *
	 * @param $httpStatus
	 *
	 * @return bool
	 */
	public static function isFailure($httpStatus){
		return ($httpStatus >= 400);
	}
}