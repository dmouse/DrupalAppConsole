<?php
/**
 *@file
 * Contains \Drupal\AppConsole\Command\GeneratorEntityCommand.
 */

namespace Drupal\AppConsole\Command;

use Drupal\AppConsole\Command\ContainerAwareCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\AppConsole\Command\Helper\DialogHelper;
use Drupal\AppConsole\Generator\EntityGenerator;

class GeneratorEntityCommand extends GeneratorCommand {

  /**
   * @see Command
   */
  protected function configure() {
    $this->setDefinition([
      new InputOption('module','',InputOption::VALUE_REQUIRED, 'The name of the module'),
      new InputOption('entity_name','',InputOption::VALUE_REQUIRED, 'The name of the entity'),
      new InputOption('class_name','',InputOption::VALUE_REQUIRED, 'The class name of the entity'),
      new InputOption('fields','',InputOption::VALUE_OPTIONAL, 'Create additinal fields in an entity'),
    ])
    ->setDescription('Generate an entity')
    ->setHelp('The <info>generate:entity</info> command helps you generates new entities.')
    ->setName('generate:entity');
  }

  /**
   *
   * @param  InputInterface  $input  [description]
   * @param  OutputInterface $output [description]
   * @return [type]                  [description]
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $dialog = $this->getDialogHelper();

    if ($input->isInteractive()) {
      if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
        $output->writeln('<error>Command aborted</error>');
        return 1;
      }
    }

    $module = $input->getOption('module');
    $entity_name = $input->getOption('entity_name'));
    $class_name = $input->getOption('class_name');
    $fields = $input->getOption('fields');

    $generator = $this->getGenerator();
    $generator->generate($module, $entity_name, $class_name, $fields);

    $errors = [];

    $dialog->writeGeneratorSummary($output, $errors);
  }

  /**
   * [interact description]
   * @param  InputInterface  $input  [description]
   * @param  OutputInterface $output [description]
   * @return [type]                  [description]
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $dialog = $this->getDialogHelper();
    $dialog->writeSection($output, 'Welcome to the Drupal entity generator');

    $d = $this->getHelperSet()->get('dialog');

    // Module name
    $modules = $this->getModules();
    $module = $d->askAndValidate(
      $output,
      $dialog->getQuestion('Enter your module '),
      function($module) use ($modules){
        return Validators::validateModuleExist($module, $modules);
      },
      false,
      '',
      $modules
    );
    $input->setOption('module', $module);

    // Entity name
    $entity_name = $this->getName();
    $entity_name = $dialog->ask($output, $dialog->getQuestion('Enter the entity name', 'foo_bar'), 'foo_bar');
    $input->setOption('entity_name', $entity_name);

    // Class name
    $class_name = $this->getName();
    $class_name = $dialog->ask($output, $dialog->getQuestion('Enter the entity class name', 'FooBar'), 'FooBar');
    $input->setOption('class_name', $class_name);

    // Entity fields
    if ($dialog->askConfirmation(
      $output,
      $dialog->getQuestion('Do you like generate additional fields?', 'yes', '?'),
      true
    )) {
      $field_types = array(
        'string',
      );
      $fields = array();
      while(true){
        // Field name
        $field_name = $dialog->ask(
          $output,
          $dialog->getQuestion('  Field name','',':'),
          null
        );

        // break if is blank
        if ($field_name == null) {
          break;
        }

        // Field description
        $field_description = $dialog->ask(
          $output,
          $dialog->getQuestion('  Field description','',':'),
          null
        );

        // Field class
        $field_class = $dialog->ask(
          $output,
          $dialog->getQuestion('  Field class', '', ':'),
          null
        );

        // Field type
        $field_type = $d->askAndValidate(
          $output,
          $dialog->getQuestion('  Field type'),
          function($input) use ($field_types){
            return $input;
          },
          false,
          null,
          $field_types
        );

        array_push($fields, array(
          'name'        => $field_name,
          'description' => $field_description,
          'type'        => $field_type,
          'class'       => $field_class,
        ));
      }
      $input->setOption('fields', $fields);
    }
  }

  /**
  * Get a filesystem
  * @return [type] Drupal Filesystem
  */
  protected function createGenerator() {
    return new EntityGenerator();
  }
}
