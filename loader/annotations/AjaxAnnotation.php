<?php namespace csvdc\router\loader\annotations;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 15/11/2019
 */

class AjaxAnnotation implements Annotation {

  private $regex = '/@ajax/i';

  function getKey(): string {
    return 'ajax';
  }

  function decode(string $text): ?string {
    if (preg_match($this->regex, $text, $matches, PREG_OFFSET_CAPTURE, 2) === 1) {
      return '1';
    }
    return null;
  }
}