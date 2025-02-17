# Configuration

The configuration file describes your [layers, ruleset](concepts.md) and adjusts
output formatting.

We suggest you also check out [Deptrac's configuration](https://github.com/deptrac/deptrac/blob/main/deptrac.yaml)
for checking its own architecture as it uses most available options.

## Deptrac

The following table shows the available config keys for Deptrac.

<table>
<thead>
<tr>
<th>Property Path</th>
<th>Input description</th>
<th>Example usage</th>
</tr>
</thead>
<tbody>
<tr>
<td>analyser.internal_tag</td>
<td>
Specifies a custom doc block tag which deptrac should use to identify layer-internal
class-like structures. The tag <code>@deptrac-internal</code> will always be used
for this purpose. This option allows an additional tag to be specified, such as
<code>@layer-internal</code> or plain <code>@internal</code>.
</td>
<td>

```yaml
deptrac:
  analyser:
    internal_tag: "@layer-internal"
```
</td>
</tr>
<tr>
<td>analyser.types</td>
<td>

A list with at least one of the following supported dependency types:
<ul>
<li><strong>class</strong> default &mdash; analyses class definitions for everything apart from superglobal usage.
</li>
<li><strong>class_superglobal</strong> &mdash; analyses class definitions for superglobal usage.
</li>
<li><strong>use</strong> default &mdash; analyses file definitions for use statements.
</li>
<li><strong>file</strong> &mdash; analyses file for everything apart from use statements and function/class definitions.
</li>
<li><strong>function</strong> &mdash; analyses function definitions for everything apart from superglobal usage.
</li>
<li><strong>function_superglobal</strong> &mdash; analyses function definitions for superglobal usage.
</li>
<li><strong>function_call</strong> &mdash; analyses calls to custom(user-defined) functions

</td>
<td>

```yaml
deptrac:
  analyser:
    types:
      - "use"
      - "file"
      - "class_superglobal"
      - "function_superglobal"
      - "function_call"
```

</td>
</tr>
<tr>
<td>paths</td>
<td>

List of paths where Deptrac should look for dependencies to be analysed.
Usually, this is where your code is stored, e.g. <code>src/</code>, or <code>lib/</code> or
something similar.

</td>
<td>

```yaml
deptrac:
  paths:
    - src/
```

</td>
</tr>
<tr>
<td>exclude_files</td>
<td>

A list of regular expression-patterns to determine which files or directories to exclude,
e.g. test files or config

</td>
<td>

```yaml
deptrac:
  exclude_files:
    - '#.*Test\.php$#'
```

</td>
</tr>
<tr>
<td>formatters.graphviz.groups</td>
<td>

Key is the name of the group and values are the layers belonging to that group

</td>
<td>

```yaml
deptrac:
  formatters:
    graphviz:
      groups:
        Entrypoints:
          - Controllers
          - Commands
        Persistence:
          - Repositories
          - Entities
```

</td>
</tr>
<tr>
<td>formatters.graphviz.hidden_layers</td>
<td>

List of layers to be excluded from the Graphviz output

</td>
<td>

```yaml
deptrac:
  formatters:
    graphviz:
      hidden_layers:
        - Controllers
```

</td>
</tr>
<tr>
<td>formatters.codeclimate.severity</td>
<td>

Assigns a severity to each section reported by Deptrac. The following
severity types are supported by codeclimate:
<ul>
<li>info</li>
<li>minor</li>
<li>major</li>
<li>critical</li>
<li>blocker</li>
</ul>

</td>
<td>

```yaml
deptrac:
  formatters:
    codeclimate:
      severity:
        failure: blocker
        skipped: major
        uncovered: major
```

</td>
</tr>
<tr>
<td>deptrac.ignore_uncovered_internal_classes</td>
<td>

Whether PHP-internal classes like <code>DateTimeImmutable</code> should count
towards uncovered classes, when they are not part of any layer.

</td>
<td>

```yaml
deptrac:
  ignore_uncovered_internal_classes: false # default: true
```

</td>
</tr>
<tr>
<td>deptrac.layers</td>
<td>

Defines your architectural layers by collecting dependencies using collectors

</td>
<td>

```yaml
deptrac:
  layers:
    -
      name: Controller
      collectors:
        -
          type: classLike
          value: .*Controller.*
```

</td>
</tr>
<tr>

<td>deptrac.ruleset</td>
<td>

Assign communication rules by specifying which layers a layer can
communicate with (if any). If you prepend a layer with <code>+</code> then not
only this layer is allowed, but also all layers it allows.

</td>
<td>

```yaml
deptrac:
  ruleset:
    Controllers: [Services]
    Services:
      - Repositories
    Repositories: ~
```

</td>
</tr>
<tr>

<td>deptrac.skip_violations</td>
<td>

Define a dictionary of dependencies and their known violations.
This violations will be ignored in your pipeline and not trigger a
failing return code.

</td>
<td>

```yaml
deptrac:
  skip_violations:
    Library\LibClass:
      - Core\CoreClass
```

</td>
</tr>
</tbody>
</table>

## Imports

If your config file becomes too large, you can split it up into multiple files
that can then be imported in the main file using the `imports` section.
This is also useful to separate your baseline from the rest of the
configuration, so it can be regenerated by the `baseline` formatter.

Example:

```yaml
imports:
  - deptrac.baseline.yaml
```

## Services

Please see [Symfony docs](https://symfony.com/doc/current/service_container.html#explicitly-configuring-services-and-arguments).
This allows you to register new services, e.g. custom formatters or collectors.


```yaml
services:
  - class: Internal\Deptrac\Deptrac\IgnoreDependenciesOnContract
    tags:
      - { name: kernel.event_listener, event: Deptrac\Deptrac\Contract\Analyser\ProcessEvent }
```

## Parameters

Deptrac provides parameters that can be user in your configuration.

* `%currentWorkingDirectory%` The path Deptrac runs in
* `%projectDirectory%` The path where the configuration is stored.
* `%cache_file%` contains the filename and path for the cache file.
  Note: This parameter is overwritten by `--cache-file=` if it is set.

You can specify your own parameters and reuse them in your configuration:

Example:

```yaml
parameters:
  Project: MyProject

deptrac:
  layers:
    -
      name: Foo
      collectors:
        -
          type: implements
          value: '%Project%\SomeInterface'
```
