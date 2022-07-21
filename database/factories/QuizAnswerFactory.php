<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class QuizAnswerFactory extends Factory
{
  protected $model = QuizAnswer::class;

  public static function makeImageMatch(Quiz $quiz){
    $letters = $quiz->letters;
    $lettersArray = str_split($letters);
    return [
      "from"=>$lettersArray,
      "to"=>Arr::shuffle($lettersArray)
    ];
  }
  public static function makeLetterSequence(Quiz $quiz){
    $word = $quiz->word;
    return [
      "items"=>Arr::shuffle(str_split($word))
    ];
  }
  public static function makeMultipleChoice(Quiz $quiz){
    $options = $quiz->options;
    $self = new self();
    return [
      "selected"=>$self->faker->randomElement($options)
    ];
  }
  public static function makeMetaData(Quiz $quiz){
    $type = $quiz->type;
    switch ($type){
      case "IMAGE_MATCH": return self::makeImageMatch($quiz);
      case "LETTER_SEQUENCE": return self::makeLetterSequence($quiz);
      case "MULTIPLE_CHOICE": return self::makeMultipleChoice($quiz);
    }
    return [];
  }


  public function definition()
  {
    return [
      "meta_data"=>json_encode([""])
    ];
  }
}
