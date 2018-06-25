<?php
namespace Application\Traits;

trait ReturnFormat {
    protected function returnData(array $data) {
        $this->response->getHeaders()->addHeaderLine( 'Content-Type', 'application/json' );
        $this->response->getHeaders()->addHeaderLine( 'Status', $data["status"] );
        $this->response->setStatusCode($data["status"]);
        $data = $this->injectDebugInfo($data);
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
        $data = $this->injectDebugInfo($data);
        $this->response->setContent($data['excelOutput']);
        return $this->response;
    }

    protected function returnHtml(array $data) {
        $this->response->getHeaders()->addHeaderLine( 'Content-Type', 'text/html' );
        $this->response->getHeaders()->addHeaderLine( 'Status', $data["status"] );
        $this->response->setStatusCode($data["status"]);
        $data = $this->injectDebugInfo($data);
        $this->response->setContent($data['data']);
        return $this->response;
    }

    protected function returnJavascript(array $data) {
        $this->response->getHeaders()->addHeaderLine( 'Content-Type', 'text/javascript' );
        $this->response->getHeaders()->addHeaderLine( 'Status', $data["status"] );
        $this->response->setStatusCode($data["status"]);
        $data = $this->injectDebugInfo($data);
        $this->response->setContent($data['data']);
        return $this->response;
    }

    protected function returnPdf(array $data, $browserView = false) {
        $data = $this->injectDebugInfo($data);
        return $this->returnFile($data, 'application/pdf', $browserView);

    }

    protected function returnImage(array $data) {
        $data = $this->injectDebugInfo($data);
        return $this->returnFile($data, 'image/jpeg');
    }

    protected function returnZip(array $data) {
        $data = $this->injectDebugInfo($data);
        return $this->returnFile($data, 'application/octet-stream');
    }

    protected function returnFile(array $data, $contentType, $browserView = false) {
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

    private function injectDebugInfo(array $data) {
        if ( !$this->params()->fromQuery('debug') ) {
            if (isset($data['data']['raw'])) unset($data['data']['raw']);
        }
    }

}