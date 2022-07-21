<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * App\Models\StudentGrade
 *
 * @property int $id
 * @property int $grade_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Grade $grade
 * @property-read \App\Models\User $student
 * @method static \Illuminate\Database\Eloquent\Builder|StudentGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StudentGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StudentGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder|StudentGrade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudentGrade whereGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudentGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudentGrade whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudentGrade whereUserId($value)
 * @mixin \Eloquent
 * @property-read mixed $progress
 */
class StudentGrade extends Model
{
  use HasFactory;
  protected $guarded = ['id'];

  public function grade(){
    return $this->belongsTo(Grade::class)->withTrashed();
  }
  public function student(){
    return $this->belongsTo(User::class, 'user_id')->withTrashed();
  }

  public function builder($builder){
    return $builder->join("users", "student_grades.user_id", "users.id")
      ->select(["student_grades.*","users.name as username"])->orderBy("username");
  }

  public function search($builder, string $value){
    $userIds = User::where("name", "like", $value)->get()->pluck("id");
    $gradeIds = Grade::where("name", 'like', $value)->get()->pluck("id");
    return $builder->whereIn('grade_id', $gradeIds)->orWhereIn("user_id", $userIds);
  }

  /**
   * @attributes
   */
  public function getProgressAttribute(){
    return Progress::whereUserId($this->user_id)->first();
  }

}
