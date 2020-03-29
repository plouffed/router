<?php namespace plouffed\router\loader\annotations;

class MethodAnnotation implements Annotation {

  private $regex = '/@method\("(GET|POST|PUT|DELETE|OPTIONS)"\)/';

  function getKey(): string {
    return 'method';
  }

  function decode(string $text): ?string {
    if (preg_match($this->regex, $text, $matches, PREG_OFFSET_CAPTURE, 2) === 1) {
      return $matches[1][0];
    }
    return null;
  }
}