<?php namespace csvdc\router\loader\module_file;
use csvdc\router\loader\annotations\RouteAnnotationParser;

/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */

class ControllerModuleFile implements ModuleFile {
  function getKey(): string {
    return 'controller';
  }

  function isType(string $filename): bool {
    return preg_match('#Controller.php$#', $filename);
  }

  function parse(string $file): array {
    $routeParser = new RouteAnnotationParser();
    $route = $routeParser->parseFile($file);
    if(empty($route['path'])) return [];
    return ['route' => [$route]];
  }


}