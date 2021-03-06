<?php namespace plouffed\router\loader\annotations;

class NameAnnotation implements Annotation {

  private $regex = '/@name\("([a-z-_ ]+)"\)/i';

  function getKey(): string {
    return 'name';
  }

  function decode(string $text): ?string {
    if (preg_match($this->regex, $text, $matches, PREG_OFFSET_CAPTURE, 2) === 1) {
      return $matches[1][0];
    }
    return null;
  }
}