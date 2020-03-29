<?php namespace plouffed\router\loader\module_file;

interface ModuleFile {
  function getKey(): string;
  function isType(string $filename): bool;

  /**
   * L'array peut être de deux types uniquement
   * un array "file" indique qu'il faut inclure la donnée
   * dans la ou les routes selon la hierarchie des fichiers.
   *
   * Les types par prise en compte par le framework
   * sont 'gates' et 'validator'.
   *
   * 1. ['file' => ['type' => 'valeur']]
   *
   * L'array représente une route
   *
   * 2. ['route' => ['path', ...]]
   *
   */
  function parse(string $file): array;
}