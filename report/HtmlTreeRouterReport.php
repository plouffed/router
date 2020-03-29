<?php namespace csvdc\router\report;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */

use csvdc\router\Route;
use csvdc\router\Router;

class HtmlTreeRouterReport implements RouterReport {

  private const propController = 'controller';
  private const propPath = 'path';

  private $invisibleKeys = [];
  private $printStyles;
  private static $routeCounter = 0;

  public function __construct($printStyles=true) {
    $this->printStyles = $printStyles;
  }

  function addInvisibleKeys($keys) {
    $this->invisibleKeys = array_merge($this->invisibleKeys, (array) $keys);
  }

  function print(Router $router): string {
    return $this->printStyles()
      .$this->wrapHtml('router',
        $this->makeHeader($router).
        $this->wrapHtml('routes',
          $this->convertRoutesToHtml($router->getRoutes())
        )
      );
  }

  private function wrapHtml($classname, $html) {
    return '<div class="'.$classname.'">'.$html.'</div>';
  }

  private function makeHeader($router) {
    return '<h4 class="router-header">Liste des routes existantes ('.count($router->getAllRoutes()).')</h4>';
  }

  private function printStyles() {
    if($this->printStyles) {
      return '<style>'.file_get_contents(__DIR__.'/views/HtmlTreeReportStyle.css').'</style>';
    }
    return '';
  }

  private function convertRoutesToHtml($routes) {
    $html = '';
    if($routes) {
      ksort($routes);
      foreach ($routes as $route) {
        /** @var Route $route */
        $html .= '<div class="toggle">';
        $html .= '<input id="id'.(self::$routeCounter).'" type="checkbox" aria-hidden="true"/>';
        $html .= $this->convertToHtml($route);
        $html .='<section>';
        $html .= $this->convertRoutesToHtml($route->getRouter()->getRoutes());
        $html .='</section>';
        $html.=$this->makeEndTag();
      }
    }
    return $html;
  }


  private function convertToHtml(Route $route): string {
    return $this->makePath($route)
      .$this->makeProps($route);
  }

  private function makePath(Route $route): string {
    $class = 'path';
    if($route->is404()) $class .= ' is404';
    if($route->hasChild()) {
      return $this->makeLabel($route, $class);
    } else {
      self::$routeCounter++;
      return '<div class="'.$class.'">'
        .$route->getPath()
        .$this->makeAction($route)
        .'</div>';
    }
  }

  private function makeLabel(Route $route, string $class): string {
    return '<label for="id'.(self::$routeCounter++).'" class="'
      .$class.'">'
      .$route->getPath()
      .$this->makeAction($route)
      .'</label>';
  }

  private function makeAction(Route $route) {
    return '<span class="controller">'
      .$this->makeSpaceMinusPathLength($route)
      .$route->getData(self::propController)
      .'</span>';
  }

  private function makeSpaceMinusPathLength(Route $route) {
    $n = 5;//-mb_strlen($route->getPath());
    $spaces = '';
    for($i=0; $i<$n; $i++) {
      $spaces.='&nbsp;';
    }
    return $spaces;
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
      if(!in_array($key,[self::propController, self::propPath]) ) {
        $html .= $this->makeProp($key, $value);
      }
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