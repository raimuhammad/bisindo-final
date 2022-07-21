<?php


namespace App\Shared;


use Illuminate\Database\Schema\Blueprint;
use Schema;
use Str;

class RelationHelper
{
  public static function AttachRelation(string $table, array $relations){
    Schema::table($table, function (Blueprint $table) use ($relations){
      foreach ($relations as $relation){
        $on = Str::of($relation)->beforeLast("_id")->plural();
        $table
          ->foreign($relation)
          ->on($on)
          ->references("id")
          ->cascadeOnUpdate()
          ->cascadeOnDelete();
      }
    });
  }
}
