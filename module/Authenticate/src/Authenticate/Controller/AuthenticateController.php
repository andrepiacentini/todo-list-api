<?php

namespace Authenticate\Controller;

use Application\Controller\ApplicationController;
use Application\Email\RecoverPassword;
use Application\Model\User;
use Namshi\JOSE\SimpleJWS;

class AuthenticateController extends ApplicationController
{
    protected $bypass_routes = array(
        '/v1/authenticate/authenticate',
        '/v1/authenticate/logout',
        '/v1/authenticate/createHash',
        '/v1/authenticate/recoveryByHash',
        '/v1/authenticate/checkHash',
        '/v1/authenticate/usernameExists'
    );

    public function authenticateAction()
    {
        $request = $this->getRequest();
        if (($return = $this->basicCheck('post')) !== NULL) return $return;
        // Params
        $post_data = get_object_vars(json_decode($request->getContent()));

        // validators
        $validatorUsername = new \Zend\I18n\Validator\Alnum();
        $validatorEmail = new \Zend\Validator\EmailAddress();
        $validatorPassword = new \Application\Validators\Password();

        if (!$validatorUsername->isValid($post_data["username"]) && !$validatorEmail->isValid($post_data["username"]))
            return $this->returnData(['status' => 401, 'data' => ['message' => 'username is not valid.']]);

        if (!$validatorPassword->isValid($post_data["password"]))
            return $this->returnData([
                'status' => 401,
                'data' => ['message'=>'password is not valid', 'validator' => $validatorPassword->getMessages()]
            ]);

        // Indica que irá ignorar se o usuário já está logado em outra session (destroi sessions ativas do usuário)
        $force = (isset($post_data["force"]) && ($post_data["force"]=='true')) ? true : false;

        // verifica se o username é de algum usuário
        $user = User::isAuthenticable($post_data["username"]);

        if (!$user)
            return $this->returnData(['status' => 401, 'data' => ['message' => 'username is not authenticable.']]);

        // Valida o login (username é valido)
        if (!$user->isLoginValid($post_data['username'], $post_data['password']))
            return $this->returnData(['status' => 401, 'data' => ['message' => 'username or password is not valid.']]);

        // checa blacklist
        $options = [
            'expires'   => '1 day',
            'force'     => $force,
        ];

        if ($user->hasMultiplesLogins($options))
            return $this->returnData(['status' => 401, 'data' => ['message' => 'multiples logins detected.']]);

        // Usuário está bloqueado (nivel banco)
        if (!$user->is_active)
            return $this->returnData(['status' => 401, 'data' => ['message' => 'username or password is not valid.']]);

        // seta o idioma do usuário
        $user->language = $post_data['lang']->code;

        // sucesso. Cria JWT
        // payload
        $tokenId    = base64_encode(openssl_random_pseudo_bytes(32));
        $issuedAt   = time();
        $notBefore  = $issuedAt + 10;             //Adding 10 seconds
        $expire     = $notBefore + 60;            // Adding 60 seconds
        $serverName = $this->config["security"]["server"]; // Retrieve the server name from config file

        $payload = [
            'iat'  => $issuedAt,         // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss'  => $serverName,       // Issuer
            'nbf'  => $notBefore,        // Not before
            'exp'  => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'id'   => $user->id, // userid from the users table
                'username' => $user->username, // User login
                'name' => $user->name,
                'phone' => $user->phone,
                'company' => isset($user->company->id) ? $user->company->id : null,
                'areas' => $user->areaPermissions()->select(['id', 'area'])->get()->toArray(),
                'lang' => $user->language
            ]
        ];

        $user->setCertificates($this->cert_private, $this->cert_public);
        $jwt_string = $user->setJWT($payload);
        $this->jwt = $jwt_string;
        // registra na blacklist
        $user->setLoginBlacklist($this->jwt);

        return $this->returnData(['status' => 200, 'data' => ['jwt_token' => $jwt_string]]);
    }

    public function logoutAction() {
        if (($return = $this->checkMethod('get')) !== NULL) return $return;

        $user = $this->getUserLogged();
        if ($user) {
            if (!$user->killSession($this->oContainer))
                return $this->returnData(['status' => 401, 'data' => ['message' => 'logout failed']]);
        }
        return $this->returnData(['status' => 200, 'data' => ['message' => 'logout']]);
    }

    public function getTokenContentAction() {
        if (($return = $this->checkMethod('get')) !== NULL) return $return;

        if (!isset($this->jwt))
            return $this->returnData(['status' => 401, 'data' => ['message' => 'unauthorized. Token not found.']]);

        $user = $this->getUserLogged();

        if (!$user instanceof User) return $this->returnData(['status' => 401, 'data'   => ['message' => 'unauthorized. The jwt token is not valid.']]);

        $userMoreData = User::find($this->getUserLogged()->id);
        $payloadData = $user->getJWTPayload($this->jwt)['data'];
        $payloadData['image']=$userMoreData->image;
        return $this->returnData([ 'status' => 200, 'data' => ['payload' => $payloadData ] ]);

    }

    /*
     * Recovery pass methods
     */

    public function createHashAction()
    {
        if (($return = $this->basicCheck('post')) !== NULL) return $return;

        $request = $this->getRequest();
        // Params
        $aData = get_object_vars(json_decode($request->getContent()));

        if (!isset($aData['host']) || empty($aData['host'])) {
            return $this->returnData(["status" => 400, "data" => "Invalid request"]);
        }

        $validatorEmail = new \Zend\Validator\EmailAddress();
        if (!isset($aData['email']) || !$validatorEmail->isValid($aData['email'])) {
            return $this->returnData(['status' => 400, 'data' => ['message' => 'Invalid email']]);
        }

        $user = User::where('username', $aData['email'])->first();
        if ($user === null) {
            return $this->returnData(['status' => 400, 'data' => ['message' => 'E-mail not found']]);
        }

        // Create recovery hash
        //$hash = Hash::make($email);
        $hash = md5(uniqid(rand(), true));
        $user->remember_token = $hash;
        $user->save();

        $mail = new RecoverPassword(array(
            'hash' => $hash,
            'host' => $aData['host'],
        ));

        $lang = $this->params()->fromQuery('lang');

        // Language
        switch ($lang) {
            case "en"    :
            case "en-us" :  $mail->setView('recover-password-en');
                            break;
            case "es"    :  $mail->setView('recover-password-es');
                            break;
            default      :  $mail->setView('recover-password');
                            break;
        }

        $mail->setServiceManager($this->oServiceManager);
        $mail->setTo(array(
            'email' => $user->username,
            'name' => $user->name
        ))->send();

        return $this->returnData(['status' => 200, 'data' => ['message' => 'Check your inbox']]);
    }

    public function recoveryByHashAction()
    {
        if (($return = $this->basicCheck('post')) !== NULL) return $return;

        $request = $this->getRequest();
        // Params
        $aData = get_object_vars(json_decode($request->getContent()));

        // Validation
        if (!isset($aData['hash']) || empty($aData['hash']))
            return $this->returnData(["status" => 400, "data" => "Invalid request"]);

        if ($this->checkEqualPassword($aData)) {
            // Get user by hash
            $user = User::where(['remember_token' => $aData['hash']])->first();

            if ((!is_object($user)) || ($user->id <= 0))
                return $this->returnData(["status" => 400, "data" => "Invalid request"]);

            $user->setPasswordAttribute($aData['password']);
            //sets null so same token cant be used twice
            $user->remember_token = null;
            $user->save();

            return $this->returnData(['status' => 200, 'data' => $user]);
        }

        return $this->returnData(["status" => 400, "data" => "Invalid request"]);
    }

    public function checkHashAction()
    {
        if (($return = $this->checkMethod('get')) !== NULL) return $return;

        $hash = $this->params()->fromQuery('hash');
//        var_dump($hash);exit;

        if (!$hash || empty($hash))
            return $this->returnData(["status" => 400, "data" => "Invalid request"]);

        // Get user by hash
        $user = User::where(['remember_token' => $hash])->first();

        // User exists?
        if ((!is_object($user)) || ($user->id <= 0))
            return $this->returnData(["status" => 400, "data" => "Invalid request"]);

        // Return OK
        return $this->returnData(['status' => 200, 'data' => 1]);
    }

    /**
     * Check if username is taken
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function usernameExistsAction()
    {
        if (($return = $this->checkMethod('get')) !== NULL) return $return;

        $username = $this->params()->fromQuery('un');

        if (User::where('username', '=', $username)->exists()) {
            return $this->returnData(['status' => 200, 'data' => 1]);
        } else {
            return $this->returnData(['status' => 200, 'data' => 0]);
        }
    }

}
