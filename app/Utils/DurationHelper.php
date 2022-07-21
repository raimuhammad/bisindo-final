<?php


namespace App\Utils;


use FFMpeg\FFProbe;

class DurationHelper
{

  private FFProbe $probe;
  private int $duration = 0;
  public function __construct()
  {
    $this->probe = FFProbe::create([
      'ffmpeg.binaries'  =>config('media-library.ffmpeg_path'),
      'ffprobe.binaries' => config('media-library.ffprobe_path'),
    ]);
  }
  public function getVideoDuration(string $videoPath) : int {
    return (int) $this->probe->format($videoPath)->get("duration");
  }
}
