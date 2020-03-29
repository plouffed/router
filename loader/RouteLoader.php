<?php namespace csvdc\router\loader;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */
interface RouteLoader {
    function __construct(String $modulePath);
    function getRoutes();
}