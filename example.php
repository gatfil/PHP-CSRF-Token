<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "csrfToken.class.php";
echo "***EXAMPLES***<hr>";

session_start();

echo "<pre>PRE: ".print_r($_SESSION['csrf_token'], true)."</pre>";

#region otp 1 (scade dopo 60 sec quindi sarà valido)
$param = [
   'type' => 'otp',
   'name' => null,
   'expire' => 60,
   'extend' => 0,
];
$token = CsrfToken::getToken($param);

echo 'OTP 1: ';
var_dump(CsrfToken::checkToken($token, $param));
echo '<br>';
#endregion

#region otp 2 (scade prima del controllo, sleep di test)
$param = [
   'type' => 'otp',
   'name' => null,
   'expire' => 2,
   'extend' => 0,
];
$token = CsrfToken::getToken($param);

echo 'OTP 2: ';
sleep(3);
var_dump(CsrfToken::checkToken($token, $param));
echo '<br>';
#endregion

#region generic 1 (per pagine generiche o dove non si vuole forzare il controllo specifico )
$param = [
   'type' => null,
   'name' => null,
   'expire' => null,
   'extend' => null
];
$token = CsrfToken::getToken($param); //PARAM non obbligatorio

echo 'generic 1: ';
var_dump(CsrfToken::checkToken($token, $param)); //PARAM non obbligatorio
echo '<br>';
#endregion

#region generic 2 senza paramentri (per pagine generiche o dove non si vuole forzare il controllo specifico )
$token = CsrfToken::getToken();

echo 'generic 2: ';
var_dump(CsrfToken::checkToken($token));
echo '<br>';
#endregion

#region specific 1 (per pagine dove si vuole fare un controllo più specifico)
$param = [
   'type' => 'specific',
   'name' => 'form_login',
   'expire' => 600,
   'extend' => 0
];
$token = CsrfToken::getToken($param);

echo 'specific 1: ';
var_dump(CsrfToken::checkToken($token, $param));
echo '<br>';
#endregion

#region specific 2
$param = [
   'type' => 'specific',
   'name' => 'form_registrazione',
   'expire' => 600,
   'extend' => 0,
   'destroy' => true
];
$token = CsrfToken::getToken($param);

echo 'specific 2: ';
var_dump(CsrfToken::checkToken($token, $param));
echo '<br>';
echo 'specific 2: ';
var_dump(CsrfToken::checkToken($token, $param)); //non valido perchè unset dopo verifica precedente
echo '<br>';
#endregion

echo "<pre>POST: ".print_r($_SESSION['csrf_token'], true)."</pre>";

echo "<hr>***END EXAMPLES***";