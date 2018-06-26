<?php
namespace Application\Controller;

use Application\Model\AreaPermission;
use Application\Model\LoginBlacklist;
use Application\Model\User;
use Application\Traits\ObjectOperations;
use Application\Traits\ReturnFormat;
use Application\Traits\SecurityCheck;
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

    use SecurityCheck, ReturnFormat, ObjectOperations;

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
    protected $jwt;


    public function __construct($serviceManager)
    {
        $this->cert_private = 'file://' . realpath($this->cert_private);
        $this->cert_public = 'file://' . realpath($this->cert_public);
        $this->oServiceManager = $serviceManager;
        $this->config = $this->oServiceManager->get("config");
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
//                $this->oContainer->api_secret = $temp[1];
                $this->jwt = $temp[1];
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
