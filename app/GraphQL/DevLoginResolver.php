<?php


namespace App\GraphQL;


class DevLoginResolver
{

  public function __invoke($_, array $__)
  {
    return [
      "email"=>env("DEV_EMAIL", "laravel@laravel.dev"),
      "password"=>"password"
    ];
  }

}
