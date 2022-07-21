<?php

namespace App\GraphQL\Validators;

use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class QuizInputValidator extends Validator
{

  private function makeImageMatcherRule(){
    return [
      "meta_data.letters"=>["required", "string"]
    ];
  }
  private function makeLetterSequenceRule(){
    return [
      "meta_data.word"=>["required", "string"]
    ];
  }
  private function makeMultiChoiseRule(){
    return [
      "meta_data.question"=>["required", "string"],
      "meta_data.options"=>["array"],
      "meta_data.options.*"=>["required" ,"string"],
      "meta_data.question_answer"=>["required","string"],
    ];
  }

  private function getAddtionalRule() : array{
    $arg = $this->arg("type");
    switch ($arg){
      case "IMAGE_MATCH" : return $this->makeImageMatcherRule();
      case "LETTER_SEQUENCE": return $this->makeLetterSequenceRule();
      case "MULTIPLE_CHOICE": return $this->makeMultiChoiseRule();
    }
    return [];
  }

  public function rules(): array
  {
    return array_merge([
      "video_id"=>["required", "exists:videos,id"],
      "type"=>["required", Rule::in(["IMAGE_MATCH", "LETTER_SEQUENCE", "MULTIPLE_CHOICE"])]
    ], $this->getAddtionalRule());
  }
}
