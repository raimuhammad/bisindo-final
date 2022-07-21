<?php

namespace App\GraphQL\Directives;

use Closure;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class HasFileDirective extends BaseDirective implements FieldMiddleware
{
  public function handleField(FieldValue $fieldValue, Closure $next)
  {
    $resolver = $fieldValue->getResolver();
    $keyname = $this->directiveArgValue("key", "file");
    $callback = $this->directiveArgValue("resolver");
    [$class, $method] = explode("@", $callback);
    if (! class_exists($class)){
      throw new \Error($class. " is not exists");
    }
    $instance = new $class();

    if (! method_exists($instance, $method)){
      throw new \Error("method $method in $class is not exists");
    }

    $fieldValue->setResolver(function ($root, array &$args, $context, $resolveInfo) use ($resolver, $keyname, $method) {
      $file = $args[$keyname];
      $newArgs = Arr::except($args, $keyname);
      unset($resolveInfo->argumentSet->arguments[$keyname]);
      /**
       * @var Model $result
       */
      $result = $resolver($root, $newArgs, $context, $resolveInfo);
      $result->$method($file);
      return $result;
    });
    return $fieldValue;
  }

  public static function definition(): string
  {
    return /** @lang GraphQL */ <<<'GRAPHQL'
directive @hasFile(
  key: String
  resolver: String!
) on FIELD_DEFINITION
GRAPHQL;
  }
}
