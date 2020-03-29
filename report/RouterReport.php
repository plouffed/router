<?php namespace csvdc\router\report;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */
use csvdc\router\Router;

interface RouterReport {
  function print(Router $router): string;
  function addInvisibleKeys($keys);
}