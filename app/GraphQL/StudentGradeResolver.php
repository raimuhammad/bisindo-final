<?php


namespace App\GraphQL;


use App\Models\Grade;
use App\Models\StudentGrade;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;

class StudentGradeResolver
{

  private function useEmail($builder, string $email){
    return $builder;
  }
  private function useUserName($builder, string $email){
    return $builder;
  }
  private function useGradeName($builder, string $email){
    return $builder;
  }

  public function builder($b){
    return $b
      ->join("users", "users.id", "student_grades.user_id")
      ->join("grades", "grades.id", "student_grades.grade_id")
	    ->whereNull("users.deleted_at")
      ->select(["student_grades.*"])
      ->orderBy("users.name","ASC");
  }

  public function search(Builder $builder, string $search){
    $users = User::where('name', 'like', $search)->get()->pluck("id");
    $grade = Grade::where('name', 'like', $search)->get()->pluck("id");
    $q = $builder
      ->whereIn("user_id", $users)
      ->orWhereIn("grade_id",$grade);
    return $q;
  }
  public function byGrade(Builder $builder, string $search){
    return $builder->whereGradeId($search);
  }

  public function getGradeByAuth(){
    return StudentGrade::whereUserId(auth()->id());
  }

  public function quiz($builder, string $gradeId){
    $videos = Video::whereGradeId($gradeId)->get()->pluck("id");
    return $builder->whereIn("video_id", $videos);
  }
}
