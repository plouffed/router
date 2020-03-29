<?php namespace plouffed\router\loader\annotations;

class RouteAnnotation implements Annotation {

  private $regexSingleRoute = '/@route\("(.+)"\)/i';
  //private $regexRoutes = '/@routes\((.+)\)/i';
  //private $regexRoute = '/"(.+?)"/m';

  function getKey(): string {
    return 'path';
  }

  function decode(string $text): ?string {
    //if ($routeText = $this->extractRouteText($text)) {
    //  return $this->extractRoutes($routeText);
    //}
    return $this->extractSingleRoutePath($text);
  }

  private function extractSingleRoutePath($text) {
    if(preg_match($this->regexSingleRoute, $text, $path, PREG_OFFSET_CAPTURE, 2) === 1)
      return $path[1][0];
    return null;
  }

  private function extractRouteText($text) {
    if(preg_match($this->regexRoutes, $text, $routes, PREG_OFFSET_CAPTURE, 2) === 1)
      return $routes[1][0];
    return null;
  }

  private function extractRoutes($routeText) {
    if(preg_match_all($this->regexRoute, $routeText, $routes) !== false) {
      return $routes[1];
    }
    return null;
  }
}