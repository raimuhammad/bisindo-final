<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class QuizMigrator extends Migration
{
  public function up()
  {
    Schema::create('quizzes', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger("video_id");
      $table->unsignedBigInteger("show_at");
      $table->enum("type", ["IMAGE_MATCH", "LETTER_SEQUENCE", "MULTIPLE_CHOICE"]);
      $table->json("meta_data");
      $table->timestamps();
      $table->softDeletes();
    });
    \App\Shared\RelationHelper::AttachRelation("quizzes", [
      "video_id"
    ]);
  }
  public function down()
  {
    Schema::dropIfExists('quizes');
  }
}
