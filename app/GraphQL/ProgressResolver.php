<?php

namespace App\GraphQL;

use App\Models\Progress;
use App\Models\Quiz;
use App\Models\Video;

class ProgressResolver
{

  public function updateVideoProgress($_, array $args){
    $video = Video::find($args['video_id']);
    $progress = Progress::whereUserId(auth()->id())->first();
		$play = $args['play'] ?? 0;
    if ($progress){
      $videos = collect(\Safe\json_decode($progress->video_histories, true));
      $check = $videos->first(function ($item) use ($video){
        return $item['video_id'] === $video->id;
      });
			if (! $check){
				$videos->push([
					"video_id"=>$video->id,
					"time"=>$args['play'] ?? 0
				]);
			}
      if ($check && $check['time'] <= $play){
				$videos = $videos->map(function ($item) use ($check, $play){
					if ($check['video_id'] == $item['video_id']){
						$item['time'] = $play;
					}
					return $item;
				});
      }
      $progress->video_histories = $videos->toJson();
      $progress->save();
    }
    return $progress;
  }
  public function updateQuizProgress($_, array $args){
    $quiz = Quiz::find($args['quiz_id']);
    $progress = Progress::whereUserId(auth()->id())->first();
    if ($progress){
      $attemptedQuiz = collect(\Safe\json_decode($progress->quiz_histories, true));
      $check = $attemptedQuiz->first(function ($item) use ($quiz){
        return $item['id'] === $quiz->id;
      });
      if ($check){
        return $progress;
      }
      $attemptedQuiz->push([
        "correct"=>$args['correct'],
        "id"=>$quiz->id,
	      "videoId"=>$quiz->video_id
      ]);
      $progress->quiz_histories = $attemptedQuiz->toJson();
      $progress->save();
    }
    return $progress;
  }

  public function find(){
    if (auth()->check()){
      $check = Progress::whereUserId(auth()->id())->first();
      if (! $check){
        $check = Progress::create([
          "user_id" => auth()->id(),
          "video_histories" => "[]",
          "quiz_histories" => "[]", 
        ]);
      }
      return $check;
    }
    return null;
  }

  public function getByGrade($_, array $arg){
    $videoIds = Video::whereGradeId($arg['grade_id'])
      ->get()
      ->pluck("id");
    return Progress::all()->filter(function (Progress $progress) use ($videoIds) {
      $doneVideos = collect(\Safe\json_decode($progress->video_histories, true))->pluck("video_id");
      if (! $doneVideos->count()){
        return false;
      }
      $check = $doneVideos->first(function ($dt) use($videoIds) {
        return $videoIds->first(function ($item) use($dt){
          return $item === $dt;
        });
      });
      return (bool) $check;
    });
  }

}
