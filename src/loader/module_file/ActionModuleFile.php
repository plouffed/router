<?php namespace plouffed\router\loader\module_file;
use plouffed\router\loader\annotations\RouteAnnotationParser;

class ActionModuleFile implements ModuleFile {
  const PHP_EXTENSION_LENGTH = 4;

  function getKey(): string {
    return 'action';
  }

  function isType(string $filename): bool {
    return preg_match('#Action.php$#', $filename);
  }

  function parse(string $file): array {
    $routeParser = new RouteAnnotationParser();
    $route = $routeParser->parseFile($file);
    if(empty($route['path'])) {
      return [];
      //$route['path'] = $this->getRoutePathFromFilename($routeParser->getFilename());
    }
    return ['route' => [$route]];
  }

  function getRoutePathFromFilename($filename) {
    return strtolower(substr($filename, 0,
      mb_strlen($filename) - (mb_strlen($this->getKey())+self::PHP_EXTENSION_LENGTH)));
  }

}