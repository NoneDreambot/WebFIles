<?php
include "function.php";

global $privateKey;
global $iv;

$token = $_GET["token"];

if (empty($token)) {
    return;
}

$binaryToken = '';
for ($i = 0; $i < strlen($token); $i += 2) {
    $binaryToken .= chr(hexdec(substr($token, $i, 2)));
}

$td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv); 
mcrypt_generic_init($td, $privateKey, $iv);
$decrypted = mdecrypt_generic($td, $binaryToken); 
mcrypt_generic_deinit($td);
mcrypt_module_close($td); 
$ut = utf8_encode(trim($decrypted));

$decryptedToken = substr($ut, 0);

$params = explode(',', $decryptedToken);

if ($params[0] != $iv) {
    return;
}

input($params[1], $params[2], $params[3], $params[4], $params[5], $params[6],$params[7]);

?>