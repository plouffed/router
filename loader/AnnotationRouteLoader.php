<?php namespace csvdc\router\loader;

use csvdc\router\loader\module_file\ControllerModuleFile;
use csvdc\router\loader\module_file\ModuleFile;

/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */
class AnnotationRouteLoader implements RouteLoader {

  /** @var ModuleFile[] $moduleFile */
  private $moduleFiles = [];
  private $routes = [];

  function __construct(String $modulePath) {
    $this->setDefaultModuleFiles();
    $this->routes = $this->scanDirectoriesRecursively($modulePath, 0)['route'] ?? [];
  }

  private function setModuleFiles(array $moduleFiles) {
    $this->moduleFiles = $moduleFiles;
  }

  private function setDefaultModuleFiles(): void {
    $this->moduleFiles[] = new ControllerModuleFile();
  }

  function getRoutes() {
    return $this->routes;
  }

  /**
   * parcours l'ensemble des fichiers récursivement afin de
   * mettre en relation les fichiers "Action", "Gatekeeper" et "Validator".
   *
   * Chaque action représentera alors une Route, qui
   * peut posséder 1 ou plusieurs "Gatekeeper" et "Validator".
   *
   */
  private function scanDirectoriesRecursively($dir, $dept) {
    $directoryItems = scandir($dir);
    $nDir = count($directoryItems);
    $result = [];

    for ($i = 2; $i < $nDir; $i++) {
      $item = $directoryItems[$i];
      $fullPath = $dir.'/'.$item;
      if (is_dir($fullPath)) {
        $result = array_merge_recursive($result,
          $this->scanDirectoriesRecursively($fullPath, $dept + 1));
      } else {
        $result = $this->analyseFile($result, $fullPath, $item);
      }
    }
    $files = $result['file']??[];
    unset($result['file']);

    $result = $this->mergeFilesIntoRoutes($result, $files);
    return $result;
  }

  private function analyseFile($result, $fullPath, $item) {
    foreach ($this->moduleFiles as $moduleFile) {
      if ($moduleFile->isType($item)) {
        return array_merge_recursive($result, $moduleFile->parse($fullPath));
      }
    }
    return $result;
  }

  private function mergeFilesIntoRoutes($result, $files) {
    if(empty($result)) return $result;
    foreach ($result['route'] as &$route) {
      foreach ($files as $key => $value) {
          $route[$key] = array_merge_recursive($value,$route[$key]??[]);
      }
    }
    return $result;
  }

}