<?php

namespace Application\Validators;


use Zend\Validator\AbstractValidator;

class Password extends AbstractValidator
{

    const LENGTH = 'length';
    const UPPER  = 'upper';
    const LOWER  = 'lower';
    const DIGIT  = 'digit';

    protected $messageTemplates = array(
        self::LENGTH => "must be at least 8 characters in length",
        self::UPPER  => "must contain at least one uppercase letter",
        self::LOWER  => "must contain at least one lowercase letter",
        self::DIGIT  => "must contain at least one digit character"
    );

    public function isValid($value)
    {
        $this->setValue($value);

        $isValid = true;

        if (strlen($value) < 8) {
            $this->error(self::LENGTH);
            $isValid = false;
        }

//        if (!preg_match('/[A-Z]/', $value)) {
//            $this->error(self::UPPER);
//            $isValid = false;
//        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->error(self::LOWER);
            $isValid = false;
        }

        if (!preg_match('/\d/', $value)) {
            $this->error(self::DIGIT);
            $isValid = false;
        }

        return $isValid;
    }
}