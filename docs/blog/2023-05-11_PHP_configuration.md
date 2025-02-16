---
hide:
  - navigation
  - toc
---

# Dynamic Deptrac configuration with PHP config file

May 11, 2023 - 5 min read

---

Do you copy-paste deptrac config every time you need to
add a layer? Do you have a large existing project that has similar structure across its architecture? With PHP config files you can significantly cut down the amount of time spend on creating and maintaining your deptrac configuration.

---

For a long time, Deptrac was only supporting a `yaml` configuration. Thanks to the effort by [grennadi](https://github.com/gennadigennadigennadi) you can now use Symfony config builders to create a dynamic deptrac configuration using PHP. Let's take a look at how to do it.

Start by creating a `deptrac.config.php` file in the root of the project:

```php
<?php

use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (DeptracConfig $config, ContainerConfigurator $containerConfigurator): void {

};
```

> All the required classes you might need for config definition exist in the `Deptrac\Deptrac\Contract\Config` namespace and are covered by the [backwards compatibility promise,](../bc_policy.md) so you don't need to worry your configuration will suddenly stop working without warning.

Then define the shared configuration for the project like `paths` to the analysed files and the used `analysers`:

```php
    $config
        ->paths('src')
        ->analysers(
            EmitterType::CLASS_TOKEN,
            EmitterType::FUNCTION_TOKEN,
            EmitterType::FUNCTION_CALL,
        );
```

Continue by defining some layers:

```php
    $config
        ->layers(
            $dependency = Layer::withName('Dependency')->collectors(
                DirectoryConfig::create('src/Core/Dependency/.*')
            ),
            $dependencyInjection = Layer::withName('DependencyInjection')->collectors(
                DirectoryConfig::create('src/Supportive/DependencyInjection/.*')
            ),
            $inputCollector = Layer::withName('InputCollector')->collectors(
                DirectoryConfig::create('src/Core/InputCollector/.*')
            ),
            $layer = Layer::withName('Layer')->collectors(
                DirectoryConfig::create('src/Core/Layer/.*')
            ),
            $file = Layer::withName('File')->collectors(
                DirectoryConfig::create('src/Supportive/File/.*')
            ),
        );
```

You can use all the collectors you find in the [collectors' documentation](../collectors.md). Use the appropriate config class in the  `Deptrac\Deptrac\Contract\Config\Collector\` namespace.

Notice that we assign all the layer configs to a variable. This is important to define rulesets between the layers:

```php
    $config
        ->rulesets(
            Ruleset::forLayer($inputCollector)->accesses($file),
            #...
        );
```

You can also define configuration for the formatters if you need to, again re-using the previously defined layers to ensure you don't have a typo in your definition:

```php
    $config
        ->formatters(
            GraphvizConfig::create()
                ->pointsToGroup(true)
                ->groups('Supportive', $file, $dependencyInjection)
                ->groups('Core', $dependency, $inputCollector, $layer)
        );
```

Last, but not least, you can also plug in any extension you write like custom collectors, rules or commands. For example a custom suppression of violations when depending on contract classes:

```php
    $services = $containerConfigurator->services();
    $services->set(IgnoreDependenciesOnContract::class)
        ->tag('kernel.event_subscriber');
```

To tie it all together, you have to specify that you want deptrac to you your php config file, for example like this:

```console
$ php deptrac.php -c deptrac.config.php
```

As you can see this feature allows you to use the full expressive power of PHP to create dynamic configuration on the fly.

---
Do you like Deptrac and use it every day? [Consider supporting further development of Deptrac by sponsoring me on GitHub Sponsors](https://github.com/sponsors/patrickkusebauch). I’d really appreciate it!

Author: [patrickkusebauch](https://github.com/patrickkusebauch)
