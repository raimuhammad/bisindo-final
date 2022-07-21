<?php


namespace App\GraphQL;


use App\Constants\AppRole;
use App\Models\StudentGrade;
use App\Models\User;
use App\Notifications\InvitationNotification;
use App\Shared\GraphqlResolver;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

/**
 * Class UserResolver
 * @package App\GraphQL
 * @property User $model
 */
class UserResolver extends GraphqlResolver
{

	public function checkInvitation($_, $args ){
		$code = $args['code'];
		$user = User::whereInvitation($code)->first();
		if (! $user) return false;
		return true;
	}

  public function students($_, $__){
    $auth = auth()->guard()->check();
    if (! $auth) return [];
    $user = auth()->user();
    if (! $user->hasRole(AppRole::ADMIN)){
      return [];
    }
    return User::role(AppRole::SUBSCRIBER)->get();
  }

  public function getExcluded(array $array): array
  {
    return ["grade_id"];
  }


  protected function transformArguments(array $arguments)
  {
    $arguments['password'] = env('APP_KEY');
    return $arguments;
  }

  public function generateToken(){
    $faker = Factory::create();
    $str = $faker->regexify('[A-Z0-9][A-Z0-9.-][A-Z]{10}');
    $check = User::whereInvitation($str)->count();
    if ($check) return $this->generateToken();
    return $str;
  }

  public function makeModel(): Model
  {
    $data = $this->modelArguments;
    $data['invitation'] = $this->generateToken();
    $data['password'] = Hash::make("password");
    return User::create($data);
  }
  protected function afterCreate()
  {
    $this->model->assignRole(AppRole::SUBSCRIBER);
    $this->model->notify(new InvitationNotification($this->model));
    StudentGrade::create([
      "user_id"=>$this->model->id,
      'grade_id'=>$this->additionalArguments['grade_id']
    ]);
    parent::afterCreate();
  }

  private function changePassword(User $user, string $hashedPassword){
    $user->password = $hashedPassword;
    $user->invitation = "";
    $user->active = true;
    $user->save();
  }

  public function activation($_, array $args){
    $user = User::find($args['id']);
    if (! $user) return null;
    $this->changePassword($user, Hash::make($args['password']));
    $user->refresh();
    return $user;
  }

  public function changeDefaultPassword($_, array $args){
    $password = Hash::make($args['password']);
    /**
     * @var User $user
     */
    $user = auth()->user();
    if ($user && $user->invitation){
      $this->changePassword($user, $password);
			$user->active = 1;
			$user->save();
      return true;
    }
    return false;
  }

  public function sendInvitation($_, $args){
    $user = User::find($args['id'] ?? "-10");
    if ($user && ! $user->active){
      $user->invitation = $this->generateToken();
      $user->save();
      $user->notify(new InvitationNotification($user));
      return true;
    }
    return false;
  }

  public function loginWithInvitation($_, $args){
    $query = User::whereInvitation($args['invitation'])->first();
		if (! $query) return false;
		$guard = auth()->guard();
		if ($guard->user()){
			$guard->logout();
		}
	  $guard->loginUsingId($query->id);
	  return Hash::check('password',$query->password);

//    $check = $query->count();
//    if ($check){
//      $user = $query->get()->first();
//      $this->changePassword($user, Hash::make($args['password']));
//      return true;
//    }
//    return false;
  }
  public function isUniqEmail($_, $args) : bool{
    return User::where("email", $args['email'])->count() === 0;
  }
  public function getUserByGrade($_, array $args){
    $studentIds = StudentGrade::whereGradeId($args['grade_id'])->get()->pluck("user_id");
    return User::whereIn('id',$studentIds)->get();
  }
}
