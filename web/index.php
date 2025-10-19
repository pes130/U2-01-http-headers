<?php
// --- Helpers ---
function get_env_flag() {
    $envFlag = getenv('FLAG');
    if (!empty($envFlag)) 
        return trim($envFlag);
}

function header_val($name) {
    // Intenta acceso por $_SERVER y por getallheaders()
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$key])) return $_SERVER[$key];
    if (function_exists('getallheaders')) {
        $h = getallheaders();
        // getallheaders puede devolver claves con diferente capitalización
        foreach ($h as $k => $v) {
            if (strcasecmp($k, $name) === 0) return $v;
        }
    }
    return null;
}

// --- Lectura de cabeceras / cookie / flag ---
$host       = $_SERVER['HTTP_HOST']              ?? '';
$acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE']   ?? '';
$userAgent  = $_SERVER['HTTP_USER_AGENT']        ?? '';
$xSecret    = header_val('X-Secret-Header')      ?? '';
$cookieFlag = $_COOKIE['ctf_flag']               ?? '';
$flag       = get_env_flag(); // null si no hay

// --- Comprobaciones requeridas ---
$host_found = ($host === 'iescelia.local');

$language_found = false;
if (!empty($acceptLang)) {
    // accept-language puede ser: "bn", "bn-BD, en;q=0.9", etc.
    // Buscamos 'bn' como substring (insensible a mayúsculas)
    $language_found = (stripos($acceptLang, 'bn') !== false);
}

$user_agent_found = false;
if (!empty($userAgent)) {
    // Aceptamos user-agents que contengan PSP o la cadena clásica de PSP
    // Ejemplo que tú pusiste: "Mozilla/4.0 (PSP (PlayStation Portable); 2.00)"
    if (stripos($userAgent, 'Mozilla/4.0 (PSP (PlayStation Portable') !== false) {
        $user_agent_found = true;
    }
}

// --- Lógica principal ---
$all_three = $host_found && $language_found && $user_agent_found;

// Si ya tienen la cookie de flag válida, mostramos el flag en texto plano (solo para comprobación local)
// Nota: si prefieres no mostrarlo nunca, comenta el bloque siguiente.
if (!empty($cookieFlag)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Tienes la cookie ctf_flag establecida. Valor (cookie): " . htmlspecialchars($cookieFlag, ENT_QUOTES|ENT_SUBSTITUTE) . "\n";
    exit;
}

// Si todas las condiciones base se cumplen, damos la pista por cabecera
if ($all_three) {
    header('X-HINT: Envía cabecera X-Secret-Header con el número de hijos legítimos de Julio Iglesias. Si aciertas, recibirás el flag en una cookie.');
    // comprobamos si han enviado X-Secret-Header
    if ($xSecret !== '') {
        $expected = '8';
        if (trim($xSecret) === $expected) {
            $to_set = $flag;
            // Establece cookie segura (HttpOnly). Ajusta 'secure' => true si usas HTTPS.
            setcookie('ctf_flag', $to_set, [
                'expires' => time() + 3600, // 1 hora
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            // Responder informando que se ha establecido la cookie (no revelar el flag en el body)
            header('Content-Type: text/plain; charset=utf-8');
            echo "Correcto. Cookie 'ctf_flag' establecida. Revisa las cookies del navegador o haz otra petición para comprobarla.\n";
            exit;
        } else {
            // X-Secret-Header erróneo
            header('Content-Type: text/plain; charset=utf-8', true, 403);
            echo "X-Secret-Header incorrecto. Vuelve a intentarlo.\n";
            exit;
        }
    } else {
        // Sólo devolvemos la pista en cabecera y una página ligera indicando que envíen la cabecera
        header('Content-Type: text/html; charset=utf-8');
        echo "<h1>CTF HTTP — paso 1 completado</h1>";
        echo "<p>Has proporcionado Host, Accept-Language (bn) y un User-Agent de PSP. Examina la cabecera de respuesta X-HINT para obtener la última pista</p>";
        echo "<p>Hint visual: Bengali + PSP + iescelia.local.</p>";
        exit;
    }
}

// Si no han cumplido las tres condiciones primarias
header('Content-Type: text/html; charset=utf-8');
echo "<h1>CTF HTTP — Cabeceras</h1>";
echo "<p>Sólo personas que hablen bengali y se conecten al host iescelia.local desde su Playstation Portable, estarán cerca de conocer la verdad</p>"; 
echo "<img src='img/Geographic_distribution_of_Bengali_language.png' width='600px'><br>"; 
echo "<img src='img/Sony-PSP-1000-Body.png' width='600px'>";

echo "<ul>";
echo "<li>Host: " . htmlspecialchars($host ?: '(no enviado)', ENT_QUOTES|ENT_SUBSTITUTE) . " " . ($host_found ? "<strong>OK</strong>" : "<em>faltante</em>") . "</li>";
echo "<li>Accept-Language: " . htmlspecialchars($acceptLang ?: '(no enviado)', ENT_QUOTES|ENT_SUBSTITUTE) . " " . ($language_found ? "<strong>OK</strong>" : "<em>faltante</em>") . "</li>";
echo "<li>User-Agent: " . htmlspecialchars($userAgent ?: '(no enviado)', ENT_QUOTES|ENT_SUBSTITUTE) . " " . ($user_agent_found ? "<strong>OK</strong>" : "<em>faltante</em>") . "</li>";
echo "</ul>";


