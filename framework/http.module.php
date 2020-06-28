<?php

class Http {

    public function __construct() {
        //empty one
    }

    static public function redirect($where, $stop = true)
    {
        if(defined("API_MODE")) return false;

        header('Location: ' . $where);
        if( $stop )
        {
            exit();
        }
    }

    static public function is_ajax()
    {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            return true;
        }

        return false;
    }

    static public function getHostUrl()
    {
        if( self::isHttps() )
        {
            $url = 'https://';
        } else
        {
            $url = 'http://';
        }

        return $url . $_SERVER['HTTP_HOST'];
    }

    static public function isHttps() {
      return
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
    }

    static public function request($what,$https = false) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $what);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if ($https) {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        }

        $responce = curl_exec($ch);
        curl_close($ch);
        return $responce;
    }

    static public function _curl($url, $post = "", $sock, $usecookie = false)
    {
        $ch = curl_init();
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if (!empty($sock)) {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_PROXY, $sock);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT,
            "Mozilla/6.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.7) Gecko/20050414 Firefox/1.0.3");
        if ($usecookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $usecookie);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $usecookie);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    static function protocol()
    {
        if( isset($_SERVER['HTTPS']) )
        {
            if( $_SERVER['HTTPS'] != '' )
            {
                return 'https://';
            }
        } else
        {
            return 'http://';
        }
    }

}