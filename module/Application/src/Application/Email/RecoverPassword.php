<?php
namespace Application\Email;

use Application\Email\Mailable;

class RecoverPassword extends Mailable
{
    protected $view = 'recover-password';

    public function boot()
    {
        $this->setSubject('Tradetools - Recover password');
    }
}
