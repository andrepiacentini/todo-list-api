<?php

/**
 * Created by PhpStorm.
 * User: murilo
 * Date: 4/18/17
 * Time: 4:44 PM
 */
class SmtpConfig extends \Zend\Mail\Transport\SmtpOptions
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setHost('smtp.gmail.com')
            ->setConnectionClass('login')
            ->setName('smtp.gmail.com')
            ->setConnectionConfig(array(
                'username' => 'YOUR GMAIL ADDRESS',
                'password' => 'YOUR PASSWORD',
                'ssl' => 'tls',
            ));
    }

}