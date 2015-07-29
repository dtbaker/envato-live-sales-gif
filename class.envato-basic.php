<?php


/**
 * Exception handling class.
 */
class EnvatoException extends Exception {}


class envato_api_basic{

    private static $instance = null;
    public static function getInstance () {
        if (is_null(self::$instance)) { self::$instance = new self(); }
        return self::$instance;
    }

    private $_api_url = 'https://api.envato.com/';

    private $_client_id = false;
    private $_client_secret = false;
    private $_personal_token = false;
    private $_redirect_url = false;
    private $_cookie = false;
    private $token = false; // token returned from oauth

    public function set_client_id($token){
        $this->_client_id = $token;
    }
    public function set_client_secret($token){
        $this->_client_secret = $token;
    }
    public function set_personal_token($token){
        $this->_personal_token = $token;
    }
    public function set_redirect_url($token){
        $this->_redirect_url = $token;
    }
    public function set_cookie($cookie){
        $this->_cookie = $cookie;
    }
    public function api($endpoint, $params=array(), $personal = true){
        $ch = curl_init($this->_api_url . $endpoint);
        if($personal && !empty($this->_personal_token)){
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: ' . 'Bearer ' . $this->_personal_token));
        }else if(!empty($this->token['access_token'])){
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: ' . 'Bearer ' . $this->token['access_token']));
        }
        curl_setopt($ch,CURLOPT_USERAGENT,'dtbaker Envato Item Sales');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $response = curl_exec($ch);
        return @json_decode($response,true);
    }



    private $ch = false;
    private $cookies = array();
    private $cookie_file = false;
    public function curl_init($cookies = true) {
        if ( ! function_exists( 'curl_init' ) ) {
            echo 'Please contact hosting provider and enable CURL for PHP';

            return false;
        }
        $this->ch = curl_init();
        curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
        @curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $this->ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $this->ch, CURLOPT_TIMEOUT, 20 );
        curl_setopt( $this->ch, CURLOPT_HEADER, false );
        curl_setopt( $this->ch, CURLOPT_USERAGENT, "Support Hub dtbaker" );
        if ( $cookies ) {
            if ( ! $this->cookie_file ) {
                $this->cookie_file = tempnam( sys_get_temp_dir(), 'SupportHub' );
            }
            curl_setopt( $this->ch, CURLOPT_COOKIEJAR, $this->cookie_file );
            curl_setopt( $this->ch, CURLOPT_COOKIEFILE, $this->cookie_file );
            curl_setopt( $this->ch, CURLOPT_HEADERFUNCTION, array( $this, "curl_header_callback" ) );
        }
    }
    public function curl_done(){
        @unlink($this->cookie_file);
    }
    public function get_url($url, $post = false, $extra_headers = array(), $cookies = true) {

        if($this->ch){
            curl_close($this->ch);
        }
        $this->curl_init($cookies);

        if($cookies) {
            $cookies                        = '';
            $this->cookies['envatosession'] = $this->_cookie;
            foreach ( $this->cookies as $key => $val ) {
                if ( ! strpos( $url, 'account.envato' ) && $key == 'envatosession' ) {
                    continue;
                }
                $cookies = $cookies . $key . '=' . $val . '; ';
            }
            curl_setopt( $this->ch, CURLOPT_COOKIE, $cookies );
        }

        curl_setopt( $this->ch, CURLOPT_URL, $url );
        if($extra_headers){
            curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $extra_headers);
        }

        if ( is_string( $post ) && strlen( $post ) ) {
            curl_setopt( $this->ch, CURLOPT_POST, true );
            curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $post );
        }else if ( is_array( $post ) && count( $post ) ) {
            curl_setopt( $this->ch, CURLOPT_POST, true );
            curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $post );
        } else {
            curl_setopt( $this->ch, CURLOPT_POST, 0 );
        }
        // safe mode redirection hack
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {

        }else{
            $mr = 6;
            $newurl = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
            $rch = curl_copy_handle($this->ch);
            curl_setopt($rch, CURLOPT_HEADER, true);
            curl_setopt($rch, CURLOPT_NOBODY, true);
            curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
            curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
            do {
                curl_setopt($rch, CURLOPT_URL, $newurl);
                $header = curl_exec($rch);
                if (curl_errno($rch)) {
                    $code = 0;
                } else {
                    $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                    if ($code == 301 || $code == 302) {
                        preg_match('/Location:(.*?)\n/', $header, $matches);
                        $newurl = trim(array_pop($matches));
                    } else {
                        $code = 0;
                    }
                }
            } while ($code && --$mr);
            curl_close($rch);
            if (!$mr) {
                return false;
            }
            curl_setopt($this->ch, CURLOPT_URL, $newurl);

        }
        return curl_exec( $this->ch );
    }

    public function curl_header_callback($ch, $headerLine) {
        //echo $headerLine."\n";
        if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1){
            $bits = explode('=',$cookie[1]);
            $this->cookies[$bits[0]] = $bits[1];
        }
        return strlen($headerLine); // Needed by curl
    }

    /**
     * OAUTH STUFF
     */

    public function get_authorization_url() {
        return 'https://api.envato.com/authorization?response_type=code&client_id='.$this->_client_id."&redirect_uri=".urlencode($this->_redirect_url);
    }
    public function get_token_url() {
        return 'https://api.envato.com/token';
    }
    public function get_authentication($code) {
        $url = $this->get_token_url();
        $parameters = array();
        $parameters['grant_type']    = "authorization_code";
        $parameters['code']          = $code;
        $parameters['redirect_uri']  = $this->_redirect_url;
        $parameters['client_id']     = $this->_client_id;
        $parameters['client_secret'] = $this->_client_secret;
        $fields_string = '';
        foreach ( $parameters as $key => $value ) {
            $fields_string .= $key . '=' . urlencode($value) . '&';
        }
        try {
            $response = $this->get_url($url, $fields_string, false, false);
        } catch ( EnvatoException $e ) {
            return false;
        }
        $this->token = json_decode( $response, true );
        return $this->token;
    }
    protected function refresh_token(){
        $url = $this->get_token_url();

        $parameters = array();
        $parameters['grant_type'] = "refresh_token";

        $parameters['refresh_token']  = $this->token['refresh_token'];
        $parameters['redirect_uri']   = $this->_redirect_url;
        $parameters['client_id']      = $this->_client_id;
        $parameters['client_secret']  = $this->_client_secret;

        $fields_string = '';
        foreach ( $parameters as $key => $value ) {
            $fields_string .= $key . '=' . urlencode($value) . '&';
        }
        try {
            $response = $this->get_url($url, $fields_string, false, false);
        }
        catch (EnvatoException $e) {
            return false;
        }
        $new_token = json_decode($response, true);
        $this->token['access_token'] = $new_token['access_token'];
        return $this->token['access_token'];
    }



}