<?php namespace plouffed\router\loader;

interface RouteLoader {
    function __construct(String $modulePath);
    function getRoutes();
}