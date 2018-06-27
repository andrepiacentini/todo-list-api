<?php
namespace Application\Traits;

use Application\Model\AreaPermission;
use Application\Model\LoginBlacklist;
use Application\Model\User;
use Zend\Validator\Identical;

trait SecurityCheck {

    protected function authorize() {
        if (!in_array($this->uri_route,$this->bypass_routes)) return $this->isUserAccessAuthorized();
        return true;
    }

    protected function hasAreaAccess($wic) {
        if (!$this->user_logged) return false;
        if (in_array($wic,$this->bypass_area_access)) return true;
        $area_permissions = new AreaPermission();
        return $area_permissions->hasAccess($this->user_logged->id,$wic);
    }

    protected function isUserAccessAuthorized() {
        $user = new User();
        $user->setCertificates($this->cert_private,$this->cert_public);
        $this->user_logged = ($user->isAuthorized($this->jwt)) ? $user : null;
        if (!$this->user_logged) return false;
        // Testa se o token expirou
        if (!LoginBlacklist::isJWTActive($this->jwt)) return false;
        $this->user_logged->fill($this->user_logged->getJWTPayload($this->jwt)['data']);
        return true;
    }

    protected function updateLoginSession() {
        if ($this->user_logged) {
            $this->user_logged->updateLoginSessionDate($this->jwt);
        }
    }


    protected function checkMethod($method) {
        $request = $this->getRequest();

        if (strtolower($request->getMethod()) == 'options') {
            return $this->returnData(["status" => 200, "data" => ["message" => $method]]);
        }

        $checkMethodFunction = 'is'. ucfirst($method);
        if (method_exists($request, $checkMethodFunction) && !$request->$checkMethodFunction()) {
            return $this->returnData(["status" => 406, "data" => ["message" => "method not allowed - only $method is acceptable"]]);
        }
    }

    protected function basicCheck($method) {
        $return = $this->checkMethod($method);
        if (!$return) {
            $return = $this->checkContent();
        }
        return $return;
    }

    protected function checkContent() {
        if (empty($this->getRequest()->getContent())) return $this->returnData(["status" => 400, "data" => ["message" => "missing object json"]]);
    }


    /**
     * Checks if password and password confirmation are equal
     * @param $data
     * @param bool $unset
     * @return bool
     */
    protected function checkEqualPassword(&$data) {
        if (isset($data['set_password'])) {
            if (!isset($data['password']) || !isset($data['confirm_password'])) {
                return false;
            }
            $validator = new Identical($data['password']);
            return $validator->isValid($data['confirm_password']);
        }
        unset($data['password']);

        return true;
    }

    protected function validBase64($string)
    {
        $decoded = base64_decode($string, true);

        // Check if there is no invalid character in string
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) return false;

        // Decode the string in strict mode and send the response
        if (!base64_decode($string, true)) return false;

        // Encode and compare it to original one
        if (base64_encode($decoded) != $string) return false;

        return true;
    }

}