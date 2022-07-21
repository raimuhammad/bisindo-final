<?php


namespace App\Constants;


use Spatie\Permission\Models\Role;

class AppRole
{
  const ADMIN = "admin";
  const SUBSCRIBER = "students";

  const ROLES = [self::ADMIN, self::SUBSCRIBER];

  public static function register(){
    foreach (self::ROLES as $role){
      Role::findOrCreate($role);
    }
  }
}
