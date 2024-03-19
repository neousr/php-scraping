#!/usr/bin/php
<?php

require_once 'Utils.php';
require_once 'Request.php';

error_reporting(E_ALL | E_STRICT);
mb_internal_encoding('UTF-8');

// Otra alternativa es utilizar la función php_sapi_name()
if (PHP_SAPI !== 'cli') {

    // https://www.php.net/manual/en/features.commandline.usage.php
    print('<p>PHP code to execute directly on the command line.</p>');

    exit;
}

$payload = [
    'action' => 'localidades',
    'localidad' => 'none',
    'calle' => '',
    'altura' => '',
    'provincia' => 'V',
];

$options = [
    CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Accept: application/json, text/javascript, */*; q=0.01',
        'X-Requested-With: XMLHttpRequest',
        'Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3'

    ],
    // true para devolver el resultado de la transferencia como string del valor de curl_exec() en lugar de mostrarlo directamente.
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    // Si pasamos un array a CURLOPT_POSTFIELDS codificará los datos como multipart/form-data, pero si pasamos una cadena URL-encoded codificará los datos como application/x-www-form-urlencoded.
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_TIMEOUT => 120,
    CURLOPT_ENCODING => ''
];

$request_url = 'https://www.correoargentino.com.ar/sites/all/modules/custom/ca_forms/api/wsFacade.php';

$r = Request::post($request_url, $options);

// si la opción CURLOPT_RETURNTRANSFER está establecida, devolverá el resultado si se realizó con éxito, o false si falló.
$r = preg_replace("/[^\x20-\x7EÁÉÍÓÚÜÑáéíóúüñ]/u", "", Utils::strip($r));

// Se devuelve null si el json no se puede decodificar o si los datos codificados son más profundos que el límite de anidamiento.
$rows = json_decode($r, true);

$fieldnames = array_keys($rows[0]);

$f = fopen(dirname(__FILE__) . '\tierra-del-fuego.tsv', 'w');

fputcsv($f, $fieldnames, "\t");

foreach ($rows as $key => $row) {

    fputcsv($f, $row, "\t");
}

fclose($f);
