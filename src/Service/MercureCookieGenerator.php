<?php


namespace App\Service;


use App\Entity\User;
use Lcobucci\JWT\Token\Builder;

class MercureCookieGenerator
{
    public function generate(User $user){
        $token = (new Builder())
            ->set();
        return "mercureAuthorization={$token}; path=.well-known/mercure; httpOnly;";
    }

}