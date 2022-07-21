<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Grade
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $student_count
 * @property-read mixed $video_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\StudentGrade[] $students
 * @property-read int|null $students_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Video[] $videos
 * @property-read int|null $videos_count
 * @method static \Database\Factories\GradeFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Grade extends Model
{
  use HasFactory, SoftDeletes;
  protected $guarded = ["id"];

  public function students(){
    return $this->hasMany(StudentGrade::class);
  }
	public function getVideosAttribute(){
		$builder = VideoGrade::where(["grade_id"=>$this->id])->get()->pluck("video_id");
		return Video::whereIn("id",$builder);
	}
  public function getStudentCountAttribute(){
    return $this->students()->count();
  }
  public function getVideoCountAttribute(){
    return $this->videos->count();
  }

}
