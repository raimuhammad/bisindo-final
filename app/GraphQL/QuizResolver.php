<?php


namespace App\GraphQL;


use App\Models\Quiz;
use App\Models\QuizMetadata;
use App\Models\Video;
use App\Shared\GraphqlResolver;
use Illuminate\Database\Eloquent\Model;

/**
 * Class QuizResolver
 * @package App\GraphQL
 * @property Quiz $model
 */
class QuizResolver extends GraphqlResolver
{

  protected function transformArguments(array $arguments)
  {
    if ($arguments['type'] === "MULTIPLE_CHOICE"){
      if (isset($arguments['answer']) && isset($arguments['question'])){
        $arguments['meta_data'] = json_encode([
          "question"=>$arguments['question'],
          "answer"=>$arguments['answer'],
        ]);
        unset($arguments['answer']);
        unset($arguments['question']);
      }
    }
    if ($arguments['type'] === "IMAGE_MATCH" || $arguments['type'] === "LETTER_SEQUENCE"){
      $arguments['meta_data'] = json_encode([
        "text"=>$arguments['text']
      ]);
      unset($arguments['text']);
    }
    return parent::transformArguments($arguments);
  }

  public function getExcluded(array $array): array
  {
    return [
      "options",
      "additionalFile",
    ];
  }
  public function makeModel(): Model
  {
    return Quiz::create($this->modelArguments);
  }

  private function getType($v){
    $map = [
      "multipleChoiseQuiz"=>'MULTIPLE_CHOICE',
      "imageMatchQuiz"=>"IMAGE_MATCH",
      "letterSequenceQuiz"=>"LETTER_SEQUENCE"
    ];
    return $map[$v];
  }

  public function addMultipleChoiseOption(){
    $options = $this->additionalArguments['options'];
    $additionalImage = $this->additionalArguments['additionalFile'] ?? null;
    if ($additionalImage){
      $this->model->addAdditionalImage($additionalImage);
    }
    foreach ($options as $option){
      $isText = isset($option['text']);
      $instance = QuizMetadata::create([
        "quiz_id"=>$this->model->id,
        "meta_data"=>"{}"
      ]);
      $metadata = [
        "index"=>$option['index']
      ];
      if ($isText){
        $metadata['text'] = $option['text'];
      }
      if (isset($option['image'])){
        $instance->addOptionImage($option['image']);
      }
      $instance->meta_data = json_encode($metadata);
      $instance->save();
    }
  }

  protected function afterCreate()
  {
    if ($this->model->type === 'MULTIPLE_CHOICE'){
      $this->addMultipleChoiseOption();
    }
  }

  public function create($_, array $args, $ctx = null, $fieldInfo = null)
  {
    $args["type"] = $this->getType($fieldInfo->fieldDefinition->name);
    return parent::create($_, $args, $ctx, $fieldInfo);
  }

  public function getByGrade($_, $gradeId){
    $videos = Video::whereGradeId($gradeId)->get()->pluck("id");
    return $_->whereIn("video_id", $videos);
  }

}
