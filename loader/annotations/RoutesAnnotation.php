<?php namespace csvdc\router\loader\annotations;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */

class RoutesAnnotation implements Annotation {

  private $regexRoutes = '/@routes\((.+)\)/i';
  private $regexRoute = '/"(.+?)"/g';

  function getKey(): string {
    return 'path';
  }

  function decode(string $text): ?string {
    if (preg_match($this->regexRoutes, $text, $matches, PREG_OFFSET_CAPTURE, 2) === 1) {
      preg_match_all($this->regexRoute, $matches[1][0], $routes);
      return $routes;
    }
    return null;
  }
}