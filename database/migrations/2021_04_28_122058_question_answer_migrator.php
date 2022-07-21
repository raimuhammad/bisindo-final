<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class QuestionAnswerMigrator extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('quiz_answers', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger("user_id");
      $table->unsignedBigInteger("quiz_id");
      $table->json("meta_data");
      $table->timestamps();
    });
    \App\Shared\RelationHelper::AttachRelation("quiz_answers", ["user_id", "quiz_id"]);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('question_answers');
  }
}
