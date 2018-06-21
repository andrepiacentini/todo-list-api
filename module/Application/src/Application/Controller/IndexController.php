<?php
namespace Application\Controller;

use Zend\Http\PhpEnvironment\Request as Request;


class IndexController extends ApplicationController {

    protected $bypass_routes = [ '/v1/index', '/v1/', '/v1' ];

    public function get(Request $request) {
        return $this->returnData(['status' => 200,
                                'data' => ['message' => 'Boiler Plate API v '. $this->config["version"] . ' by André Piacentini']]);
    }

}
