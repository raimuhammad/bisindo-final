<?php

namespace App\Console\Commands;

use App\Constants\AppRole;
use App\Models\Grade;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizMetadata;
use App\Models\StudentGrade;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoGrade;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class Development extends Command
{
  protected $signature = 'dev';
  protected $description = '';
  private User $user;
  private \Faker\Generator $faker;

  public function __construct()
  {
    parent::__construct();
    $this->faker = Factory::create();
  }

  private $modelStacks = [
    QuizAnswer::class,
    QuizMetadata::class,
    Quiz::class,
    Video::class,
    StudentGrade::class,
    Grade::class,
    User::class,
  ];

  public function resetDb(){
    $this->info("resetting database");
    AppRole::register();
    Schema::disableForeignKeyConstraints();
    foreach ($this->modelStacks as $modelStack){
      $modelStack::query()->truncate();
    }
    Schema::enableForeignKeyConstraints();
  }

  public function makeUser(string $email, string $role, bool $isActive = false){
    $user = User::factory()->create([
      "email"=> $email,
      'active'=>$isActive
    ]);
    $user->assignRole($role);
    $this->info("user ". $user->email . " has been created ");
    return $user;
  }

  public function makeMultipleChoiseQuiz(int $i, $video ){
    $metadata = [
      "question"=> $this->faker->text(100),
      "answer"=>$this->faker->randomElement([0,1,2,3])
    ];
    $isImage = $this->faker->boolean;

    $quiz = Quiz::create([
      "video_id"=>$video->id,
      "meta_data"=>json_encode($metadata),
      "show_at"=>$this->faker->numberBetween(1, $video->duration),
      "type"=>"MULTIPLE_CHOICE",
    ]);
    if ($isImage){
      $quiz->addAdditionalImage(public_path() . "/quiz.jpg");
    }
    $quizInfos[] = [];
    $metas = [];
    for ($j = 0; $j < 4; $j++){
      $quizMeta = [
        "index"=>$j + 1,
      ];
      if (!$isImage){
        $quizMeta['text'] = $this->faker->text(30);
      }
      $quizInfos[] = $quizMeta;
      $metas[] = QuizMetadata::create([
        "quiz_id"=>$quiz->id,
        "meta_data"=>json_encode($quizMeta)
      ]);
    }
    if ($isImage)
      foreach ($metas as $index=>$meta){
        $meta->addOptionImage(public_path() . "/option-". $index .".jpg");
      }
  }

  public function randomUniqueLetter($current){
    $alphas = range("a", "z");
    $lettter = $this->faker->randomElement($alphas);
    if (in_array($lettter, $current)){
      return $this->randomUniqueLetter($current);
    }
    return $lettter;
  }

  public function makeImageMatchQuiz($video, bool $isLetter = false){
    $letters = [];

    for ($i = 0; $i < 5; $i ++){
      $letters[] = $this->randomUniqueLetter($letters);
    }
    Quiz::create([
      "video_id"=>$video->id,
      "meta_data"=>json_encode([
        "text"=>join("", $letters)
      ]),
      "show_at"=>$this->faker->numberBetween(1, $video->duration),
      "type"=>$isLetter ? "IMAGE_MATCH" : "LETTER_SEQUENCE",
    ]);
  }

  public function makeVideos(Grade $grade){
    $c = Factory::create()->numberBetween(5,10);
    /**
     * @var Video $video
     */
    $video = Video::factory()
      ->count($c)
	    ->create()
      ->each(function (Video $video) use ($grade){
				VideoGrade::create([
					"video_id"=>$video->id,
					"grade_id"=>$grade->id,
				]);
        for ($i = 0; $i < 5; $i++){
          if ($i === 0){
            $this->makeImageMatchQuiz($video);
          }else{
            if ($i % 5 === 0){
              $this->makeImageMatchQuiz($video, true);
            }else{
              $this->makeMultipleChoiseQuiz($i, $video);
            }
          }
        }
      });
  }

  public function makeProgress(User $user, Grade $grade){
    $data = collect();
    $c = $grade->videos->count();
    foreach ($grade->videos->get()->take($this->faker->numberBetween(1, $c)) as $video){
      $data->add([
        "video_id"=>$video->id,
        "time"=>$this->faker->numberBetween(1, $video->duration),
      ]);
    }
    Progress::create([
      "user_id"=>$user->id,
      "grade_id"=>$grade->id,
      "video_histories"=>$data->toJson(),
      "quiz_histories"=>collect([])->toJson()
    ]);
  }

  public function handle()
  {
    $this->resetDb();
    $admin = $this->makeUser(env("DEV_EMAIL", "admin@app.com"), AppRole::ADMIN, true);
    $student = $this->makeUser(env("STUDENT_EMAIL", "student@app.com"), AppRole::SUBSCRIBER, true);
    Grade::factory()->count(1)->create()->each(function (Grade $grade){
//      $this->makeVideos($grade);
    });
    StudentGrade::create([
      "grade_id"=>Grade::all()->first()->id,
      "user_id"=>$student->id
    ]);
    return 1;
  }
}
