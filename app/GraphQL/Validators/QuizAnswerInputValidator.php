<?php


namespace App\GraphQL\Validators;


use App\Models\Quiz;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class QuizAnswerInputValidator extends Validator
{

  private array $alphabetic = [];
  private Quiz $quiz;

  public function __construct()
  {
    $this->alphabetic = range("a","z");
  }

  public function imageMatchRule(){
    $arr = (str_split($this->quiz->letters));
    $rules = ["required", "string", Rule::in($arr)];
    return [
      "meta_data.from"=>["required", "array", "size:" . count($arr)],
      "meta_data.from.*"=>$rules,
      "meta_data.to"=>["required", "array", "size:". count($arr)],
      "meta_data.to.*"=>$rules
    ];
  }

  public function letterSequenceRule(){
    return [
      "meta_data.items"=>["required",'array'],
      "meta_data.items.*"=>["string",Rule::in(str_split($this->quiz->word))]
    ];
  }

  public function multipleChoice(){
    return [
      "meta_data.selected"=>["required", Rule::in($this->quiz->options)]
    ];
  }

  public function makeMetaDataRule(){
    if ($this->arg("quiz_id")){
      $quiz = Quiz::find($this->arg("quiz_id"));
      $type = $quiz->type;
      $this->quiz = $quiz;
      switch ($type){
        case "IMAGE_MATCH" : return $this->imageMatchRule();
        case "LETTER_SEQUENCE": return $this->letterSequenceRule();
        case "MULTIPLE_CHOICE": return $this->multipleChoice();
      }
    }
    return [];
  }

  public function rules(): array
  {
    return array_merge([
      "quiz_id"=>["required", "exists:quizzes,id"],
    ], $this->makeMetaDataRule());
  }
}
