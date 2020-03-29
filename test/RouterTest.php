<?php

use plouffed\router\exception\RouterException;
use plouffed\router\exception\RouterExceptionCode;
use PHPUnit\Framework\TestCase;
use plouffed\router\Router;

class RouterTest extends TestCase {

  function testRouterAdd() {
    $router = new Router();
    $route = $router->add('/account/confirm');
    self::assertEquals('account/confirm', $route->getFullPath());
  }

  function testRouterRegexDetection() {
    $router = new Router();
    $route = $router->add('/(.+)');
    self::assertEquals(true, $route->isRegex());
  }

  function test_router_regex_with_path_equal_to_zero__should_work() {
    $router = new Router();
    $route = $router->add('/([0-9]+)');
    self::assertEquals(true, $route->isRegex());
    self::assertEquals($route, $router->find('0'));
  }

  function testRouterGetPath() {
    $router = new Router();
    $router->add('forum');
    $router->add('forum/(\d+)');
    $router->add('register');
    $router->add('register/confirm');

    $route = $router->get('register/confirm');
    self::assertEquals('confirm', $route->getPath());
    self::assertFalse($route->is404());
  }

  function restRouterFindSimplePath() {
    $router = new Router();
    $router->add('forum');
    $router->add('forum/(\d+)');
    $router->add('register');
    $router->add('register/confirm');

    $route = $router->find('register/confirm');
    self::assertEquals('register/confirm', $route->getFullPath());
  }

  function testRouterFindRegexPath() {
    $router = new Router();
    $router->add('forum');
    $router->add('forum/(\d+)');
    $router->add('register');
    $router->add('register/confirm');

    $route = $router->find('forum/345');
    self::assertEquals('forum/(\d+)', $route->getFullPath());
  }

  function testRouterConstructorStringInput() {
    $router = new Router('forum/topic');
    self::assertEquals('forum/topic', $router->get('forum/topic')->getFullPath());
    self::assertTrue($router->get('forum')->is404());
  }

  function testRouterContructorArrayInput() {
    $router = new Router([
      ['path' => 'account'],
      ['path' => 'register/confirm'],
      ['path' => 'forum/topic']
    ]);
    self::assertEquals('forum/topic', $router->get('forum/topic')->getFullPath());
  }

  function testRouterAddReverseHierarchy() {
    $router = new Router('register/confirm');
    $router->add('register/thanks');
    $route = $router->add('register');
    self::assertEquals('register', $route->getFullPath());
    self::assertFalse($route->is404());
  }

  /**
   * @expectException RouterException
   * @expectExceptionCode 0
   */
  function testRouterPathWrongFormat() {
    $this->expectException(RouterException::class);
    $this->expectExceptionCode(RouterExceptionCode::WRONG_PATH_FORMAT);
    $router = new Router();
    $router->add(' ');
  }

  function testRouterPathNotSpecifiedError() {
    $this->expectException(RouterException::class);
    $this->expectExceptionCode(RouterExceptionCode::DATA_PATH_UNDEFINED);
    $router = new Router();
    $router->add(['forum']);
  }

  function testRouterPathDoesNotExist() {
    $this->expectException(RouterException::class);
    $this->expectExceptionCode(RouterExceptionCode::ROUTE_PATH_DOES_NOT_EXIST);
    $router = new Router();
    $router->get('not/real/path');
  }

  function testRouterFindNotExistError() {
    $this->expectException(RouterException::class);
    $this->expectExceptionCode(RouterExceptionCode::ROUTE_PATH_NOT_FOUND);
    $router = new Router();
    $router->add('forum');
    $router->add('forum/(\d+)');
    $router->add('register');
    $router->add('register/confirm');

    $router->find('register/confirm/test');
  }

  function testRouterFindNotExistError2() {
    $this->expectException(RouterException::class);
    $this->expectExceptionCode(RouterExceptionCode::ROUTE_PATH_NOT_FOUND);
    $router = new Router();
    $router->find('not/real/path');
  }

  function testRouterFindUndefinedRoute() {
    $this->expectException(RouterException::class);
    $this->expectExceptionCode(RouterExceptionCode::ROUTE_PATH_UNDEFINED);
    $router = new Router('/register/confirm');
    $router->find('register');
  }

  function testRouterAddStringPathDoublonError() {
    $this->expectException(RouterException::class);
    $this->expectExceptionCode(RouterExceptionCode::ROUTE_PATH_ALREADY_EXIST);
    $router = new Router();
    $router->add('/account');
    $router->add([
      'path' => '/account',
      'controller' => 'Account'
    ]);
  }

  function testRouterAddRegexPathDoublonError() {
    $this->expectException(RouterException::class);
    $this->expectExceptionCode(RouterExceptionCode::ROUTE_PATH_ALREADY_EXIST);
    $router = new Router();
    $router->add('/account/(\d+)');
    $router->add([
      'path' => 'account/(\d+)',
      'controller' => 'Account'
    ]);
  }

}