<?php


namespace Drupal\ib3_module_generator\Generator;

use Drupal\Console\Core\Generator\Generator;

class ModuleGenerator extends Generator
{
  public function generate(
    $module,
    $machineName,
    $dir,
    $description,
    $core,
    $package,
    $moduleFile,
    $twigtemplate
  ) {

    $templateName = str_replace("_", "-", $machineName);

    $name = str_replace("Ib3 ","", ucwords(str_replace("_", " ", $machineName)));
    $className = str_replace(" ","", $name);
    $snakeName = str_replace(" ","_", $name);
    $camelName = strtolower(substr($name, 0, 1)) . substr($className, 1);

    $this->directoryTests($dir, 'module');

    $parameters = [
      'module' => $module,
      'name' => $name,
      'machine_name' => $machineName,
      'template_name' => $templateName,
      'class_name' => $className,
      'snake_name' => $snakeName,
      'camel_name' => $camelName,
      'type' => 'module',
      'core' => $core,
      'description' => $description,
      'package' => $package,
      'twigtemplate' => $twigtemplate,
    ];

    $this->addSkeletonDir(__DIR__ . '/../../templates');

    $this->renderFile(
      'info.twig',
      $dir.'/'.$machineName.'.info.yml',
      $parameters
    );

    $this->renderFile(
      'module.twig',
      $dir.'/'.$machineName.'.module',
      $parameters
    );

    $this->renderFile(
      'libraries.twig',
      $dir.'/'.$machineName.'.libraries.yml',
      $parameters
    );

    $this->renderFile(
      'install.twig',
      $dir.'/'.$machineName.'.install',
      $parameters
    );

    $template_dir .= $dir.'/templates/';
    $this->directoryTests($template_dir, 'templates');
    $this->renderFile(
      'template.twig',
      $template_dir . $templateName . '.html.twig',
      $parameters
    );

    $images_dir .= $dir.'/assets/images/';
    $this->directoryTests($images_dir, 'images');
    $this->renderFile(
      'readme.twig',
      $images_dir . 'readme.md',
      $parameters
    );

    $install_dir .= $dir.'/config/install/';
    $this->directoryTests($install_dir, 'install');
    $this->renderFile(
      'settings.twig',
      $install_dir . $machineName . '.settings.yml',
      $parameters
    );

    $sass_dir .= $dir.'/sass/';
    $this->directoryTests($sass_dir, 'sass');
    $this->renderFile(
      'sass.twig',
      $sass_dir . '_' . $machineName . '.scss',
      $parameters
    );

    $block_dir .= $dir.'/src/Plugin/Block/';
    $this->directoryTests($block_dir, 'block');
    $this->renderFile(
      'block.twig',
      $block_dir . $className . 'Block.php',
      $parameters
    );

    $js_dir .= $dir.'/js/';
    $this->directoryTests($js_dir, 'js');
    $this->renderFile(
      'js.twig',
      $js_dir . $templateName . '.js',
      $parameters
    );
  }

  private function directoryTests($dir, $name)
  {
    if (file_exists($dir)) {
      if (!is_dir($dir)) {
        throw new \RuntimeException(
          sprintf(
            'Unable to generate the %s directory as the target directory "%s" exists but is a file.',
            $name,
            realpath($dir)
          )
        );
      }
      $files = scandir($dir);
      if ($files != ['.', '..']) {
        throw new \RuntimeException(
          sprintf(
            'Unable to generate the %s directory as the target directory "%s" is not empty.',
            $name,
            realpath($block_dir)
          )
        );
      }
      if (!is_writable($dir)) {
        throw new \RuntimeException(
          sprintf(
            'Unable to generate the %s directory as the target directory "%s" is not writable.',
            $name,
            realpath($block_dir)
          )
        );
      }
    }
  }
}
