<?php
	namespace Application\Model;

    use Zend\Db\Sql\Sql;

	class Security  {
		private $dbAdapter;
		private $sql;

		public function __construct($oSM) {
			$this->dbAdapter = $oSM->get("Zend\Db\Adapter\Adapter");
			$this->sql = new Sql($this->dbAdapter);
		}
		

		
		/* Registra dados no log */
		public function registraLog($sModuloOrigem,$sDescricao) {
			// Teve dados de POST?
			$sStringPost = "";
			if (isset($_POST)) {
				if (count($_POST)>0) {
					$aKeys = array_keys($_POST);
					for ($i=0;$i<count($aKeys);$i++) {
						$sStringPost .= $aKeys[$i]."=".$_POST[$aKeys[$i]]."&";
					}
				}
			}

			$aColumns = [
    			'id'            => null,
    			'id_user'       => $this->umContainer->iIdUsuarioSiteLogado,
    			'source'        => $sModuloOrigem,
    			'description'   => $sDescricao,
    			'url'           => $_SERVER["REMOTE_ADDR"],
    			'register_date' => date("Y-m-d H:i:s"),
    			'ip'            => $_SERVER["REQUEST_URI"],
    			'post_data'     => $sStringPost
			];
			
			$oMapper = new SecurityMapper($this->oServiceManager);
//			$iIdRegistro = $this->oSecurityDB->insereDadosLog(,,,,,,);
			return $iIdRegistro;
		}
		
	
	
	
	
    	/**
    	 * returnHeaderCode function.
    	 * 
    	 * @access public
    	 * @param mixed $code
    	 * @return void
    	 */
    	public function returnHeaderCode($code) {
    	    $http_status = [
    	        100 => 'Continue',    
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Moved Temporarily',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Time-out',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Large',
                415 => 'Unsupported Media Type',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Time-out',
                505 => 'HTTP Version not supported',
            ];
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            
            return $protocol . ' ' . $code . ' ' . (aaray_key_exists($code,$http_status))?$http_status[$code]:'Unknown http status code "' . htmlentities($code) . '"';
    	}
		
	}
