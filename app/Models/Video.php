<?php

namespace App\Models;

use App\Constants\AppRole;
use App\Utils\AttachMedia;
use App\Utils\DurationHelper;
use FFMpeg\FFProbe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Video
 *
 * @property int $id
 * @property int $grade_id
 * @property string $title
 * @property string $caption
 * @property int $duration
 * @property mixed $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $content
 * @property-read string $thumbnail
 * @property-read \App\Models\Grade $grade
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @method static \Database\Factories\VideoFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Video newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Video newQuery()
 * @method static \Illuminate\Database\Query\Builder|Video onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Video query()
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Video withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Video withoutTrashed()
 * @mixin \Eloquent
 * @property-read mixed $student_progress
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Quiz[] $quizes
 * @property-read int|null $quizes_count
 * @property-read mixed $grades
 * @property-read mixed $quiz_count
 */
class Video extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia, SoftDeletes, AttachMedia;


  protected $guarded = ["id"];

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('content')->singleFile();
    $this->addMediaCollection('description')->singleFile();
  }
  public function registerMediaConversions(Media $media = null): void
  {
    $this->addMediaConversion('thumbnail')
      ->extractVideoFrameAtSecond(1)
      ->performOnCollections("content");
  }
  /**
   * @utility
   */
  public function getGradesAttribute(){
		$connect = VideoGrade::whereVideoId($this->id);
		return Grade::whereIn("id", $connect->get()->pluck("grade_id"))->get();
  }
  /**
   * @utility
   */
  public function quizes(){
    return $this->hasMany(Quiz::class);
  }

	public function getQuizCountAttribute(){
		return $this->quizes->count();
	}

  public function durationHelper() : int{
    $video = $this->getFirstMedia('content');
    $helper = new DurationHelper();
    return $helper->getVideoDuration($video->getPath());
  }
  /**
   * @utility
   */
  public function attachContent($fileOrUrl) : void {
    $this->attachMedia($fileOrUrl, 'content');
    $this->duration = $this->durationHelper();
    $this->save();
  }
  /**
   * @utility
   */
  public function attachDescription($fileOrUrl) : void {
    $file = UploadedFile::fake()->createWithContent("description.json",json_encode($fileOrUrl));
    $this->attachMedia($file, 'description');
  }
  /**
   * @attributes
   * @return string
   */
  public function getContentAttribute() : string {
		$media = $this->getFirstMedia("content");
		$ext = \Str::afterLast($media->file_name,".");
		return route("stream", [
			"fileId"=>$media->uuid,
			"extension"=>$ext
		]);
  }
	public function getOrderAttribute(){
		/**
		 * @var User $user
		 */
		$user = auth()->user();
		if ($user && $user->hasRole(AppRole::SUBSCRIBER)){
			$progress = Progress::whereUserId($user->id)->first();
			if ($progress && $connect = VideoGrade::
				whereVideoId($this->id)->whereGradeId($progress->student_grade->grade_id)->first()){
				return $connect->order;
			}
		}
		return 0;
	}
  /**
   * @attributes
   * @return string
   */
  public function getThumbnailAttribute() : string {
		return str_replace(env("APP_URL"), "",$this->getFirstMediaUrl("content", 'thumbnail') );
  }
  public function getStudentProgressAttribute(){
    if (auth()->user()->hasRole(AppRole::ADMIN)){
      return Progress::all()->filter(function (Progress $progress){
        $current = collect(\Safe\json_decode($progress->video_histories, true));
        return $current->first(function ($item){
          return $item['video_id'] == $this->id;
        });
      });
    }
    return [];
  }
}
