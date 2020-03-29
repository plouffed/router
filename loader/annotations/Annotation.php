<?php namespace csvdc\router\loader\annotations;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */

interface Annotation {
  function getKey(): string;
  function decode(string $str): ?string;
}