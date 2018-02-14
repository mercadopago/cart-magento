<?php

/**
 * MercadoPago cURL RestClient
 */
$GLOBALS["LIB_LOCATION"] = dirname(__FILE__);

class MercadoPago_Lib_RestClient {

    const API_BASE_URL = "https://api.mercadopago.com";

    private static function get_connect($uri, $method, $content_type, $extra_params = array()) {
        if (!extension_loaded ("curl")) {
            throw new Exception("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
        }

        $connect = curl_init(self::API_BASE_URL . $uri);

        curl_setopt($connect, CURLOPT_USERAGENT, "MercadoPago Magento-1.9.x-transparent Cart v1.0.2");
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($connect, CURLOPT_CAINFO, $GLOBALS["LIB_LOCATION"] . "/cacert.pem");

        $header_opt = array("Accept: application/json", "Content-Type: " . $content_type);
        if (count($extra_params) > 0) {
            $header_opt = array_merge($header_opt, $extra_params);
        }

        curl_setopt($connect, CURLOPT_HTTPHEADER, $header_opt);

        return $connect;
    }

    private static function set_data(&$connect, $data, $content_type) {
        if ($content_type == "application/json") {
            if (gettype($data) == "string") {
                json_decode($data, true);
            } else {
                $data = json_encode($data);
            }

            if(function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    throw new Exception("JSON Error [{$json_error}] - Data: {$data}");
                }
            }
        }

        curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
    }

    private static function exec($method, $uri, $data, $content_type, $extra_params) {
        $connect = self::get_connect($uri, $method, $content_type, $extra_params);
        if ($data) {
            self::set_data($connect, $data, $content_type);
        }

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($api_result === FALSE) {
            throw new Exception (curl_error ($connect));
        }

        $response = array(
            "status" => $api_http_code,
            "response" => json_decode($api_result, true)
        );

        /*if ($response['status'] >= 400) {
            $message = $response['response']['message'];
            if (isset ($response['response']['cause'])) {
                if (isset ($response['response']['cause']['code']) && isset ($response['response']['cause']['description'])) {
                    $message .= " - ".$response['response']['cause']['code'].': '.$response['response']['cause']['description'];
                } else if (is_array ($response['response']['cause'])) {
                    foreach ($response['response']['cause'] as $cause) {
                        $message .= " - ".$cause['code'].': '.$cause['description'];
                    }
                }
            }

            throw new Exception ($message, $response['status']);
        }*/

        if ($response != null && $response['status'] >= 400 && self::$check_loop == 0) {
			try {
				self::$check_loop = 1;
				$message = null;
				$payloads = null;
			 	$endpoint = null;
				$errors = array();
				if (isset($response['response'])) {
					if (isset($response['response']['message'])) {
						$message = $response['response']['message'];
					}
					if (isset($response['response']['cause'])) {
				 		if (isset($response['response']['cause']['code']) && isset($response['response']['cause']['description'])) {
				 			$message .= " - " . $response['response']['cause']['code'] . ': ' . $response['response']['cause']['description'];
				 		} else if (is_array($response['response']['cause'])) {
				 			foreach ($response['response']['cause'] as $cause) {
				 				$message .= " - " . $cause['code'] . ': ' . $cause['description'];
				 			}
				 		}
				 	}
				}
                //add data
                if (isset($data) && $data != null) {
                    $payloads = json_encode($data);
                }
                //add uri
                if (isset($uri) && $uri != null) {
                    $endpoint = $uri;
                }

				$errors[] = array(
					"endpoint" => $endpoint,
					"message" => $message,
					"payloads" => $payloads
				);
				self::sendErrorLog($response['status'], $errors);
		  	} catch (Exception $e) {
			   throw new MercadoPagoException("error to call API LOGS" . $e);
			}
		 }
		self::$check_loop = 0;

        curl_close($connect);

        return $response;
    }

    public static function get($uri, $content_type = "application/json", $extra_params = array()) {
        return self::exec("GET", $uri, null, $content_type, $extra_params);
    }

    public static function post($uri, $data, $content_type = "application/json", $extra_params = array()) {
        return self::exec("POST", $uri, $data, $content_type, $extra_params);
    }

    public static function put($uri, $data, $content_type = "application/json", $extra_params = array()) {
        return self::exec("PUT", $uri, $data, $content_type, $extra_params);
    }

    public static function delete($uri, $content_type = "application/json", $extra_params = array()) {
        return self::exec("DELETE", $uri, null, $content_type, $extra_params);
    }


    /**************
     * 
     * Error implementation tracking
     * 
    ***************/

    static $module_version = "";
    static $url_store = "";
    static $email_admin = "";
    static $country_initial = "";
    static $check_loop = 0;

    public static function setModuleVersion($module_version){
        self::$module_version = $module_version; 
    }

    public static function setUrlStore($url_store){
        self::$url_store = $url_store; 
    }

    public static function setEmailAdmin($email_admin){
        self::$email_admin = $email_admin; 
    }

    public static function setCountryInitial($country_initial){
        self::$country_initial = $country_initial; 
    }

    public static function sendErrorLog($code, $errors) {

        $server_version = php_uname();
        $php_version = phpversion();
        
        $data = array(
            "code" => $code,
            "errors" => $errors, 
            "module" => "Magento",
            "module_version" => self::$module_version,
            "url_store" => self::$url_store,
            "email_admin" => self::$email_admin,
            "country_initial" => self::$country_initial,
            "server_version" => $server_version,
            "code_lang" => "PHP " . $php_version
        );


        return self::post("/modules/log" , $data);
    }
}
