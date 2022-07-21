<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VideoMigrator extends Migration
{
  public function up()
  {
    Schema::create('videos', function (Blueprint $table) {
      $table->id();
      $table->string("title");
      $table->string("caption");
      $table->integer("duration")->default(0);
      $table->json("description");
      $table->softDeletes();
      $table->timestamps();
    });
  }
  public function down()
  {
    Schema::dropIfExists('videos');
  }
}
