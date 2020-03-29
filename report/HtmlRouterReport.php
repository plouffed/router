<?php namespace csvdc\router\report;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */

use csvdc\router\Route;
use csvdc\router\Router;

class HtmlRouterReport implements RouterReport {

  private $invisibleKeys = [];
  private $printStyles;

  public function __construct($printStyles = true) {
    $this->printStyles = $printStyles;
  }

  function addInvisibleKeys($key) {
    $this->invisibleKeys = array_merge($this->invisibleKeys, (array) $key);
  }

  function print(Router $router): string {
    $routes = $router->getAllRoutes();
    $html = '';
    foreach ($routes as $route) {
      $html .= $this->convertToHtml($route);
    }
    return $this->printStyles().$html;
  }

  private function printStyles() {
    if($this->printStyles) {
      return '<style>'.file_get_contents(__DIR__.'/views/HtmlReportStyle.css').'</style>';
    }
    return '';
  }

  private function convertToHtml(Route $route): string {
    return $this->makeStartTag()
      .$this->makePath($route)
      .$this->makeProps($route)
      .$this->makeEndTag();
  }

  private function makePath(Route $route): string {
    return '<div class="path">'.$route->getFullPath().'</div>';
  }

  private function makeStartTag(): string {
    return '<div class="uri">';
  }

  private function makeEndTag(): string {
    return '</div>';
  }

  private function makeProps(Route $route): string {
    $html = '<div class="props">';
    foreach ($route->getData() as $key => $value) {
      $html .= $this->makeProp($key, $value);
    }
    $html .= $this->makeEndTag();
    return $html;
  }

  private function makeProp($key, $value): string {
    if ($this->isKeyVisible($key, $value)) {
      return $this->makePropStart().$this->makePropKey($key)
        .' = '.$this->makePropValue($value)
        .$this->makeEndTag();
    }
    return '';
  }

  private function isKeyVisible($key, $value) {
    return !in_array($key, $this->invisibleKeys) && !empty($value);
  }

  private function makePropKey($key): string {
    return "<span class='prop-key'>$key</span>";
  }

  private function makePropValue($value) {
    return "<span class='prop-value'>".implode(", ", (array)$value).'</span>';
  }

  private function makePropStart(): string {
    return "<div class='prop'>";
  }

}