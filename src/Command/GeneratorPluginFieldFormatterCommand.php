<?php
/**
 * @file
 * Contains \Drupal\AppConsole\Command\GeneratorPluginFieldFormatterCommand.
 */

namespace Drupal\AppConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\AppConsole\Generator\PluginFieldFormatterGenerator;
use Drupal\AppConsole\Command\Helper\ModuleTrait;
use Drupal\AppConsole\Command\Helper\FormTrait;

class GeneratorPluginFieldFormatterCommand extends GeneratorCommand
{
  use ModuleTrait;
  use FormTrait;

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
    $this
      ->setDefinition(array(
        new InputOption('module','',InputOption::VALUE_REQUIRED, 'The name of the module'),
        new InputOption('class-name','',InputOption::VALUE_OPTIONAL, 'Plugin field formatter class'),
        new InputOption('plugin-label','',InputOption::VALUE_OPTIONAL, 'Plugin Label'),
        new InputOption('plugin-id','',InputOption::VALUE_OPTIONAL, 'Plugin id'),
        new InputOption('plugin-form','',InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Plugin id'),
      ))
      ->setDescription('Generate plugin block')
      ->setHelp('The <info>generate:plugin:field:formatter</info> command helps you generate a new field formatter')
      ->setName('generate:plugin:field:formatter');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $dialog = $this->getDialogHelper();

    if ($input->isInteractive()) {
      if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
        $output->writeln('<error>Command aborted</error>');
        return 1;
      }
    }

    $module = $input->getOption('module');
    $class_name = $input->getOption('class-name');
    $plugin_label = $input->getOption('plugin-label');
    $plugin_id = $input->getOption('plugin-id');
    $inputs = $input->getOption('plugin-form');

    $this
      ->getGenerator()
      ->generate($module, $class_name, $plugin_label, $plugin_id, $inputs)
    ;
  }

  protected function interact(InputInterface $input, OutputInterface $output)
  {
    $dialog = $this->getDialogHelper();
    $dialog->writeSection($output, 'Welcome to the Drupal Plugin Field Formatter generator');

    // --module option
    $module = $input->getOption('module');
    if (!$module) {
      // @see Drupal\AppConsole\Command\Helper\ModuleTrait::moduleQuestion
      $module = $this->moduleQuestion($output, $dialog);
    }
    $input->setOption('module', $module);

    // --class-name option
    $class_name = $input->getOption('class-name');
    if (!$class_name) {
      $class_name = $dialog->ask(
        $output,
        $dialog->getQuestion('Enter the field formatter name', 'DefaultFieldFormatter'),
        'DefaultFieldFormatter'
      );
    }
    $input->setOption('class-name', $class_name);

    $machine_name = $this->getStringUtils()->camelCaseToUnderscore($class_name);

    // --plugin-label option
    $plugin_label = $input->getOption('plugin-label');
    if (!$plugin_label) {
      $plugin_label = $dialog->ask(
        $output,
        $dialog->getQuestion('Enter the plugin label', $machine_name),
        $machine_name
      );
    }
    $input->setOption('plugin-label', $plugin_label);

    // --plugin-id option
    $plugin_id = $input->getOption('plugin-id');
    if (!$plugin_id) {
      $plugin_id = $dialog->ask(
        $output,
        $dialog->getQuestion('Enter the plugin id',$machine_name),
        $machine_name
      );
    }
    $input->setOption('plugin-id', $plugin_id);

    $output->writeln([
      '',
      'You can add some input fields to create to create settings form',
      'This is optional, press <info>enter</info> to <info>continue</info>',
      ''
    ]);

    // @see Drupal\AppConsole\Command\Helper\FormTrait::formQuestion
    $form = $this->formQuestion($output, $dialog);
    $input->setOption('plugin-form', $form);
  }

  /**
   * {@inheritdoc}
   */
  protected function createGenerator()
  {
    return new PluginFieldFormatterGenerator();
  }
}