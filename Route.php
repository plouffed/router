<?php namespace csvdc\router;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */
class Route {

  private $data;
  private $isRegex = false;
  private $is404 = false;
  private $matches = [];

  /** @var Router */
  private $router;

  /** @var Route */
  private $backref;

  /**
   * Route constructor.
   * @param string|array $data
   * @param null $backref
   */
  function __construct($data, $backref = null) {
    if (is_string($data)) $data = ['path' => $data];
    $this->data = $data;
    $this->backref = $backref;
    $this->setIsRegex();
  }

  function hasChild() {
    return isset($this->router) && !empty($this->router->getRoutes());
  }

  function set404($is404) {
    $this->is404 = $is404;
  }

  function is404() {
    return $this->is404;
  }

  function isRegex() {
    return $this->isRegex;
  }

  function getPathParts($nbOfParts = 1) {
    $backRoute = $this->backref;
    $parts[] = $this->data['path'];
    for ($i = 0; $i < $nbOfParts && $backRoute !== null; $i++) {
      $parts[] = $backRoute->getPath();
      $backRoute = $backRoute->backref;
    }
    return $parts;
  }

  /**
   * @param int $nPart specific path from the reverse direction: $nPath[2]/$nPath[1]/$nPath[0]
   * @return mixed|string
   */
  function getPath($nPart = 0) {
    if ($nPart === 0) return $this->data['path'];
    $backRoute = $this->backref;
    $path = '';
    for ($i = 0; $i < $nPart && $backRoute !== null; $i++) {
      $path = $backRoute->getPath();
      $backRoute = $backRoute->backref;
    }
    return $path;
  }

  function getFullPath() {
    $path = $this->data['path'];
    $backRoute = $this->backref;
    while ($backRoute !== null) {
      $path = $backRoute->getPath().'/'.$path;
      $backRoute = $backRoute->backref;
    }
    return $path;
  }

  function getData($key = null) {
    if (isset($key)) {
      return $this->data[$key] ?? null;
    } else {
      return $this->data;
    }
  }

  /**
   * @param array $data
   */
  function setData($data) {
    $oldPath = $this->data['path'];
    $this->data = $data;
    $this->data['path'] = $oldPath;
  }

  public function getRouter() {
    if (!$this->router) {
      $this->router = new Router();
    }
    return $this->router;
  }

  public function match($path) {
    return $this->isRegex() && preg_match($this->getPath(), $path, $this->matches);
  }

  public function getFirstNotRegexRoute() {
    if ($this->isRegex) {
      $backRoute = $this->backref;
      while ($backRoute !== null) {
        if (!$backRoute->isRegex) {
          return $backRoute;
        }
      }
    }
    return $this;
  }

  function getMatches() {
    $matches = $this->matches;
    if (empty($matches)) return [];
    $backRoute = $this->backref;
    while ($backRoute !== null) {
      $matches = array_merge($backRoute->matches, $matches);
      $backRoute = $backRoute->backref;
    }
    return $matches;
  }

  private function setIsRegex() {
    $path = $this->getPath();
    $this->isRegex = preg_match('/[\[\(.+*]/u', $this->getPath()) === 1;
  }

  public function __toString() {
    return $this->getFullPath();
  }

}