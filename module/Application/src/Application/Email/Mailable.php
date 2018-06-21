<?php
namespace Application\Email;

//use Application\Model\Log;
//use Application\Model\ServiceManager;
//use Zend\Db\Adapter\Adapter;
//use Zend\Db\Sql\Sql;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Message;
use Zend\Mime\Part;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;

abstract class Mailable implements ServiceLocatorAwareInterface {
    use ServiceLocatorAwareTrait;
    protected $message;
    protected $mail;
    protected $options;
    protected $view;
    protected $data;
    protected $sm;

    const LOG_TABLE = 'log_email';

    protected static $fromDefault = [
        'name' => 'Do not reply',
        'email' => 'no-reply@andrepiacentini.com.br'
    ];

    public function __construct($data = [],$to = null,$subject = '')
    {
        $this->setData($data);
        $this->buildmessage();

        if(!is_null($to)){
            $this->setTo($to);
        }

        if(!empty($subject)){
            $this->setSubject($subject);
        }

        $this->boot();
    }

    public function setView($file) {
        $this->view = $file;
    }


    public function boot()
    {
        //Write any code you want to execute
    }

    protected static function pathToMails()
    {
        return __DIR__ . '/../../../view/email';
    }

    protected function getViewPath()
    {
        $viewName = strpos($this->view,".") === false ? $this->view.".phtml" : $this->view;

        return static::pathToMails()."/$viewName";
    }

    protected function compileView()
    {
        $renderer = new PhpRenderer();

        $resolver = new TemplateMapResolver();
        $resolver->setMap(array(
            'mail' => $this->getViewPath()
        ));
        $renderer->setResolver($resolver);

        $contentModel = new ViewModel();
        $contentModel->setTemplate('mail')->setVariables($this->data);

        return $renderer->render($contentModel);
    }

    protected function viewToMailBody()
    {
        $part = new Part($this->compileView());
        $part->type = 'text/html';

        $body = new \Zend\Mime\Message();
        $body->addPart($part);

        return $body;
    }

    protected function buildMessage()
    {
        $this->message = new Message();
        $this->message->setBody($this->viewToMailBody());
    }

    protected function buildOptions()
    {
        $config = $this->sm->get('config')["smtp"];

        $this->options = new SmtpOptions([
            "name" => $config["name"],
            "host" => $config["host"],
            "port" => $config["port"],
            "connection_class" => $config["class"],
            "connection_config" => [
                "username" => $config["username"],
                "password" => $config["password"],
                "ssl" => $config["ssl"]
            ]
        ]);

        return $this->options;
    }

    public function getFrom()
    {
        return $this->message->getFrom();
    }

    public function setFrom($value)
    {
        if(!is_array($value)){
            $from['email'] = $value;
            $from['name'] = '';
        }else{
            $from = $value;
        }

        $this->message->setFrom(trim($from['email']), $from['name']);

        return $this; //Make it CHAIN
    }

    public function getReplyTo()
    {
        return $this->message->getReplyTo();
    }

    public function setReplyTo($value)
    {
        if(!is_array($value)){
            $replyTo['email'] = $value;
            $replyTo['name'] = '';
        }else{
            $replyTo = $value;
        }

        $this->message->addReplyTo(trim($replyTo['email']),$replyTo['name']);

        return $this; //Make it CHAIN
    }

    public function getTo()
    {
        return $this->message->getTo();
    }

    public function setTo($value)
    {
        return $this->addTo($value); //Escolhi mal o nome da função
    }

    public function addTo($value)
    {
        if(!is_array($value)){
            $to['email'] = $value;
            $to['name'] = '';
        }else{
            $to = $value;
        }

        $this->message->addTo($to['email'], $to['name']);

        return $this; //Make it CHAIN
    }

    public function getSubject()
    {
        return $this->message->getSubject();
    }

    public function setSubject($subject){
        $this->message->setSubject($subject);

        return $this; //Make it CHAIN
    }

    public function getOptions()
    {
        return !is_null($this->options) ? $this->options : $this->buildOptions();
    }

    public function setOptions(SmtpOptions $options)
    {
        $this->options = $options;

        return $this; //Make it CHAIN
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage(Message $message)
    {
        $this->message = $message;

        return $this;
    }

    public function getData($key = null)
    {
        if(!is_null($key)){
            return isset($this->data[$key]) ? $this->data[$key] : null;
        }else{
            return $this->data;
        }
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getContent()
    {
        return $this->message->getBody()->getPartContent(0);
    }

    public function hasFrom()
    {
        return count($this->getFrom()) > 0;
    }

    public function hasTo()
    {
        return count($this->getTo()) > 0;
    }

    protected function setFromDefault()
    {
            $this->setFrom(static::$fromDefault);
    }

    protected function setHeaderToEncoding($encoding,$header)
    {
        if(is_array($header)){
            foreach($header as $h){
                $this->message->getHeaders()->get($h)->setEncoding($encoding);
            }
        }else{
            $this->message->getHeaders()->get($header)->setEncoding($encoding);
        }
    }

    protected function shouldSend()
    {
        $config = $this->sm->get('config');
        return isset($config['email']) && isset($config['email']['send']) ?
            $config['email']['send']
            :
            true;
    }

    protected function shouldEmulateSend()
    {
        $config = $this->getServiceLocator()->get('config');
        return isset($config['email']) && isset($config['email']['emulate']) ?
            $config['email']['emulate']
            :
            true;
    }

    protected function log($recipient)
    {
        //TODO define log structure
//        $statement = Log::create([
//            'log' => $recipient,
//        ]);
    }

    public function send()
    {
        if(!$this->hasFrom()){
            $this->setFromDefault();
        }

        if(!$this->hasTo()){
            throw new \Exception('No recipient');
        }

        $this->setHeaderToEncoding('UTF-8',['Subject','From','To']);

        if($this->shouldSend()){
            $transport = new Smtp();
            $transport->setOptions($this->getOptions())->send($this->getMessage());
        }else if($this->shouldEmulateSend()){
            //Write in File
        }

        foreach($this->getTo() as $recipient){
            $this->log($recipient->getEmail());
        }
    }

    public function alreadySentToRecipient($recipient,$returnCount = false)
    {
        return false;
//        $type = get_called_class();
//
//
//        $statement = Log::where([
//            'type' => $type,
//            'recipient' => $recipient
//        ]);
//
//
//        return $returnCount ? $statement->count() : $statement->count() > 0;
    }

    public function setServiceManager($sm)
    {
        $this->sm = $sm;
    }

    public function getServiceManager()
    {
        return $this->sm;
    }
}