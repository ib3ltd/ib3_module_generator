<?php

namespace Drupal\ib3_module_generator\Command;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Drupal\ib3_module_generator\Generator\ModuleGenerator;
use Drupal\Console\Command\Shared\ConfirmationTrait;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Utils\Validator;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Utils\DrupalApi;


class CreateCommand extends Command {

  use ConfirmationTrait;

  /**
   * @var ModuleGenerator
   */
  protected $generator;

  /**
   * @var Validator
   */
  protected $validator;

  /**
   * @var string
   */
  protected $appRoot;

  /**
   * @var StringConverter
   */
  protected $stringConverter;

  /**
   * @var DrupalApi
   */
  protected $drupalApi;

  /**
   * @var string
   */
  protected $twigtemplate;

  public function __construct(
      ModuleGenerator $generator,
      Validator $validator,
      $appRoot,
      StringConverter $stringConverter,
      DrupalApi $drupalApi,
      $twigtemplate = null
  ) {
      $this->generator = $generator;
      $this->validator = $validator;
      $this->appRoot = $appRoot;
      $this->stringConverter = $stringConverter;
      $this->drupalApi = $drupalApi;
      $this->twigtemplate = $twigtemplate;
      parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('ib3:module:create')
      ->setDescription($this->trans('commands.ib3_module_generator.create.description'))
      ->addOption(
        'module',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.module.options.module')
      )
      ->addOption(
        'machine-name',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.module.options.machine-name')
      )
      ->addOption(
        'module-path',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.module.options.module-path')
      )
      ->addOption(
        'description',
        null,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.module.options.description')
      )
      ->addOption(
        'core',
        null,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.module.options.core')
      )
      ->addOption(
        'package',
        null,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.module.options.package')
      )
      ->addOption(
        'module-file',
        null,
        InputOption::VALUE_NONE,
        $this->trans('commands.generate.module.options.module-file')
      )->addOption(
        'twigtemplate',
        null,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.module.options.twigtemplate')
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output)
  {
    $validator = $this->validator;

    $io = new DrupalStyle($input, $output);
    $module = $io->ask(
      'Module name (e.g. ib3 Gallery)',
      null,
      function ($module) use ($validator) {
        $module = trim(str_replace(["Ib3 ", "IB3 ","ib3_","Ib3_","IB3_"], "ib3 ", $module));
        if (substr($module, 0, 4) != 'ib3 ') $module = 'ib3 ' . $module;
        return $validator->validateModuleName($module);
      }
    );

    $input->setOption('module', $module);

    $machineName = $this->stringConverter->createMachineName($module);
    $input->setOption('machine-name', $machineName);

    $modulePath = $this->appRoot.'/modules/custom/'.$machineName;
    $input->setOption('module-path', $modulePath);

    $description = 'Another ib3 auto generated module.';
    $input->setOption('description', $description);

    $package = 'ib3';
    $input->setOption('package', $package);

    $core = '8.x';
    $input->setOption('core', $core);

    $moduleFile = $machineName.'.module';
    $input->setOption('module-file', $moduleFile);

    $twigTemplate = $machineName.'.html.twig';
    $input->setOption('twigtemplate', $twigTemplate);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $io = new DrupalStyle($input, $output);
    $yes = $input->hasOption('yes')?$input->getOption('yes'):false;

    if (!$this->confirmGeneration($io, $yes)) {
      return 1;
    }

    $module = $input->getOption('module');
    $modulePath = $input->getOption('module-path');
    $machineName = $input->getOption('machine-name');
    $description = $input->getOption('description');
    $core = $input->getOption('core');
    $package = $input->getOption('package');
    $moduleFile = $input->getOption('module-file');
    $twigTemplate = $input->getOption('twigtemplate');

    $this->generator->generate(
      $module,
      $machineName,
      $modulePath,
      $description,
      $core,
      $package,
      $moduleFile,
      $twigTemplate
    );

    return 0;
  }

  /**
   * @return ModuleGenerator
   */
  protected function createGenerator()
  {
    return new ModuleGenerator();
  }

}
