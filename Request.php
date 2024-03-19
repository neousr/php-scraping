<?php

/**
 * Permite enviar solicitudes HTTP
 */
final class Request
{

    private function __construct()
    {
    }

    public static function get($request_url, $options = null)
    {
        $request_url = self::checkUrl($request_url);

        $curl_handle = curl_init($request_url);

        curl_setopt_array($curl_handle, $options);

        $response = curl_exec($curl_handle);

        curl_close($curl_handle);

        return $response;
    }

    public static function post($request_url, $options = null)
    {
        $request_url = self::checkUrl($request_url);

        $curl_handle = curl_init($request_url);

        curl_setopt_array($curl_handle, $options);

        $response = curl_exec($curl_handle);

        curl_close($curl_handle);

        return $response;
    }

    private static function checkUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            // if (!preg_match('/^[/a-z0-9-]+$/i', $url)) {
            // TODO log attempt, redirect attacker, ...
            throw new Exception('Unsafe page "' . $url . '" requested');
        }

        return $url;
    }
}
