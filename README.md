# PHP-CSRF-Token
PHP CSRF Token (Class + Session)

# Configure Class
$session_var_name = 'csrf_token';<br>
$session_expire = 1800;<br>
$session_time_extend = 600;<br>
$session_secret = 'SecretSpecific2k21!';<br>

# Include to PHP page
include "csrfToken.class.php";

# Examples
$token = CsrfToken::getToken();<br>
CsrfToken::checkToken($token);
