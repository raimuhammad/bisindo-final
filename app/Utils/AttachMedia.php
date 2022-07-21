<?php


namespace App\Utils;


use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

trait AttachMedia
{
  /**
   * @utility
   * @param $fileOrUrl
   * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded
   * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
   * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
   */
  public function attachMedia($fileOrUrl, string $collection = ""){
    /**
     * @var FileAdder $media
     */
    $media = null;
    if (is_string($fileOrUrl)){
      if (Str::contains($fileOrUrl, 'http')){
        $media = $this->addMediaFromUrl($fileOrUrl);
      }else{
        $media = $this->addMedia($fileOrUrl);
      }
    }else{
      $media = $this->addMedia($fileOrUrl);
    }
    $media
      ->preservingOriginal()
      ->toMediaCollection($collection);
  }
}
