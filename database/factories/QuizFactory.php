<?php

namespace Database\Factories;

use App\Constants\AppRole;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\User;
use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
  protected $model = Quiz::class;

  private function imageMatch(){
    return [
      "letters"=>$this->faker->word
    ];
  }
  private function letterSequence(){
    return [
      "word"=>$this->faker->words(1)[0]
    ];
  }
  private function randomAlpha($current = []){
    $val = range("a", "z");
    $alpha = $this->faker->randomElement($val);
    if (in_array($alpha, $current)){
      return $this->randomAlpha($current);
    }
    return $alpha;
  }
  private function multipleChoice(){
    $options = [];
    for ($i = 0; $i< 4; $i++){
      $options[] = $this->randomAlpha($options);
    }
    return [
      "options"=>$options,
      "question"=>$this->faker->realText(50),
      "question_answer"=>$this->faker->randomElement($options)
    ];
  }
  private function makeMetaData(string $type){
    switch ($type){
      case "IMAGE_MATCH": return $this->imageMatch();
      case "LETTER_SEQUENCE": return $this->letterSequence();
      case "MULTIPLE_CHOICE": return $this->multipleChoice();
    }
    return[];
  }

  public function configure()
  {
    return $this->afterMaking(function ($user) {
      //
    })->afterCreating(function (Quiz $quiz) {
      User::role(AppRole::SUBSCRIBER)->get()->each(function (User $user) use ($quiz) {
        QuizAnswer::factory()->create([
          "quiz_id"=>$quiz->id,
          "user_id"=>$user->id,
          "meta_data"=>json_encode(QuizAnswerFactory::makeMetaData($quiz))
        ]);
      });
    });
  }

  public function definition()
  {
    return [
      "show_at"=> $this->faker->numberBetween(15,120),
      "type"=> $type,
      "meta_data"=>json_encode($this->makeMetaData($type))
    ];
  }
}
