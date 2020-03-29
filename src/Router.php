<?php namespace plouffed\router;

use plouffed\router\exception\RouterException;
use plouffed\router\exception\RouterExceptionCode;

class Router {

  /**
   * @var Route[]
   */
  private $routes = [];

  public function __construct($data = null) {
    if ($data) {
      if (is_array($data[0])) {
        $this->addAll($data);
      } else {
        $this->add($data);
      }
    }
  }

  function addAll($arData) {
    foreach ($arData as $data) {
      $this->add($data);
    }
  }

  /**
   * @param string|array $data
   * @return Route
   * @throws RouterException
   */
  function add($data) {
    if (is_string($data)) {
      $data = ['path' => $data];
    } else if (!isset($data['path'])) {
      throw new RouterException('$data[\'path\'] not defined.', RouterExceptionCode::DATA_PATH_UNDEFINED);
    }
    if (is_array($data['path'])) {
      foreach ($data['path'] as $path) {
        $newRoute = $data;
        $newRoute['path'] = $path;
        $route = $this->add($newRoute);
      }
      return $route;
    }
    $data['path'] = $this->getFormatedPath($data['path']);
    return $this->_add($data['path'], $data, null);
  }

  function get($path) {
    $path = $this->getFormatedPath($path);
    $parts = $this->getPathParts($path);
    $route = $this->routes[$parts[0]] ?? false;
    if (!$route) {
      throw new RouterException('Route "'.$path.'" does not exist.', RouterExceptionCode::ROUTE_PATH_DOES_NOT_EXIST);
    }
    if (isset($parts[1])) {
      return $route->getRouter()->get($parts[1]);
    } else {
      return $route;
    }
  }

  private function getFormatedPath($path) {
    $path = trim($path);
    if (strlen($path) === 0) {
      throw new RouterException("Empty path is not allowed", RouterExceptionCode::WRONG_PATH_FORMAT);
    } elseif ($path !== '/') {
      $path = trim($path, '/');
    }
    return $path;
  }

  function find($path) {
    $path = $this->getFormatedPath($path);
    $parts = $this->getPathParts($path);
    $route = $this->routes[$parts[0]] ?? $this->searchMatch($parts[0]);
    if (!$route) {
      throw new RouterException('Route "'.$path.'" was not found.', RouterExceptionCode::ROUTE_PATH_NOT_FOUND);
    }
    $isDeeper = isset($parts[1]);
    if (!$isDeeper && $route->is404()) {
      throw new RouterException('Route "'.$path.'" was found, but is not a place.', RouterExceptionCode::ROUTE_PATH_UNDEFINED);
    } elseif ($isDeeper) {
      return $route->getRouter()->find($parts[1]);
    } else {
      return $route;
    }
  }

  function getUriFromName(string $name) {
    foreach ($this->routes as $route) {
      $routeName = $route->getData('name');
      if ($routeName===$name) return '/'.$route->getFullPath();
      $routeName = $route->getRouter()->getUriFromName($name);
      if($routeName!==null) return $routeName;
    }
    return null;
  }

  /**
   * @return Route[]
   */
  function getRoutes() {
    return $this->routes;
  }

  /**
   * @return Route[]
   */
  function getAllRoutes($callback = null) {
    return $this->getAllRoutesRecursive($callback, []);
  }

  private function getAllRoutesRecursive($callback, $routes) {
    foreach ($this->routes as $route) {
      if (!$route->is404()) {
        $routes[] = $route;
        if ($callback) call_user_func($callback, $route);
      }
      $routes = $route->getRouter()->getAllRoutesRecursive($callback, $routes);
    }
    return $routes;
  }

  /**
   *
   * <p>Create the entire path.  </p>
   * <p>If a complex path is given, all missing subpath will be created.</p>
   *
   * <pre>
   * For exemple, if the given path is: "register/confirm" and "register/thanks", then the
   * route "register" will be created and "confirm" will be
   * added to the "register" router. "thanks" path will also be added to "register" router.
   *
   * We would have this structure:
   *
   * mainRouter => "register" => RegisterRouter => "confirm"
   *                          => RegisterRouter => "thanks"
   * </pre>
   *
   *
   * @param string $path
   * @param array $data
   * @param Route|null $backref
   * @return Route
   * @throws RouterException;
   */
  private function _add(&$path, &$data, $backref) {
    $parts = $this->getPathParts($path);
    $isComposedPath = isset($parts[1]);
    $newRoute = $this->routes[$parts[0]] ?? false;

    if (!$isComposedPath && $newRoute && !$newRoute->is404()) {
      throw new RouterException('Route "'.$newRoute->getFullPath().'" already exist', RouterExceptionCode::ROUTE_PATH_ALREADY_EXIST);
    } else if (!$newRoute) {
      $newRoute = $this->routes[$parts[0]] = new Route($parts[0], $backref);
      if ($isComposedPath) $newRoute->set404(true);
    }
    if ($isComposedPath) {
      $newRouter = $newRoute->getRouter();
      return $newRouter->_add($parts[1], $data, $newRoute);
    } else {
      $newRoute->set404(false);
      $newRoute->setData($data);
    }
    return $newRoute;
  }

  /**
   * if a route can not be found with $this->routes['PATH'],
   * then maybe a regex route exist for the current path.
   *
   * @param $path
   * @return bool|Route false if no route is found, or the Route of the current path
   */
  private function searchMatch($path) {
    $routes = $this->routes;
    foreach ($routes as $route) {
      if ($route->match($path)) {
        return $route;
      }
    }
    return false;
  }

  private function getPathParts($path) {
    if ($path === '/') return $path;
    $split = preg_split('#/#', $path);
    $ar[] = $split[0];
    if (isset($split[1])) {
      $ar[] = mb_substr($path, mb_strlen($split[0]) + 1);
    }
    return $ar;
  }


}