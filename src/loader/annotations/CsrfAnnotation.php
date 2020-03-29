<?php namespace plouffed\router\loader\annotations;

class CsrfAnnotation implements Annotation {

  private $regex = '/@csrf/i';

  function getKey(): string {
    return 'csrf';
  }

  function decode(string $text): ?string {
    if (preg_match($this->regex, $text, $matches, PREG_OFFSET_CAPTURE, 2) === 1) {
      return '1';
    }
    return null;
  }
}