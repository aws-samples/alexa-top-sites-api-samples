<?php
/**
 * Makes a request to ATS for the top 10 sites in a country
 */
class TopSites {

    protected static $ActionName        = 'Topsites';
    protected static $ResponseGroupName = 'Country';
    protected static $ServiceEndpoint   = 'ats.us-west-1.amazonaws.com';
    protected static $ServiceHost       = 'ats.amazonaws.com';
    protected static $NumReturn         = 10;
    protected static $StartNum          = 1;
    protected static $SigVersion        = '2';
    protected static $HashAlgorithm     = 'HmacSHA256';
    protected static $ServiceURI = "/api";
    protected static $ServiceRegion = "us-west-1";
    protected static $ServiceName = "AlexaTopSites";


    public function TopSites($accessKeyId, $secretAccessKey, $countryCode) {
        $this->accessKeyId = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->countryCode = $countryCode;
        $now = time();
        $this->amzDate = gmdate("Ymd\THis\Z", $now);
        $this->dateStamp = gmdate("Ymd", $now);

    }

    /**
     * Get site info from AWIS.
     */
    public function getTopSites() {
        $canonicalQuery = $this->buildQueryParams();
        $canonicalHeaders =  $this->buildHeaders(true);
        $signedHeaders = $this->buildHeaders(false);
        $payloadHash = hash('sha256', "");
        $canonicalRequest = "GET" . "\n" . self::$ServiceURI . "\n" . $canonicalQuery . "\n" . $canonicalHeaders . "\n" . $signedHeaders . "\n" . $payloadHash;
        $algorithm = "AWS4-HMAC-SHA256";
        $credentialScope = $this->dateStamp . "/" . self::$ServiceRegion . "/" . self::$ServiceName . "/" . "aws4_request";
        $stringToSign = $algorithm . "\n" .  $this->amzDate . "\n" .  $credentialScope . "\n" .  hash('sha256', $canonicalRequest);
        $signingKey = $this->getSignatureKey();
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $authorizationHeader = $algorithm . ' ' . 'Credential=' . $this->accessKeyId . '/' . $credentialScope . ', ' .  'SignedHeaders=' . $signedHeaders . ', ' . 'Signature=' . $signature;

        $url = 'https://' . self::$ServiceHost . self::$ServiceURI . '?' . $canonicalQuery;
        $ret = self::makeRequest($url, $authorizationHeader);
        self::parseResponse($ret);
    }

    protected function sign($key, $msg) {
        return hash_hmac('sha256', $msg, $key, true);
    }

    protected function getSignatureKey() {
        $kSecret = 'AWS4' . $this->secretAccessKey;
        $kDate = $this->sign($kSecret, $this->dateStamp);
        $kRegion = $this->sign($kDate, self::$ServiceRegion);
        $kService = $this->sign($kRegion, self::$ServiceName);
        $kSigning = $this->sign($kService, 'aws4_request');
        return $kSigning;
    }

    /**
     * Builds headers for the request to AWIS.
     * @return String headers for the request
     */
    protected function buildHeaders($list) {
        $params = array(
            'host'            => self::$ServiceEndpoint,
            'x-amz-date'      => $this->amzDate
        );
        ksort($params);
        $keyvalue = array();
        foreach($params as $k => $v) {
            if ($list)
              $keyvalue[] = $k . ':' . $v;
            else {
              $keyvalue[] = $k;
            }
        }
        return ($list) ? implode("\n",$keyvalue) . "\n" : implode(';',$keyvalue) ;
    }

    /**
     * Builds query parameters for the request to AWIS.
     * Parameter names will be in alphabetical order and
     * parameter values will be urlencoded per RFC 3986.
     * @return String query parameters for the request
     */
    protected function buildQueryParams() {
        $params = array(
          'Action'            => self::$ActionName,
          'ResponseGroup'     => self::$ResponseGroupName,
          'CountryCode'       => $this->countryCode,
          'Count'             => self::$NumReturn,
          'Start'             => self::$StartNum
        );
        ksort($params);
        $keyvalue = array();
        foreach($params as $k => $v) {
            $keyvalue[] = $k . '=' . rawurlencode($v);
        }
        return implode('&',$keyvalue);
    }

    /**
     * Makes request to TopSites
     * @param String $url   URL to make request to
     * @param String authorizationHeader  Authorization string
     * @return String       Result of request
     */
    protected function makeRequest($url, $authorizationHeader) {
        echo "\nMaking request to:\n$url\n";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Accept: application/xml',
          'Content-Type: application/xml',
          'X-Amz-Date: ' . $this->amzDate,
          'Authorization: ' . $authorizationHeader
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Parses the XML response from ATS and echoes the DataUrl element
     * for each returned site
     *
     * @param String $response    xml response from ATS
     */
    protected static function parseResponse($response) {
        echo "\nSites: \n";
        $xml = new SimpleXMLElement($response,null, false, 'aws', true);
        foreach($xml->Response->TopSitesResult->Alexa->TopSites->Country->Sites->children('aws', true) as $site) {
            echo $site->DataUrl . "\n";
        }
    }

}

if (count($argv) < 3) {
    echo "Usage: $argv[0] ACCESS_KEY_ID SECRET_ACCESS_KEY [COUNTRY_CODE]\n";
    exit(-1);
}
else {
    $accessKeyId = $argv[1];
    $secretAccessKey = $argv[2];
    $countryCode = count($argv) > 3 ? $argv[3] : "";
}

$topSites = new TopSites($accessKeyId, $secretAccessKey, $countryCode);
$topSites->getTopSites();

?>
