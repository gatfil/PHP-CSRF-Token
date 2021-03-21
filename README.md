# PHP-CSRF-Token
PHP CSRF Token (Class + Session)

# configure
$session_var_name = 'csrf_token';
$session_timeout = 1800;
$session_time_extend = 600;
$session_secret = 'SecretSpecific2k21!';

# include
include "csrfToken.class.php";

# examples
$token = CsrfToken::getToken();
CsrfToken::checkToken($token);