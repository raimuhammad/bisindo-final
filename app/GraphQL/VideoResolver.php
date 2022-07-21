<?php


namespace App\GraphQL;


use App\Models\Grade;
use App\Models\Video;
use App\Models\VideoGrade;
use App\Shared\GraphqlResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VideoResolver
 * @property Video $model
 * @package App\GraphQL
 */
class VideoResolver extends GraphqlResolver
{

  protected function transformArguments(array $arguments)
  {
    $arguments['description'] = json_encode($arguments['description']);
    return parent::transformArguments($arguments); // TODO: Change the autogenerated stub
  }

  public function getExcluded(array $array): array
  {
    return ["content", "grade_id"];
  }

  public function makeModel(): Model
  {
    if (isset($this->modelArguments['id'])){
      $id = $this->modelArguments['id'];
      unset($this->modelArguments['id']);
      return Video::find($id);
    }
    return Video::create($this->modelArguments);
  }

  protected function afterCreate()
  {
    $this->customModelUpdate(
      [["content", "attachContent"]]
    );
		$count = VideoGrade::whereGradeId($this->additionalArguments['grade_id'])->count();
		$connect = new VideoGrade();
		$connect->video_id = $this->model->id;
		$connect->grade_id = $this->additionalArguments['grade_id'];
		$connect->order = $count + 1;
		$connect->save();
  }

  protected function afterUpdate()
  {
//    $this->customModelUpdate(
////      [["content", "attachContent"]]
//    );
  }

  public function search($builder, string $value){
    if (! $value) return $builder;
//    $gradeIds = Grade::where("name", "like", $value)->select("id")->get()->pluck("id");
    $videoIds = Video::where("title", "like", $value)->select("id")->get()->pluck("id");
    return $builder->whereIn("id", $videoIds);
  }

  public function getByGrade($builder, string $gradeId){
    return $builder
	    ->join("video_grades", 'video_grades.video_id', 'videos.id')
	    ->where('video_grades.grade_id', $gradeId)
	    ->select(['videos.*', 'video_grades.order as order'])
	    ->orderBy('order');
  }

	public function videoNotInGrade($builder, string $gradeId){
		$excludedIds = VideoGrade::where("grade_id", $gradeId)->get()->pluck("video_id");
		return $builder->whereNotIn("id", $excludedIds);
	}

}