# Collectors

Collectors decide if a node (typically a class) is part of a layer. You can use
multiple different collectors for a layer.

## `attribute` Collector

The `attribute` collector finds all class-likes, functions or files using the
provided attribute. You can provide the full attribute name or a substring that
should be matched.

```yaml
deptrac:
  layers:
    - name: Entities
      collectors:
        - type: attribute
          value: Doctrine\ORM\Mapping\Entity
```

## `bool` Collector

The `bool` collector allows combining other collectors with or without negation.

```yml
deptrac:
  layers:
    - name: Asset
      collectors:
        - type: bool
          must:
            - type: classLike
              value: .*Foo\\.*
            - type: classLike
              value: .*\\Asset.*
          must_not:
            - type: classLike
              value: .*Assetic.*
```

Every class contains `Foo\` AND `\Asset` and NOT `Assetic`, will become a part
of the *Asset* layer.

## `class` Collector

The `class` collector allows collecting only classes by matching their fully
qualified name to a simplified regular expression. Any match will be
added to the assigned layer.

```yaml
deptrac:
  layers:
    - name: Provider
      collectors:
        - type: class
          value: .*Provider.*
```

Every class name that matches the regular expression becomes a part of the
*Provider* layer. This collector has predefined delimiters and
modifier: `/YOUR_EXPRESSION/i`

## `classLike` Collector

The `classLike` collector allows collecting classes and anything similar to classes like interfaces, traits or enums, by matching their fully
qualified name to a simplified regular expression. Any match will be
added to the assigned layer.

```yaml
deptrac:
  layers:
    - name: Domain
      collectors:
        - type: classLike
          value: .*Domain.*
```

Every classLike name that matches the regular expression becomes a part of the
*domain* layer. This collector has predefined delimiters and
modifier: `/YOUR_EXPRESSION/i`

## `classNameRegex` Collector

The `classNameRegex` collector allows collecting classes by matching their fully
qualified name to a regular expression. Any matching class will be added to the
assigned layer.

```yaml
deptrac:
  layers:
    - name: Controller
      collectors:
        - type: classNameRegex
          value: '#.*Controller.*#'
```

Every class name that matches the regular expression becomes a part of the
*controller* layer.

## `tagValueRegex` Collector

The `tagValueRegex` collector allows collecting classes and functions by
matching the name and value of any tag in their phpdoc block, such as @internal
or @deprecated.

Any matching class will be added to the assigned layer.

```yaml
deptrac:
  layers:
    - name: Deprecated
      collectors:
        - type: tagValueRegex
          tag: '@deprecated'
    - name: DeprecatedSinceV2
      collectors:
        - type: tagValueRegex
          tag: '@deprecated'
          value: '/^since v2/i'
```
All classes tagged with "@deprecated" become part of the
*Deprecated* layer. All classes that specify that they have been
deprecated since v2 also become part of the *DeprecatedSinceV2* layer.

## `composer` Collector

The `composer` collector allows you to define dependencies on composer `require` or `require-dev` packages that follow PSR-0 or PSR-4 autoloading convention. With this collector you can for example enforce:
 - That your `require-dev` dependencies are only used in you non-production code (like DB migrations or SA tools)
 - That your code does not use any transitive dependencies (dependencies on packages installed only because your `composer.json` required packages depend on them themselves)
 - That some packages are only used in particular layers

```yaml
deptrac:
  layers:
    - name: Symfony
      collectors:
        - type: composer
          composerPath: composer.json
          composerLockPath: composer.lock
          packages:
            - symfony/config
            - symfony/console
```

## `directory` Collector

The `directory` collector allows collecting classes by matching their file path
they are declared in to a simplified regular expression. Any matching class will
be added to the assigned layer.

```yaml
deptrac:
  layers:
    - name: Controller
      collectors:
        - type: directory
          value: src/Controller/.*
```

Every file path that matches the regular expression `src/Controller/.*` becomes
a part of the *controller* layer. This collector has predefined delimiters and
modifier: `#YOUR_EXPRESSION#i`

## `extends` Collector

The `extends` collector allows collecting classes extending a specified class by
matching recursively for a fully qualified class or interface name.

```yaml
deptrac:
  layers:
    - name: Foo
      collectors:
        - type: extends
          value: 'App\SomeClass'
```

## `functionName` Collector

The `functionName` collector allows collecting functions by matching their fully
qualified name to a simplified regular expression. Any matching function will be
added to the assigned layer.

```yaml
deptrac:
  layers:
    - name: Foo
      collectors:
        - type: functionName
          value: .*array_.*
```

## `glob` Collector

The `glob` collector finds all files matching the provided glob pattern.

```yaml
deptrac:
  layers:
    - name: Repositories
      collectors:
        - type: glob
          value: src/Modules/**/Repository
```

## `implements` Collector

The `implements` collector allows collecting classes implementing a specified
interface by matching recursively for a fully qualified interface name.

```yaml
deptrac:
  layers:
    - name: Foo
      collectors:
        - type: implements
          value: 'App\SomeInterface'
```
## `interface` Collector

The `interface` collector allows collecting only interfaces by matching their fully
qualified name to a simplified regular expression. Any matching interface will be
added to the assigned layer.

```yaml
deptrac:
  layers:
    - name: Contracts
      collectors:
        - type: interface
          value: .*Contracts.*
```

Every interface name that matches the regular expression becomes a part of the
*Interfaces* layer. This collector has predefined delimiters and
modifier: `/YOUR_EXPRESSION/i`

## `inherits` Collector

The `inherits` collector allows collecting classes inheriting from a specified
class, whether by implementing an interface, extending another class or by using
a trait, by matching recursively for a fully qualified class name.

```yaml
deptrac:
  layers:
    - name: Foo
      collectors:
        - type: inherits
          value: 'App\SomeInterface'
```

## `layer` Collector

This collector collects all the tokens collected by another layer. It is not
very useful by itself (unless you want to have tokens in multiple layers), but
it is very useful to exclude classes in combination with
the [`bool` Collector](#bool-collector):

```yaml
deptrac:
  layers:
    - name: SubDomain
      collectors:
        - type: directory
          value: src/Domain/Subdomain/.*
    - name: Domain
      collectors:
        - type: bool
          must:
            - type: directory
              value: src/Domain/.*
          must_not:
            - type: layer
              value: SubDomain
```

## `method` Collector

The `method` collector allows collecting classes by matching their methods name
to a regular expression. Any matching class will be added to the assigned layer.

```yaml
deptrac:
  layers:
    - name: Foo services
      collectors:
        - type: method
          value: .*foo
```

Every class having a method that matches the regular expression `.*foo`,
e.g. `getFoo()` or `setFoo()` becomes a part of the *Foo services* layer.

## `superglobal` Collector

The `superglobal` collector allows collecting superglobal PHP variables matching
the specified superglobal name.

```yaml
deptrac:
  layers:
    - name: Foo
      collectors:
        - type: superglobal
          value:
            - _POST
            - _GET
```

## `trait` Collector

The `trait` collector allows collecting only traits by matching their fully
qualified name to a simplified regular expression. Any matching trait will be
added to the assigned layer.

```yaml
deptrac:
  layers:
    - name: Traits
      collectors:
        - type: trait
          value: .*Traits.*
```

Every trait name that matches the regular expression becomes a part of the
*traits* layer. This collector has predefined delimiters and
modifier: `/YOUR_EXPRESSION/i`

## `uses` Collector

The `uses` collector allows collecting classes using a specified trait by
matching recursively for a fully qualified trait name.

```yaml
deptrac:
  layers:
    - name: Foo
      collectors:
        - type: uses
          value: 'App\SomeTrait'
```

## `PHP Internal` Collector

The `PHP Internal` collector collects PHP defined classes and functions
including those loaded with PHP extensions.

```yaml
deptrac:
  layers:
    - name: Foo
      collectors:
        - type: php_internal
          value: ^reset$
```

## Custom Collectors

You can create custom collectors in your project by implementing the
`Deptrac\Deptrac\Contract\Layer\CollectorInterface`. As soon as an unknown
collector is referenced in the config file Deptrac will try to load the class in
your project. With this you can create collectors specific for your use case.

If you would like to make your collector available to others, feel free to
[contribute](CONTRIBUTING.md) it by making a pull request.

## Extra collector configuration

Any collector can also specify parameter `private:true` like this:

```yaml
deptrac:
  layers:
    - name: Foo
      collectors:
        - type: uses
          value: 'App\SomeTrait'
          private: true
```

This means that tokens collected by this specific collector can be referenced only by other tokens in the same layer. References from other layers will be considered violations, even if they would normally be allowed by configured ruleset.

This can be useful at least in 2 cases:
 - **External library that should be used only by one particular layer** - In this case, you might via vendor include a library that should be used only by this particular layer and nobody else.
 - **Layer that has a public API and private implementation** - You might want to provide only a few classes to be available to use by other layers (public API) that call the internal implementation of the layer that on the other hand should not be available to anybody else other than the public API of the layer.
