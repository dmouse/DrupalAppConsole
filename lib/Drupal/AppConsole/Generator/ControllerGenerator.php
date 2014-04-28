<?php

namespace Drupal\AppConsole\Generator;

use Symfony\Component\DependencyInjection\Container;

class ControllerGenerator extends Generator {

  private $filesystem;

  public function __construct() {}

  public function generate($module, $name, $path, $services, $test ) {
    $path_controller = $path . '/lib/Drupal/' . $module . '/Controller';

    $parameters = array(
      'name' => $name,
      'services' => $services,
      'module' => $module
    );

    $this->renderFile(
      'module/module.controller.php.twig',
      $path_controller . '/'. $name .'.php',
      $parameters
    );

    $this->renderFile('module/controller-routing.yml.twig', $path .'/'. $module .'.routing.yml', $parameters, FILE_APPEND);

    if ($test){
      $this->renderFile(
          'module/module.test.twig',
          $path . '/lib/Drupal/' . $module . '/Tests/' . $name . 'Test.php',
          $parameters
      );
    }
  }

}
