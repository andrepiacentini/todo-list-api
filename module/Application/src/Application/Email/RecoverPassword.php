<?php
namespace Application\Email;

class RecoverPassword extends Mailable
{
    protected $view = 'recover-password';

    public function boot()
    {
        $this->setSubject('Tradetools - Recover password');
    }
}
