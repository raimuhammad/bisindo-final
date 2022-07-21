<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VideoFactory extends Factory
{
  protected $model = Video::class;

  public function definition()
  {
    $name = "description.json";
    $content = Storage::get($name);
    return [
      "title"=>$this->faker->course,
      "caption"=>$this->faker->text(50),
      "description"=>$content
    ];
  }

  private function addVideo(Video $video){
    $n = $this->faker->randomElement([1,2,3]);
    $name = "assets/sample-video/$n.mp4";
    $content = Storage::get($name);
    $uploaded = UploadedFile::fake()->createWithContent($name,$content);
    $video->attachContent($uploaded);
    $video->duration = $video->durationHelper();
    $video->save();
  }

  public function configure()
  {
    return $this->afterMaking(function ($user) {
      //
    })->afterCreating(function (Video $video) {
      $this->addVideo($video);
//      Quiz::factory()->count(2)->create([
//        "video_id"=>$video->id
//      ]);
    });
  }
}
