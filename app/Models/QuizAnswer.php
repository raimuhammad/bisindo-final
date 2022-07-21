<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\QuizAnswer
 *
 * @property int $id
 * @property int $user_id
 * @property int $quiz_id
 * @property mixed $meta_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $from
 * @property-read mixed $items
 * @property-read mixed $selected
 * @property-read mixed $to
 * @method static \Database\Factories\QuizAnswerFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer whereQuizId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuizAnswer whereUserId($value)
 * @mixin \Eloquent
 */
class QuizAnswer extends Model
{
  use HasFactory;
  protected $guarded = ["id"];

  private function parseMetadata(string $key){
    $metadata = json_decode($this->meta_data, true);
    return $metadata[$key]??null;
  }
  /**
   * @attributes
   */
  public function getToAttribute(){
    return $this->parseMetadata("to");
  }
  /**
   * @attributes
   */
  public function getFromAttribute(){
    return $this->parseMetadata("from");
  }
  /**
   * @attributes
   */
  public function getItemsAttribute(){
    return $this->parseMetadata("items");
  }
  /**
   * @attributes
   */
  public function getSelectedAttribute(){
    return $this->parseMetadata("selected");
  }
}
