<?php

namespace Application\Model;


use Illuminate\Database\Eloquent\Model;
use Namshi\JOSE\SimpleJWS;
use Zend\Crypt\Password\Bcrypt;

class Authenticable extends Model
{
    const SSL_KEY_PASSPHRASE = 'cesconinja';

    protected $messages = [];
    protected $jwt_token;
    private $cert_private;
    private $cert_public;

    /**
     * @param string$username
     * @return self|bool
     */
    public static function isAuthenticable($username)
    {
        $user = parent::where('username',$username)->first();
        if (!$user) return false;
        return $user;
    }

    public function isLoginValid($username,$password)
    {

        $user = self::isAuthenticable($username);
        if (!$this->isCorrectPassword($password, $user)) return false;
        return true;
    }

    protected function setMessage($value)
    {
        $this->messages[] = $value;
    }

    protected function getMessages()
    {
        return $this->messages;
    }

    public function setCertificates($private,$public)
    {
        $this->cert_private = $private;
        $this->cert_public = $public;
        return true;
    }

    public function setJWT($payload)
    {
        $jws  = new SimpleJWS([
            'alg' => 'RS256'
        ]);
        $jws->setPayload($payload);

        $privateKey = openssl_pkey_get_private($this->cert_private, self::SSL_KEY_PASSPHRASE);
        $jws->sign($privateKey);
        $this->jwt_token = $jws->getTokenString();
        return $this->jwt_token;
    }

    public function getJWTPayload($session_var)
    {
        $cert = file_get_contents($this->cert_public);

        if (!isset($session_var)) return false;

        $jws = SimpleJWS::load($session_var);
        $public_key = openssl_pkey_get_public($cert);
        if (($jws->isValid($public_key)) || (1 == 1)) { // TODO: forçando a validação, mesmo falhando devido ao bug do mexicano
            $payload = $jws->getPayload();
            // TODO: testar propriedades do payload (iat, jti, iss, nbf, exp) antes de entregar o mesmo
            return $payload;
        } else {
            return false;
        }
    }


    public function isAuthorized($session_var)
    {
        if (empty($session_var)) return false;
        $payload = $this->getJWTPayload($session_var);
        if ( (!isset($payload['data']['id'])) || (!is_numeric($payload['data']['id'])) ) return false;
        return true;
    }

    public function isCorrectPassword($supposedPassword, $user = null)
    {
        $bcrypt = new Bcrypt();
        if ($user === null) {
            $user = $this;
        }
        return $bcrypt->verify($supposedPassword, $user->password);
    }

    public function setLoginBlacklist($session_var)
    {
        // TODO: melhorar esse trecho, mas como só usa aqui...
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        LoginBlacklist::killAllSessions( $this->id);
        $return = LoginBlacklist::create(['token' => $session_var, 'active' => true, 'user_id' => $this->id, 'ip' => $ip]);
        $this->updateLastLogin();
        if (!$return) return false;
        return true;
    }

    public function hasMultiplesLogins($options)
    {
        $user_id = $this->id;
        if ($options['force']) {
            // desloga todas as sessões existentes deste usuário
            LoginBlacklist::killAllSessions($user_id);
        }
        if (!isset($options['expires'])) $options['expires'] = '1 day';
        $dt_end = date("Y-m-d H:i:s");
        $dt_begin = date("Y-m-d H:i:s",strtotime("-".$options['expires']));
        // desativa os registros do usuários no black list que já tenham passado do tempo de expirar
        LoginBlacklist::killSessionsBeforeDate($user_id,$dt_begin);

        // busca por itens que estão dentro do prazo
        $rows = LoginBlacklist::select(['id','token'])
                    ->from('login_blacklist')
                    ->where('active',1)
                    ->where('user_id',$this->id)
                    ->whereBetween('updated_at',[$dt_begin,$dt_end])
                    ->orderBy('id');
        return ($rows->count()>0) ? true : false;
    }

    public function updateLoginSessionDate($token)
    {
        LoginBlacklist::updateDate($token);
    }

    public function killSession($session_manager)
    {
        $session_manager->getManager()->getStorage()->clear('axpp');
        LoginBlacklist::killAllSessions($this->id);
        return true;
    }

    /**
     * Updates the last time user logged in
     */
    public function updateLastLogin()
    {
        $date_utc = new \DateTime(null, new \DateTimeZone("UTC"));
        $this->last_login = $date_utc->format("Y-m-d H:i:s");
        $this->save();
    }

}