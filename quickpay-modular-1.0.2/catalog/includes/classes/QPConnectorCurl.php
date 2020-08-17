<?php
/**
 * Quickpay v10+ php library
 *
 * This class implements the Quickpay connector interface, by using curl.
 * If curl is not present in the environment an execption is thrown on instantiation.
 */
class QPConnectorCurl implements QPConnectorInterface {
    protected $connTimeout = 10;
    protected $apiUrl = "https://api.quickpay.net";
    protected $apiVersion = 'v10';
    protected $apiKey = "";
    protected $format = "application/json";

    public function __constructor() {
        if (!function_exists('curl_init')){
            throw Exception('CURL is not installed, please install curl or change connection method');
        }
    }

    public function setOptions($apiKey, $connTimeout=10, $apiVersion="v10") {
       $this->connTimeout = $connTimeout;
       $this->apiKey = $apiKey;
       $this->apiVersion = $apiVersion;
    }

    public function request($resource, $postdata=null, $sendmode='GET-POST') {
        $curl =  curl_init();
        $url = $this->apiUrl . "/" . $resource;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->connTimeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode(":" . $this->apiKey),
            'Accept-Version: ' . $this->apiVersion,
            'Accept: ' . $this->format
        ));

        if (!is_null($postdata)) {
            if($sendmode=='GET-POST'){
                curl_setopt($curl, CURLOPT_POST, 1);
            }else{
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postdata));
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode!=200 && $httpCode!=202) {
        //  throw new Exception($response, $httpCode);
        }

        return $response;
    }
}
?>
