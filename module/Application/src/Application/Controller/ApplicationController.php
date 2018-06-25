<?php
namespace Application\Controller;

use Application\Model\AreaPermission;
use Application\Model\LoginBlacklist;
use Application\Model\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\View\Console\ViewManager;
use Zend\Session\Container;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Db\TableGateway\TableGateway;
use Namshi\JOSE\SimpleJWS;
use Zend\Validator\Identical;
use Zend\Http\PhpEnvironment\Request as Request;


abstract class ApplicationController extends AbstractActionController
{

    protected $allowed_routes;
    protected $bypass_routes = [];
    protected $bypass_area_access = [];

    protected $config;
    protected $server;
    protected $oServiceManager;
    protected $oSessionManager;
    protected $oContainer;
    protected $oSecurity;
    protected $oRenderer;
    protected $translator;

    protected $cert_private = 'data/jwtRS256.key';
    protected $cert_public = 'data/jwtRS256.key.pub';

    protected $user_logged = false;
    protected $uri_route;


    public function __construct($serviceManager)
    {
        error_reporting(E_ALL & ~ E_DEPRECATED & ~ E_USER_DEPRECATED  & ~ E_STRICT);
        $this->cert_private = 'file://' . realpath($this->cert_private);
        $this->cert_public = 'file://' . realpath($this->cert_public);
        $this->oServiceManager = $serviceManager;
        $this->config = $this->oServiceManager->get("config");
        // Session in DB
        $tableGateway = new TableGateway('session', $this->oServiceManager->get('Zend\Db\Adapter\Adapter'));
        $saveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
        $this->oSessionManager = $this->oServiceManager->get('Zend\Session\SessionManager');
        $this->oSessionManager->setSaveHandler($saveHandler);
        $this->oContainer = new Container("skt", $this->oSessionManager);
        $this->oSecurity = $this->oServiceManager->get('Application\Security');
        $this->oRenderer = $this->oServiceManager->get('Zend\View\Renderer\RendererInterface');

        $this->bypass_routes = array_map('strtolower', $this->bypass_routes);
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $request = $this->getRequest();
        if (strtolower($request->getMethod()) == 'options') {
            return parent::onDispatch($e);
        }
        $this->uri_route = strtolower(explode('?', rtrim(str_replace(['/index'], [''], $_SERVER["REQUEST_URI"]), '/'), 2)[0]); // TODO: isso precisa ser melhorado

        // a uri precisa ser testada devido a questões de segurança?
        if (!in_array($this->uri_route,$this->bypass_routes)) {
            // header authorization está sendo passado?
            if  ( ($request->getHeaders('authorization')!==null) && ($request->getHeaders('authorization')!==false) ) {
                $value = $request->getHeaders('authorization')->getFieldValue();
                if (mb_strtolower(substr($value, 0, 6))!=='bearer') return $this->returnData(['status' => 406, 'data' => ['message' => 'authentication header must be bearer']]);
                $temp = explode(" ",$value);
                $this->oContainer->api_secret = $temp[1];
            }
            // check user authorizations
            if (!$this->authorize())
                return $this->returnData(['status' => 401, 'data' => ['message' => 'not authorized. Is session expired?']]);

            // check main controller
//            $returnedValue = $this->checkControllerChange($e);
//            if ($returnedValue!==false) {
//                return $returnedValue;
//            }

            // check modules authorizations
            $wic = $this->params('controller') . '\\' . $this->params('action'); // who is calling?
            // sanitize wic (remove ultimo parametro se for numero e remonta o router)
            $last_slash = strrpos($wic,"\\");
            if ($last_slash!==false) {
                $last_parameter = substr($wic,$last_slash+1);
                if (is_numeric($last_parameter)) {
                    $wic = substr($wic,0,$last_slash);
                    $routeMatch = $e->getRouteMatch();
                    $routeMatch->setParam("action","index");
                    $routeMatch->setParam("id",$last_parameter);
                    $e->setRouteMatch($routeMatch);
                }
            }
            if (!$this->hasAreaAccess($wic)) {
                return $this->returnData(['status' => 401, 'data' => ['message' => 'not authorized. You cannot access this system area. [TIP: add \''.$wic.'\' in AreaPermission Class, property $area_actions]']]);
            }
        }
        // update blacklist login session
        $this->updateLoginSession();
        return parent::onDispatch($e);
    }




    /* Função que instancia objetos comuns utilizados pelo objeto atual */
    protected function init()
    {
    }


    public function getContainer()
    {
        return $this->oContainer;
    }


    /* ======= Security check ===== */
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
        $this->user_logged = ($user->isAuthorized($this->oContainer->api_secret)) ? $user : null;
        if (!$this->user_logged) return false;
        // Testa se o token expirou
//print($this->oContainer->api_secret); exit;
        if (!LoginBlacklist::isJWTActive($this->oContainer->api_secret)) return false;
        $this->user_logged->fill($this->user_logged->getJWTPayload($this->oContainer->api_secret)['data']);
        return true;
    }

    protected function updateLoginSession() {
        if ($this->user_logged) {
            $this->user_logged->updateLoginSessionDate($this->oContainer->api_secret);
        }
    }


    /* troca o controller, caso module_name da company esteja preenchido */
    protected function checkControllerChange(\Zend\Mvc\MvcEvent $e) {
//        $nameSpaceCompany = $this->user_logged->company->module_name;
//        if (!is_null($nameSpaceCompany)) {
//            $routeMatch = $e->getRouteMatch();
//            $routeParams = $routeMatch->getParams();
//            $actualController = $routeParams["controller"];
//            // call
//            $controller = str_replace(['DAM'],[$nameSpaceCompany],$actualController);
//            $namespace = str_replace(['DAM'],[$nameSpaceCompany],$routeParams["__NAMESPACE__"]);
//            $action = $routeParams["action"];
//            if (strpos($routeParams["__NAMESPACE__"],$namespace)===false) {
//                return $this->forward()->dispatch($controller, array(
//                    'controller' => $controller,
//                    'action' => $action,
//                    '__NAMESPACE__' => $namespace,
//                    '__CONTROLLER__' => 'filter',
//                ));
//            }
//        }
        return false;
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
     * @return User
     */
    protected function getUserLogged() {
        // Se existir ao JWT token em cookie, reativa
        if (isset($this->oContainer->api_secret)) {
            $this->user_logged = new User();
            $this->user_logged->setCertificates($this->cert_private,$this->cert_public);
            $this->user_logged->fill($this->user_logged->getJWTPayload($this->oContainer->api_secret)['data']);
        }
        return $this->user_logged;
    }

    protected function isJson($str)
    {
        $json = json_decode($str);
        return $json && $str != $json;
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


    protected function returnData(array $data) {
        $this->response->getHeaders()->addHeaderLine( 'Content-Type', 'application/json' );
        $this->response->getHeaders()->addHeaderLine( 'Status', $data["status"] );
        $this->response->setStatusCode($data["status"]);
        //if ( !$this->params()->fromQuery('debug') ) {
        //    if (isset($data['data']['raw'])) unset($data['data']['raw']);
        //}

        $this->response->setContent(\Zend\Json\Json::encode(isset($data['data']) ? $data['data'] : ['fatal_error' => 'problems with the return format']));
        return $this->response;
    }


    protected function returnExcel(array $data, $filename = 'data') {
        $this->response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="'. $filename .'xlsx"',
            'Cache-Control' => 'max-age=0',
        ));
        $this->response->setStatusCode($data['status']);

        $this->response->setContent($data['excelOutput']);
        return $this->response;
    }

    protected function returnHtml(array $data) {
        $this->response->getHeaders()->addHeaderLine( 'Content-Type', 'text/html' );
        $this->response->getHeaders()->addHeaderLine( 'Status', $data["status"] );
        $this->response->setStatusCode($data["status"]);
        $this->response->setContent($data['data']);
        return $this->response;
    }

    protected function returnJavascript(array $data) {
        $this->response->getHeaders()->addHeaderLine( 'Content-Type', 'text/javascript' );
        $this->response->getHeaders()->addHeaderLine( 'Status', $data["status"] );
        $this->response->setStatusCode($data["status"]);
        $this->response->setContent($data['data']);
        return $this->response;
    }

    protected function returnPdf(array $data, $browserView = false) {
        return $this->returnFile($data, 'application/pdf', $browserView);

    }

    protected function returnImage(array $data) {
        return $this->returnFile($data, 'image/jpeg');
    }

    protected function returnZip(array $data) {
        return $this->returnFile($data, 'application/octet-stream');
    }

    private function returnFile(array $data, $contentType, $browserView = false) {
        $file = $data['file'];

        $contentDisposition = ($browserView ? 'inline' : 'attachment') . '; filename="' . basename($file) .'"';

        $this->response = new \Zend\Http\Response\Stream();
        $this->response->setStream(fopen($file, 'r'));
        // $this->response->setStatusCode($data['status']);
        $this->response->setStreamName(basename($file));
        $headers = new \Zend\Http\Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => $contentDisposition,
            'Content-Type' => $contentType,
        //     'Content-Length' => filesize($file),
        //     'Expires' => '@0', // @0, because zf2 parses date as string to \DateTime() object
        //     'Cache-Control' => 'must-revalidate',
        //     'Pragma' => 'public'
            'Content-Transfer-Encoding'=> 'binary',
        ));
        $this->response->setHeaders($headers);
        return $this->response;
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

    /**
     * Get model name based on the controller name
     * @return string
     */
    private function getModelName() {
        $controller = explode('\\', get_class($this))[2];
        $modelName = str_replace('Controller', '', $controller);

        return 'Application\\Model\\'.$modelName;
    }

    public function indexAction()
    {

        $request = $this->getRequest();
        $method = $request->getMethod();

        // escapa as chamadas de OPTIONS do browser
        if ($method === 'OPTIONS') {
            return $this->returnData(["status" => 200, "data" => ["message" => $request->getMethod()]]);
        }

        // testa o verbo chamado e direciona para o método
        if ($method === 'GET') return $this->get($request);
        if ($method === 'PUT') return $this->put($request);
        if ($method === 'DELETE') return $this->delete($request);
        if ($method === 'POST') return $this->post($request);

        return $this->returnData(["status" => 404, "data" => ["message" => 'Page not found']]);
    }

    public function get(Request $request) {

        $modelName = $this->getModelName();

        $request = $this->getRequest();
        // Params
        $id = $this->params()->fromQuery('id');
        if ($id && $id > 0) {
            $data = $modelName::find($id);
            if (!$data) {
                return $this->returnData(['status' => 200, 'data' => 'Regional inexistente']);
            }
        } else {
            $data = $modelName::all();
        }

        return $this->returnData(['status' => 200, 'data' => $data]);
    }

    public function delete(Request $request) {
        $modelName = $this->getModelName();
        $request = $this->getRequest();

        // Params
        $id = $this->params()->fromRoute('id');
        if (!$id || $id <= 0) return $this->returnData(["status" => 400, "message" => "Param id must be declared and must be integer"]);

        $deletedId = $modelName::destroy($id);

        return $this->returnData(['status' => 200, 'data' => $deletedId]);
    }

    public function put(Request $request) {
        $modelName = $this->getModelName();
        $request = $this->getRequest();

        $id = $this->params()->fromQuery('id');
        if (!$id || $id <= 0) return $this->returnData(["status" => 400, "message" => "Param id must be declared and must be integer"]);

        // Params
        $aData = get_object_vars(json_decode($request->getContent()));

        $regional = $modelName::find($id);
        $regional->fill($aData);
        $regional->save();


        return $this->returnData(['status' => 200, 'data' => $regional]);
    }

    public function post(Request $request) {
        $modelName = $this->getModelName();
        $request = $this->getRequest();

        // Params
        $aData = get_object_vars(json_decode($request->getContent()));
        $regional = $modelName::create($aData);

        return $this->returnData(['status' => 200, 'data' => $regional]);
    }

}
