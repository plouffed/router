<?php namespace csvdc\router\loader\annotations;
/**
 * Created by daveplouffe@csvdc.qc.ca
 * Date: 28/10/2019
 */

class RouteAnnotationParser {

  private $routeAnnotation;
  private $annotations = [];
  private $annotationKeyValuePair = [];
  private $namespace;
  private $classname;
  private $numberOfAnnotations;
  private $fileInfo;

  public function __construct(array $annotations = []) {
    if(empty($annotations)){
      $this->setDefaultAnnotations();
    } else {
      $this->annotations = $annotations;
    }
    $this->numberOfAnnotations = count($this->annotations);
    $this->routeAnnotation = new RouteAnnotation();
  }

  private function setDefaultAnnotations(): void {
    $this->annotations[] = new NameAnnotation();
    $this->annotations[] = new MethodAnnotation();
    $this->annotations[] = new AjaxAnnotation();
    $this->annotations[] = new CsrfAnnotation();
    $this->annotations[] = new UserConnectedAnnotation();
  }

  public function getFileClassname($file) {
    $tokens = token_get_all(file_get_contents($file));
    $n = count($tokens);
    $this->annotationKeyValuePair = [];
    $offset = 1;
    $this->findNameSpace($tokens, $n, $offset);
    $this->findClassname($tokens, $n, $offset);

    return $this->namespace.'\\'.$this->classname;
  }

  public function getFilename() {
    return $this->fileInfo->getFilename();
  }

  public function parseFile(string $file): array {
    $this->fileInfo = new \SplFileInfo($file);
    $tokens = token_get_all(file_get_contents($this->fileInfo->getPathName()));
    $n = count($tokens);
    $this->annotationKeyValuePair = [];
    $offset = 1;
    $offset = $this->findNameSpace($tokens, $n, $offset);
    $offset = $this->findAnnotation($tokens, $n, $offset);
    $this->findClassname($tokens, $n, $offset);
    $this->normalizeRoute();
    return array_merge($this->defaultValues(),$this->annotationKeyValuePair);
  }

  private function defaultValues() {
    return [
      'controller' => $this->classname,
      'path' => ''
    ];
  }

  private function findNameSpace(array $tokens, int $n, int $offset): int {
    $ns = '';
    for ($i = $offset; $i < $n; $i++) {
      if ($tokens[$i][0] === T_NAMESPACE) {
        for ($i += 2; $i < $n; $i++) {
          if ($tokens[$i] === ';') {
            $this->namespace = $ns;
            return $i + 1;
          }
          $ns .= trim($tokens[$i][1] ?? '');
        }
      }
    }
    if($ns === '') return $offset;
    $this->namespace = $ns;
    return $i + 1;
  }

  private function findAnnotation(array $tokens, int $n, int $offset): int {
    for ($i = $offset; $i < $n; $i++) {
      $token = $tokens[$i];
      if ($token[0] === T_DOC_COMMENT) {
        if ($this->decodeAnotations($token[1])) {
          return $i;
        }
      }
    }
    return $offset;
  }

  private function decodeAnotations($token): bool {
    $n = $this->numberOfAnnotations;
    if(!$this->decodeAnnotation($this->routeAnnotation, $token)) return false;
    for ($i = 0; $i < $n; $i++) {
      $this->decodeAnnotation($this->annotations[$i], $token);
    }
    return true;
  }

  private function decodeAnnotation(Annotation $annotation, $text) {
    $value = $annotation->decode($text);
    if (null !== $value) {
      $this->annotationKeyValuePair[$annotation->getKey()] = $value;
      return true;
    }
    return false;
  }

  private function findClassname(array $tokens, int $n, int $offset): int {
    for ($i = $offset; $i < $n; $i++) {
      if ($tokens[$i][0] === T_CLASS) {
        $i += 2;
        $this->classname = $tokens[$i][1];
        return $i;
      }
    }
    return $offset;
  }

  private function normalizeRoute() {
    $this->classname = $this->namespace.'\\'.$this->classname;
    unset($this->namespace);
  }

}