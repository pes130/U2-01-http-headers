<?php
// La “bandera” se obtiene en este orden:
//  1) variable de entorno FLAG
//  2) contenido del fichero /run/flag.txt (si existe)
//  3) (opcional) valor por defecto nulo

function get_env_flag() {
    $envFlag = getenv('FLAG');
    if (!empty($envFlag)) return trim($envFlag);
    $file = '/run/flag.txt';
    if (is_readable($file)) {
        $val = trim(@file_get_contents($file));
        if ($val !== '') return $val;
    }
    return null;
}

function header_val($name) {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$key])) return $_SERVER[$key];
    if (function_exists('getallheaders')) {
        $h = getallheaders();
        if (isset($h[$name])) return $h[$name];
    }
    return null;
}

// Cabeceras útiles
$host         = $_SERVER['HTTP_HOST']           ?? '';
$acceptLang   = $_SERVER['HTTP_ACCEPT_LANGUAGE']?? '';
$userAgent    = $_SERVER['HTTP_USER_AGENT']     ?? '';
$xSecret      = header_val('X-Secret-Header')   ?? '';
$cookieFlag   = $_COOKIE['ctf_flag']            ?? '';
$flag         = get_env_flag(); // Se inyecta por Docker/Cloud-init

// Reglas 
$found = null;

if (stripos($host, 'ctf.local') !== false && stripos($acceptLang, 'es') !== false) {
    $found = "FLAG{ctf_host_lang_es}";
}
if (!$found && stripos($userAgent, 'MyCTFAgent/1.0') !== false && $xSecret === 'abracadabra') {
    $found = "FLAG{useragent_and_secret}";
}
if (!$found && $cookieFlag === 'open-sesame') {
    $found = "FLAG{cookie_success}";
}
if (!$found && stripos($host, 'admin.ctf.local') !== false && strpos($acceptLang, 'en') !== false && $xSecret === '42') {
    $found = "FLAG{admin_combo_42}";
}

// Si has inyectado un FLAG “oficial” por env/archivo, opcionalmente puedes revelarlo
// al cumplir una condición especial para el reto final:
if (!$found && $flag && stripos($userAgent, 'FinalAgent/2.0') !== false) {
    $found = $flag;
}

header('X-CTF-Hint: Modifica Host, Accept-Language, User-Agent, X-Secret-Header o Cookie ctf_flag');

if ($found) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "¡Enhorabuena! Flag: {$found}\n";
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>CTF HTTP — Cabeceras</h1>";
    echo "<p>Prueba a modificar cabeceras como <code>Host</code>, <code>Accept-Language</code>, <code>User-Agent</code>, <code>X-Secret-Header</code> o la cookie <code>ctf_flag</code>.</p>";
    echo "<ul>";
    echo "<li>curl -H 'Host: ctf.local' -H 'Accept-Language: es' http://localhost:8080/</li>";
    echo "<li>curl -H 'User-Agent: MyCTFAgent/1.0' -H 'X-Secret-Header: abracadabra' http://localhost:8080/</li>";
    echo "<li>curl --cookie 'ctf_flag=open-sesame' http://localhost:8080/</li>";
    echo "</ul>";
}
