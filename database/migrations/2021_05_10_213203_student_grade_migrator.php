<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StudentGradeMigrator extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('student_grades', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('grade_id');
      $table->unsignedBigInteger('user_id');
      $table->timestamps();
    });
    \App\Shared\RelationHelper::AttachRelation('student_grades', ['grade_id', 'user_id']);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('student_grades');
  }
}
