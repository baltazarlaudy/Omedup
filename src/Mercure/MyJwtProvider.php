<?php


namespace App\Mercure;


final class MyJwtProvider
{
    public function __invoke(): string
    {
        return 'the-JWT';
    }
}