<?php
/**
 *@file
 * Contains \Drupal\AppConsole\Generator\EntityGenerator.
 */
namespace Drupal\AppConsole\Generator;

define('DIR_UP', '..' . DIRECTORY_SEPARATOR);
require realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . DIR_UP . DIR_UP . DIR_UP . DIR_UP . '/vendor/nikic/php-parser/lib/bootstrap.php');

use Symfony\Component\DependencyInjection\Container;

class EntityGenerator extends Generator {

  public function __construct() {}

  public function generate($module, $entity_label, $entity_name, $entity_identifier, $class_name, $fields) {

    $path = DRUPAL_ROOT . '/' . drupal_get_path('module', $module);
    
    $path_entity_interface = $path . '/lib/Drupal/' . $module;
    $path_entity = $path . '/lib/Drupal/' . $module . '/Entity';
    $path_entity_controller = $path . '/lib/Drupal/' . $module . '/Entity/Controller';
    $path_entity_form = $path . '/lib/Drupal/' . $module . '/Entity/Form';

    $parameters = array(
      'module' => $module,
      'entity_label' => $entity_label,
      'entity_name' => $entity_name,
      'entity_identifier' => $entity_identifier,
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

    $this->renderFile(
      'entity/entity.local_actions.yml.twig',
      DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.local_actions.yml',
      $parameters,
      FILE_APPEND
    );

    $this->renderFile(
      'entity/entity.local_actions.yml.twig',
      DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.local_actions.yml',
      $parameters,
      FILE_APPEND
    );

    $this->renderFile(
      'entity/entity.local_tasks.yml.twig',
      DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.local_tasks.yml',
      $parameters,
      FILE_APPEND
    );

    $this->renderFile(
      'entity/entity.routing.yml.twig',
      DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.routing.yml',
      $parameters,
      FILE_APPEND
    );


    $parameters['schema'] = $this->render(
      'entity/entity.schema.php.twig',
      $parameters
    );

    $parameters['menu_links_defaults'] = $this->render(
      'entity/entity.menu_links_defaults.php.twig',
      $parameters
    );



    if (file_exists(DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.install')) {
      $code = file_get_contents(DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.install');

      $parser = new \PHPParser_Parser(new \PHPParser_Lexer);
      $traverser     = new \PHPParser_NodeTraverser;
      $prettyPrinter = new PHPParser_PrettyPrinter_Drupal;

      try {
        $code_stmts = $parser->parse($code);
        $schema_stmts = $parser->parse($parameters['schema']);

        // add your visitor
        $traverser->addVisitor(new StmtNodeVisitor($parameters, $module . '_schema', $schema_stmts));
        $code_stmts = $traverser->traverse($code_stmts);

        $code = '<?php ' . $prettyPrinter->prettyPrint($code_stmts);
        file_put_contents(DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.install', $code);

      } catch (\PhpParser_Error $e) {
        echo 'Parse Error: ', $e->getMessage();
      }
    } else {
      $this->renderFile(
        'entity/entity.install.php.twig',
        DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.install',
        $parameters
      );
    }

    if (file_exists(DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.module')) {
      $code = file_get_contents(DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.module');

      $parser = new \PHPParser_Parser(new \PHPParser_Lexer);
      $traverser     = new \PHPParser_NodeTraverser;
      $prettyPrinter = new PHPParser_PrettyPrinter_Drupal;

      try {
        $code_stmts = $parser->parse($code);
        $menu_links_defaults_stmts = $parser->parse($parameters['menu_links_defaults']);

        // add your visitor
        $traverser->addVisitor(new StmtNodeVisitor($parameters, $module . '_menu_links_defaults', $schema_stmts));
        $code_stmts = $traverser->traverse($code_stmts);

        $code = '<?php ' . $prettyPrinter->prettyPrint($code_stmts);
        file_put_contents(DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.module', $code);

      } catch (\PhpParser_Error $e) {
        echo 'Parse Error: ', $e->getMessage();
      }
    } else {
      $this->renderFile(
        'entity/entity.module.twig',
        DRUPAL_ROOT.'/modules/'.$module.'/'.$module.'.module',
        $parameters
      );
    }
  }
}

class StmtNodeVisitor extends \PHPParser_NodeVisitorAbstract
{
  protected $parameters;
  protected $name;
  protected $stmts;

  public function __construct(array $parameters, $name, $stmts ) {
    $this->parameters = $parameters;
    $this->name = $name;
    $this->stmts = $stmts;
  }
  public function beforeTraverse(array $nodes) {
  }
  public function enterNode(\PHPParser_Node $node) {
  }
  public function afterTraverse(array $nodes) {
  }
  
  public function leaveNode(\PHPParser_Node $node) {
    if ($node instanceof \PHPParser_Node_Stmt_Function && $node->name == $this->name) {
      $node->stmts = array_merge($this->stmts, $node->stmts);
    }
  }
}

class PHPParser_PrettyPrinter_Drupal extends \PHPParser_PrettyPrinter_Default
{
  public function pExpr_Array(\PHPParser_Node_Expr_Array $node) {
    return "array(\n" . $this->drupalCommaSeparated($node->items) . "\n)";
  }

  /**
   * Pretty prints an array of nodes (statements) and indents them optionally.
   *
   * @param PHPParser_Node[] $nodes  Array of nodes
   * @param bool             $indent Whether to indent the printed nodes
   *
   * @return string Pretty printed statements
   */
  protected function drupalStmts(array $nodes, $indent = true) {
      $pNodes = array();
      foreach ($nodes as $node) {
          $pNodes[] = $this->pComments($node->getAttribute('comments', array()))
                    . $this->p($node)
                    . ($node instanceof \PHPParser_Node_Expr ? ';' : '');
      }

      if ($indent) {
          return '  ' . preg_replace(
              '~\n(?!$|' . $this->noIndentToken . ')~',
              "\n" . '  ',
              implode("\n", $pNodes)
          );
      } else {
          return implode("\n", $pNodes);
      }
  }

  /**
   * Pretty prints an array of nodes and implodes the printed values with commas.
   *
   * @param PHPParser_Node[] $nodes Array of Nodes to be printed
   *
   * @return string Comma separated pretty printed nodes
   */
  protected function drupalCommaSeparated(array $nodes, $indent = true) {
      if ($indent) {
          return '  ' . preg_replace(
              '~\n(?!$|' . $this->noIndentToken . ')~',
              "\n" . '  ',
              $this->pImplode($nodes, ",\n")
          );
      } else {
          return $this->pImplode($nodes, ', ');
      }
  }

  public function pStmt_Function(\PHPParser_Node_Stmt_Function $node) {
      return 'function ' . ($node->byRef ? '&' : '') . $node->name
           . '(' . $this->pCommaSeparated($node->params) . ')'
           . " " . '{' . "\n" . $this->drupalStmts($node->stmts) . "\n" . '}';
  }
}
