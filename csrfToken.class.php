<?php
/**
 * Gestione CSRF Token
 *
 * @copyright  2021 gatfil
 * @license    
 * @version    Release: 0.1
 * @link       
 * @since      Class available since Release 0.1
 */

/**
 * $param = [
 *    type => [otp, generic, specific],
 *    name => nome richiesta,
 *    timeout => seconds,
 *    extend => seconds,
 *    destroy => [true, false]
 * ];
 */

class CsrfToken{
   private static $session_var_name = 'csrf_token';
   private static $session_timeout = 1800;
   private static $session_time_extend = 600;
   private static $session_secret = 'SecretSpecific2k21!';

   function __construct($debug = false)
   {
      $fn = __FUNCTION__;
   }

   /**
   * Imposta valori di default (paramentri)
   * 
   * @param Array $param_ (parametri opzionali)
   * 
   * @return Array $param_ (parametri opzionali)
   */
   private static function setParams(&$param)
   {
      $fn = __FUNCTION__;

      $param = [
         'type' => $param['type'] ?? 'generic',
         'name' => $param['name'] ?? null,
         'timeout' => $param['timeout'] ?? self::$session_timeout,
         'extend' => $param['extend'] ?? self::$session_time_extend,
         'destroy' => $param['destroy'] ?? false,
      ];
      
      return $param;
   }

   /**
   * Restituisce il token se è ancora valido quello della sessione, altrimenti ne genera uno nuovo
   * 
   * @param Array $param (parametri opzionali)
   * 
   * @return Bool|String
   */
   public static function getToken($param = [])
   {
      $fn = __FUNCTION__;

      $token = false;

      self::clearToken();
      self::setParams($param);

      if( session_status() === PHP_SESSION_ACTIVE )
      {
         switch($param['type'])
         {
            case 'otp':
               $token_id_f = uniqid('', true);
               $token = self::generateToken();
               $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['token'] = $token;
               $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['expire'] = time() + $param['timeout'];
               break;
            case 'generic':
               $token_id_f = self::findToken(null, $param);
               if( $token_id_f !== false )
               {
                  $token = $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['token'];
                  $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['expire'] += $param['extend'];
               }
               else
               {
                  $token_id_f = uniqid('', true);
                  $token = self::generateToken();
                  $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['token'] = $token;
                  $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['expire'] = time() + $param['timeout'];
               }
               break;
            case 'specific':
               $token_id_f = self::findToken(null, $param);
               if( $token_id_f !== false )
               {
                  $token = $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['token'];
                  $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['expire'] += $param['extend'];
               }
               else
               {
                  $token_id_f = uniqid('', true);
                  $token = self::generateToken($param);
                  $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['token'] = $token;
                  $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['expire'] = time() + $param['timeout'];
                  $_SESSION[self::$session_var_name][$param['type']][$token_id_f]['name'] = $param['name'];
               }
               break;
         }
         
      }
      
      return $token;
   }

   /**
   * Restituisce l'input da inserire nella form
   * 
   * @return String
   */
   public static function getInputToken($param_ = [])
   {
      $fn = __FUNCTION__;

      $token = self::getToken($param_);
      if( $token === false )
      {
         $token = 'EXPIRED';
      }
      $return = '<input id="crsf-token" type="hidden" value="'.$token.'">';
      return $return;
   }

   /**
   * Restituisce la verifica del token passato con la variabile di sessione
   *
   * @param String $token
   *
   * @return Bool
   */
   public static function checkToken($token = null, $param = [])
   {
      $fn = __FUNCTION__;

      $is_valid = false;
      
      /*if( isset($token) && isset($_SESSION[self::$session_var_name]['token']) && $_SESSION[self::$session_var_name]['token'] === $token && time() < ($_SESSION[self::$session_var_name]['expire'] ?? time()) )
      {
         $is_valid = true;
      }*/

      self::clearToken();
      self::setParams($param);

      if( isset($token) )
      {
         $token_id = self::findToken($token, $param);

         if( $token_id !== false )
         {
            $is_valid = true;

            if( $param['type'] === 'otp' || $param['destroy'] === true )
            {
               self::unsetToken($token_id, $param);
            }
         }

      }

      return $is_valid;
   }

   /**
   * Genera un token
   * 
   * @param Array $param_ (parametri opzionali)
   * 
   * @return String
   */
   private static function generateToken($param_ = [])
   {
      $fn = __FUNCTION__;

      if( isset($param_['name']) )
      {
         $return = hash_hmac('sha256', $param_['name'], self::$session_secret);
      }
      else
      {
         $return = bin2hex(random_bytes(32));
      }
      
      return $return;
   }

   /**
   * Cerca il token
   * 
   * @param String $token_
   * @param Array $param_ (parametri opzionali)
   * 
   * @return Bool|Array
   */
   private static function findToken($token_ = null, $param_ = [])
   {
      $fn = __FUNCTION__;

      $find = false;

      //RICERCO il token in base al type
      if( isset($_SESSION[self::$session_var_name][$param_['type']]) )
      {
         foreach($_SESSION[self::$session_var_name][$param_['type']] as $token_id => $token_s)
         {
            if( isset($token_s['token']) && time() < ($token_s['expire'] ?? time()) )
            {
               //SE è specificato il token lo controllo
               if( isset($token_) && $token_ !== $token_s['token'] )
               {
                  continue;
               }

               //SE è specificato il nome lo controllo
               if( isset($param_['name']) && $token_s['name'] !== $param_['name'] )
               {
                  continue;
               }

               $find = $token_id;
               break;
            }
         }
      }

      return $find;
   }

   /**
   * Elimina il token
   * 
   * @param String $token_id_
   * @param Array $param (parametri opzionali)
   * 
   */
   private static function unsetToken($token_id_ = null, $param_ = [])
   {
      unset($_SESSION[self::$session_var_name][$param_['type']][$token_id_]);
   }

   /**
   * Pulisci token scaduti
   */
   private static function clearToken()
   {
      $fn = __FUNCTION__;

      if( isset($_SESSION[self::$session_var_name]) )
      {
         foreach($_SESSION[self::$session_var_name] as $type => $tokens)
         {
            foreach($tokens as $token_id => $token_s)
            {
               if( time() > ($token_s['expire'] ?? time()) )
               {
                  unset($_SESSION[self::$session_var_name][$type][$token_id]);
               }
            }
         }
         
      }
   }
}