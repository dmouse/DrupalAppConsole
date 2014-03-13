<?php
/**
 *@file
 * Contains \Drupal\AppConsole\Generator\EntityGenerator.
 */

namespace Drupal\AppConsole\Generator;

use Symfony\Component\DependencyInjection\Container;

class EntityGenerator extends Generator {

  public function __construct() {}

  public function generate($module, $entity_name, $class_name, $fields) {

    $path = DRUPAL_ROOT . '/' . drupal_get_path('module', $module);
    
    $path_entity_interface = $path . '/lib/Drupal/' . $module;
    $path_entity = $path . '/lib/Drupal/' . $module . '/Entity';
    $path_entity_controller = $path . '/lib/Drupal/' . $module . '/Entity/Controller';
    $path_entity_form = $path . '/lib/Drupal/' . $module . '/Entity/Form';

    $parameters = array(
      'module' => $module,
      'entity_name' => $entity_name,
      'class_name' => $class_name,
      'fields' => $fields,
    );

    drupal_mkdir($path_entity_interface);
    drupal_mkdir($path_entity);
    drupal_mkdir($path_entity_controller);
    drupal_mkdir($path_entity_form);

    $this->renderFile(
      'entity/entity.interface.php.twig',
      $path_entity_interface . '/'. $class_name .'Interface.php',
      $parameters
    );

    $this->renderFile(
      'entity/entity.php.twig',
      $path_entity . '/'. $class_name .'.php',
      $parameters
    );

    $this->renderFile(
      'entity/entity.controller.listcontroller.php.twig',
      $path_entity_controller . '/'. $class_name .'ListController.php',
      $parameters
    );

    $this->renderFile(
      'entity/entity.form.deleteform.php.twig',
      $path_entity_form . '/'. $class_name .'DeleteForm.php',
      $parameters
    );

    $this->renderFile(
      'entity/entity.form.formcontroller.php.twig',
      $path_entity_form . '/'. $class_name .'FormController.php',
      $parameters
    );
  }
}
