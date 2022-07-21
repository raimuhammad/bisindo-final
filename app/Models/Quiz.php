<?php

namespace App\Models;

use App\Utils\AttachMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\Quiz
 *
 * @property int $id
 * @property int $video_id
 * @property int $show_at
 * @property string $type
 * @property mixed $meta_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $letters
 * @property-read mixed $options
 * @property-read mixed $question_answer
 * @property-read mixed $question
 * @property-read mixed $word
 * @method static \Database\Factories\QuizFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz newQuery()
 * @method static \Illuminate\Database\Query\Builder|Quiz onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz query()
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz whereShowAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quiz whereVideoId($value)
 * @method static \Illuminate\Database\Query\Builder|Quiz withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Quiz withoutTrashed()
 * @mixin \Eloquent
 * @property-read mixed $additional_image
 * @property-read mixed $choises
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuizMetadata[] $metadatas
 * @property-read int|null $metadatas_count
 * @property-read mixed $image_matcher
 */
class Quiz extends Model implements HasMedia
{
  use HasFactory, SoftDeletes, InteractsWithMedia, AttachMedia;
  protected $guarded = ["id"];

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('additional-image')->singleFile();
  }
  public function addAdditionalImage($fileOrUrl){
    $this->attachMedia($fileOrUrl, 'additional-image');
  }

  public function metadatas(){
    return $this->hasMany(QuizMetadata::class, 'quiz_id');
  }


  private function parseMetadata(string $key){
    $metadata = json_decode($this->meta_data, true);
    return $metadata[$key]??null;
  }

  /**
   * @attributes
   */
  public function getQuestionAttribute(){
    if ($this->type !== "MULTIPLE_CHOICE"){
      return "";
    }
    return $this->parseMetadata("question") ?? "";
  }
  /**
   * @attributes
   */
  public function getQuestionAnswerAttribute(){
    if ($this->type !== "MULTIPLE_CHOICE"){
      return -1;
    }
    return (int) $this->parseMetadata("answer");
  }

  /**
   * @attributes
   */
  public function getAdditionalImageAttribute(){
    $media = $this->getFirstMediaUrl("additional-image");
    return str_replace(env("APP_URL"), "",$media ?? "");
  }
  /**
   * @attributes
   */
  public function getChoisesAttribute(){
    return $this->metadatas;
  }
  /**
   * @attributes
   */
  public function getImageMatcherAttribute(){
    if ($this->type === "MULTIPLE_CHOICE"){
      return "";
    }
    return $this->parseMetadata("text");
  }
}
