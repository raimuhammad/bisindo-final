<?php

namespace App\Models;

use App\Utils\VideoProgressHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Progress
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Progress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Progress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Progress query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property mixed $video_histories
 * @property mixed $quiz_histories
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Progress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Progress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Progress whereQuizHistories($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Progress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Progress whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Progress whereVideoHistories($value)
 * @property-read mixed $completion_percentage
 * @property-read StudentGrade $student_grade
 * @property-read mixed $video_history
 * @property-read mixed $completion
 * @property-read \App\Models\User $user
 */
class Progress extends Model
{
  use HasFactory;

  protected $table = "progresses";
  protected $guarded = ["id"];
  private function parseJsonValue(string $key){
    return json_decode($this->$key ?? "[]", true);
  }

  public function user(){
    return $this->belongsTo(User::class);
  }

  private function videoHelper(){
    return new VideoProgressHelper($this);
  }
  /**
   * @attributes
   */
  public function getStudentGradeAttribute():? StudentGrade {
    return StudentGrade::whereUserId($this->user_id)->first();
  }
  /*
   * @attributes
   */
  public function getVideoHistoryAttribute(){
    return $this->videoHelper()->toJson();
  }
  /**
   * @attributes
   */
  public function getCompletionAttribute(){
    $studentGrade = $this->student_grade;
    $vCount = $studentGrade->grade->videos()->count();
    $quizCount = $studentGrade->grade->videos()
      ->join("quizzes", "videos.id", "quizzes.id")->count();
    $attemptedQuiz = 0;
    $attemptedVideo = $this->videoHelper()->count();
    return round((($attemptedQuiz + $attemptedVideo) / ($vCount + $quizCount)) * 100, 1) ;
  }
}
