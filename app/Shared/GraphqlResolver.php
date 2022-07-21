<?php


namespace App\Shared;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class GraphqlResolver
{
  protected array $modelArguments = [];
  protected array $additionalArguments = [];
  protected Model $model;

  abstract public function getExcluded(array $array) : array;
  abstract public function makeModel() : Model;

  protected function customModelUpdate(array $tupleArgs){
    foreach ($tupleArgs as $loop){
      [$key, $method] = $loop;
      if (isset($this->additionalArguments[$key]) && $this->additionalArguments[$key]){
        $this->model->$method($this->additionalArguments[$key]);
      }
    }
  }

  protected function afterCreate(){

  }
  protected function afterUpdate(){

  }

  protected function excludedNullValues(array $arguments){
    $arg = [];
    foreach ($arguments as $key => $value){
      if ($value !== null){
        $arg[$key] = $value;
      }
    }
    return $arg;
  }

  protected function transformArguments(array $arguments){
    return $arguments;
  }

  protected function setArguments(array $arguments){
    $excluded = array_merge($this->getExcluded($arguments), ["directive"]);
    $nonNull = $this->excludedNullValues(Arr::except($arguments, $excluded));
    $this->modelArguments = $this->transformArguments($nonNull);
    $this->additionalArguments = $this->excludedNullValues(Arr::only($arguments,$this->getExcluded($arguments)));
  }

  public function create($_, array $args, $ctx = null, $fieldInfo = null){
    $this->setArguments($args);
    $this->model = $this->makeModel();
    $this->afterCreate();
    return $this->model;
  }
  public function update($_, array $args){
    $this->setArguments($args);
    $this->model = $this->makeModel();
    $this->model->update($this->modelArguments);
    $this->afterUpdate();
    $this->model->refresh();
    return $this->model;
  }
}
