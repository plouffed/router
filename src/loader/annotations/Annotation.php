<?php namespace plouffed\router\loader\annotations;

interface Annotation {
  function getKey(): string;
  function decode(string $str): ?string;
}