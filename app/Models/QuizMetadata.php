<?php

namespace App\Models;

use App\Utils\AttachMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\QuizMetadata
 *
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 * @property-read int|null $media_count
 * @method static \Database\Factories\QuizMetadataFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizMetadata newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizMetadata newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizMetadata query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $quiz_id
 * @property mixed $meta_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|QuizMetadata whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizMetadata whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizMetadata whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizMetadata whereQuizId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizMetadata whereUpdatedAt($value)
 * @property-read mixed $image
 * @property-read mixed $index
 * @property-read mixed $text
 */
class QuizMetadata extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia, AttachMedia;
  protected $guarded = ["id"];
  protected $table = "quiz-metadatas";

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('option-image')->singleFile();
  }

  public function addOptionImage($fileOrUrl){
    $this->attachMedia($fileOrUrl, "option-image");
  }
  /**
   * @attributes
   */
  public function getImageAttribute(){
    return $this->getFirstMediaUrl("option-image") ?? "";
  }
  /**
   * @attributes
   */
  public function getTextAttribute(){
    $meta = json_decode($this->meta_data, true);
    return $meta["text"] ?? "";
  }
  /**
   * @attributes
   */
  public function getIndexAttribute(){
    $meta = json_decode($this->meta_data, true);
    return $meta["index"] ?? 0;
  }
}
