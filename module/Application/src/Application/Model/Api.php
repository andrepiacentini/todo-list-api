<?php
namespace Application\Model;


class Api
{
    protected $allowed = null;   // rotas que não são verificadas para autorização

    public function setAllowedRoutes($allowed_routes)
    {
        $this->allowed = $allowed_routes;
    }

    public function getAllowedRoutes()
    {
        return $this->allowed;
    }

    public function isProtected($sUri)
    {
        return (!in_array($sUri,$this->allowed));
    }

}