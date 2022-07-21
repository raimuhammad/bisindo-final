<?php


namespace App\GraphQL;


//use App\Models\PasswordReset;
use App\Models\User;
//use App\Notifications\PasswordResetNotification;
use Faker\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthResolver
{
  public function login($_, array $args) : bool {
    $credential = Arr::except($args, ['directive']);
    $guard = auth()->guard();
    $check = $guard->attempt($credential);
    if ($check){
      return auth()->user()->active;
    }
    return false;
  }

  public function register($_, array $args) : bool {
    $inputs = \Arr::except($args, ['password_confirmation', 'directive']);
    $inputs['password'] = Hash::make($args['password']);
    User::create($inputs);
    return true;
  }
  public function logout($_, array $__):bool{
    $user = auth()->user();
    if ($user){
      auth()->logout();
      return true;
    }
    return false;
  }
}
