# Deptrac

Deptrac is a static code analysis tool for PHP that helps you communicate,
visualize and enforce architectural decisions in your projects. You can freely
define your architectural layers over classes and which rules should apply to
them.

For example, you can use Deptrac to ensure that bundles/modules/extensions in
your project are truly independent of each other to make them easier to reuse.

Deptrac can be used in a CI pipeline to make sure a pull request does not
violate any of the architectural rules you defined. With the optional Graphviz
or Mermaidjs formatter you can visualize your layers, rules and violations.

## Documentation

You can find the documentation in the /docs directory or visit the doc page:
https://deptrac.github.io/deptrac

## Getting Started

You can install Deptrac via Composer. We recommend using the
[deptrac](https://github.com/deptrac/deptrac) package for this:

```console
composer require --dev deptrac/deptrac
```

Once you have downloaded/installed deptrac, you will need to create a
[configuration file](docs/index.md#configuration), where you define your layers and
communication ruleset. This configuration file is written in YAML or php and, by default,
is stored with the name `deptrac.yaml` in your project's root directory.

Deptrac can generate a template for you, using the `init` command.

```console
vendor/bin/deptrac init
```

When you have this file, you can analyse your code by
running the `analyse` command:

```console
vendor/bin/deptrac

# which is equivalent to
vendor/bin/deptrac analyse --config-file=deptrac.yaml
```

In order to run Deptrac you need at least PHP 8.1.

You can analyse projects that require an older PHP version as long as
[nikic/php-parser](https://github.com/nikic/PHP-Parser) can parse it.


## How to Contribute

See the [contribution guide](docs/CONTRIBUTING.md) or go to development repository [deptrac/deptrac-src](https://github.com/deptrac/deptrac-src).

## Further Documentation

* [Backwards Compatibility](docs/bc_policy.md) - General info on how we approach
  backwards compatibility
* [Upgrade Guide](docs/upgrade.md) - List of backwards breaking changes that
  need to be addressed when upgrading Deptrac to a new version and how to do it.
* [Core Concepts](docs/concepts.md) - Explains layers, rules and violations in
  more details.
* [Configuration](docs/configuration.md) - Reference for all available settings
  in a depfile
* [Collectors](docs/collectors.md) - Reference for which collectors are
  available in Deptrac to define your layers.
* [Formatters](docs/formatters.md) - Lists the different output formats
  supported by Deptrac
* [Debugging](docs/debugging.md) - Overview of the debug commands
* [Code Of Conduct](docs/CODE_OF_CONDUCT.md) - Our community standards
* [Contribute](docs/CONTRIBUTING.md) - Advice for contributing code changes,
  e.g. how to run tests or how to build a phar file with your changes that you
  can use to analyse your projects
* [Security Guide](docs/SECURITY.md) - How to report security vulnerabilities
