<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Iman\Streamer\VideoStreamer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StreamingController extends Controller
{
	public function streaming( string $fileId , string $extension){
		$media = Media::findByUuid($fileId);
		if (
			! $media
		){
			return response()->setStatusCode(404);
		}
		VideoStreamer::streamFile($media->getPath());
	}
}
