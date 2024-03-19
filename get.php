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

$options = [

    // https://www.php.net/manual/en/function.curl-setopt.php
    // CURLOPT_HTTPHEADER => [
    //     'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    //     'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
    //     'Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3',
    //     'Cookie: PHPSESSID=uvicfflq59paenkm3f3afroerv; TS01eda8db=016676ef671ed8d412b007389bdc125fb1b014f8d6b5ff08ab703764bcb32b1f6f4466ed0989e0b3656c8410436de32756f5e267c8f7063416b08a8aee842ee4bf69ae7a12; TS535cf0e7027=080b138208ab20003c5286b82586a6eb216920d3fdabb796255bc2fa5f76e041db9179a62f3040d808b1fe0b591130003c3d9740c11b55c937f616d3fcbd3a72471cb88202ba961296be7c2e4b7cb5e4dc142358201576d62f1634374da61d30; _ga_NJ4BGH6X9G=GS1.1.1710678453.1.1.1710678558.51.0.0; _ga=GA1.1.614415861.1710678454'
    // ],
    CURLOPT_COOKIE => 'Cookie: PHPSESSID=uvicfflq59paenkm3f3afroerv; TS01eda8db=016676ef671ed8d412b007389bdc125fb1b014f8d6b5ff08ab703764bcb32b1f6f4466ed0989e0b3656c8410436de32756f5e267c8f7063416b08a8aee842ee4bf69ae7a12; TS535cf0e7027=080b138208ab20003c5286b82586a6eb216920d3fdabb796255bc2fa5f76e041db9179a62f3040d808b1fe0b591130003c3d9740c11b55c937f616d3fcbd3a72471cb88202ba961296be7c2e4b7cb5e4dc142358201576d62f1634374da61d30; _ga_NJ4BGH6X9G=GS1.1.1710678453.1.1.1710678558.51.0.0; _ga=GA1.1.614415861.1710678454',
    CURLOPT_USERAGENT => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    // true para devolver el resultado de la transferencia como string del valor de curl_exec() en lugar de mostrarlo directamente.
    CURLOPT_RETURNTRANSFER => true,
    // CURLOPT_POST => true,
    // Si pasamos un array a CURLOPT_POSTFIELDS codificará los datos como multipart/form-data, pero si pasamos una cadena URL-encoded codificará los datos como application/x-www-form-urlencoded.
    // CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_TIMEOUT => 120,
    CURLOPT_ENCODING => ''
];

$scheme = "https";
$host = "www.senado.gob.ar";
$filename = "/senadores/listados/listaSenadoRes";

$request_url = $scheme . "://" . $host . $filename;

$r = Request::get($request_url, $options);

preg_match("/<table[^>]*>.*?<\/table>/", Utils::strip($r), $matches);

$table = $matches[0];

unset($matches);

preg_match("/<thead[^>]*>.*<\/thead>/", $table, $matches);

$thead = $matches[0];

unset($matches);

preg_match_all("/<th\b[^>]*>(.*?)<\/th>/", $thead, $matches);

$fieldnames = $matches[1];

unset($matches);

preg_match("/<tbody[^>]*>.*<\/tbody>/", $table, $matches);

$tbody = $matches[0];

unset($matches);

preg_match_all("/<tr[^>]*>(.*?)<\/tr>/", $tbody, $matches);

$table_rows = $matches[1];

unset($matches);

$f = fopen(dirname(__FILE__) . '\senadores.tsv', 'w');

fputcsv($f, ['Foto', 'Apellidos', 'Nombres', 'Distrito', 'Partido', 'Periodo inicio', 'Periodo fin', 'Teléfono', 'Interno telefónico', 'E-mail', 'Social media'], "\t");

foreach ($table_rows as $key => $row) {

    preg_match_all('/<td[^>]*>(.*?)<\/td>/', $row, $matches);

    $table_columns = $matches[1];

    preg_match('/"([^"]+\.gif)"/', $table_columns[0], $matches);

    $path_to_image = $scheme . "://" . $host . $matches[1];

    unset($matches);

    preg_match("/<[\w]+[^>]*>(.*?)<\/[\w]+>/", $table_columns[1], $matches);

    $name = Utils::strip($matches[1]);

    if (!str_contains($name, ',')) {

        $lastname = substr($name, 0, Utils::strsecpos($name, " "));

        $firstname = substr($name, Utils::strsecpos($name, " ") + 1);

    } else {

        list($lastname, $firstname) = explode(", ", $name);
    }

    $district = $table_columns[2];
    
    $party = $table_columns[3];

    preg_match_all("/\d{1,2}\/\d{1,2}\/\d{2,4}/", $table_columns[4], $matches);

    list($start_period, $end_period) = $matches[0];

    preg_match_all("/<li[^>]*>(.*?)<\/li>/", $table_columns[5], $matches);

    $contacts = $matches[1];

    $phone_number = $contacts[1];

    $intern = substr(Utils::strip($contacts[2]), 9);

    preg_match("/<a[^>]*>(.*?)<\/a>/", $contacts[0], $matches);

    $email = $matches[1];

    preg_match_all('/href="([^"]+)"/', $contacts[3], $matches);

    $social_networks = "";

    if (count($matches[1]) > 0) {

        $social_networks = implode(", ", $matches[1]);
    }

    fputcsv($f, [$path_to_image, $lastname, $firstname, $district, $party, $start_period, $end_period, $phone_number, $intern, $email, $social_networks], "\t");
}

fclose($f);
