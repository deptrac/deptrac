# Debugging

Deptrac provides a series of debug commands that help you identify issues in
your config files. All commands output one issue per line and can therefore be
easily combined with other tools like `wc` or `grep`.

## `debug:layer`

With the `debug:layer`-command you can list all tokens which are matched in
a specific layer. This command only shows tokens that would be emitted by your analyser configuration.

```console
$ php deptrac.phar debug:layer --config-file=deptrac.config.php Time

 ---------------------------------------------------- ------------
  Time                                                 Token Type
 ---------------------------------------------------- ------------
  /src/Supportive/Time/Period.php                      file
  /src/Supportive/Time/StartedPeriod.php               file
  /src/Supportive/Time/Stopwatch.php                   file
  /src/Supportive/Time/StopwatchException.php          file
  Deptrac\Deptrac\Supportive\Time\Period               class-like
  Deptrac\Deptrac\Supportive\Time\StartedPeriod        class-like
  Deptrac\Deptrac\Supportive\Time\Stopwatch            class-like
  Deptrac\Deptrac\Supportive\Time\StopwatchException   class-like
 ---------------------------------------------------- ------------
```

## `debug:token`

The `debug:token` (previously `debug:class-like`)-command will let you know which layers a specified token belongs to. Since you can specify the token type, this commands ignores your analyser configuration for emitted token types.

```console
$ php deptrac.phar debug:token --config-file=examples/DirectoryLayer.depfile.yaml 'examples\Layer1\AnotherClassLikeAController' class-like

Controller
Layer1
```

## `debug:unassigned`

With the `debug:unassigned`-command you list all tokens in your path that are
not assigned to any layer. This is useful to test that your collector
configuration for layers is correct.  This command only shows tokens that would be emitted by your analyser configuration.

```console
$ php deptrac.phar debug:unassigned --config-file=examples/DirectoryLayer.depfile.yaml

examples\Layer1\AnotherClassLikeAController
examples\Layer1\SomeClass
examples\Layer1\SomeClass2
```

This command exist with the return code 2 when it ran successfully, but there
were some tokens in the output. You can use this information in your CI
pipelines.

## `debug:dependencies`

With the `debug:dependencies`-command you can see all dependencies of your layer. You can optionally specify a target layer to get only dependencies from one layer to the other:

```console
$ php deptrac.phar debug:dependencies debug:dependencies Ast InputCollector

  Deptrac\Deptrac\Core\Ast\AstMapExtractor depends on Deptrac\Deptrac\Core\InputCollector\InputCollectorInterface (InputCollector)
  .../deptrac/src/Core/Ast/AstMapExtractor.php:15
  Deptrac\Deptrac\Core\Ast\AstMapExtractor depends on Deptrac\Deptrac\Core\InputCollector\InputException (InputCollector)
  .../deptrac/src/Core/Ast/AstMapExtractor.php:28
  Deptrac\Deptrac\Core\Ast\AstException depends on Deptrac\Deptrac\Core\InputCollector\InputException (InputCollector)
  .../deptrac/src/Core/Ast/AstException.php:13
```

## `debug:unused`

With the `debug:unused`-command you list all the rulesets that are not being used (i.e. there are no dependencies being allowed by this ruleset).

You can optionally specify a limit (`--limit=<int>`) of how many times can be the ruleset used to be considered unused. This is useful
if you want to find dependencies that are barely used and may be a prime candidate to get rid of.

```console
$ php deptrac.phar debug:unused --limit=10

  Analyser layer is dependent Layer layer 5 times
  Ast layer is dependent File layer 9 times
  Ast layer is dependent InputCollector layer 3 times
  Console layer is dependent OutputFormatter layer 4 times
  Console layer is dependent DependencyInjection layer 2 times
  Console layer is dependent File layer 5 times
  InputCollector layer is dependent File layer 3 times
  OutputFormatter layer is dependent DependencyInjection layer 1 times
```

## `changed-files`

> [!CAUTION]
> This command in experimental and is not covered by
> the [BC policy](bc_policy.md).

This command list the layers corresponding to the passed files. Optionally it
can also list all the layers that depend on those layers.

```console
$ php deptrac.phar changed-files --with-dependencies src/Supportive/File/FileReader.php

  File
  Console;Ast;InputCollector;Analyser;Dependency;Layer
```

For a discussion as to why that information might be useful, refer to
the [90DaysOfDevOps Presentation](https://github.com/MichaelCade/90DaysOfDevOps/pull/472).
