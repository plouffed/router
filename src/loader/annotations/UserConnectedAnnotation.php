<?php namespace plouffed\router\loader\annotations;

class UserConnectedAnnotation implements Annotation {

  private $regex = '/@user-connected/i';

  function getKey(): string {
    return 'user-connected';
  }

  function decode(string $text): ?string {
    if (preg_match($this->regex, $text, $matches, PREG_OFFSET_CAPTURE, 2) === 1) {
      return '1';
    }
    return null;
  }
}