<?php


namespace App\Utils;


use App\Models\Progress;
use App\Models\Video;
use Illuminate\Support\Collection;

class VideoProgressHelper
{
  private Progress $progress;

  private Collection $data;

  public function __construct(Progress $progress)
  {
    $this->progress= $progress;
    $this->data = collect(json_decode($progress->video_histories ?? "[]", true));
  }

  public function add(Video $video, int $time){
    $this->data->add([
      "video_id"=>$video->id,
      "time"=>$time
    ]);
  }

  public function update(Video $video, int $time){
    $isExist = $this->data->first(function ($item) use ($video){
      return $item['video_id'] === $video->id;
    });
    if (!$isExist){
      $this->add($video, $time);
    }else{
      $this->data->map(function ($item) use ($video, $time){
        if ($item['video_id'] === $video->id){
          $item['time'] = $time;
        }
        return $item;
      });
    }
  }

  public function toJson(){
    return $this->data->toJson();
  }
  public function count(){
    return $this->data->count();
  }
}
