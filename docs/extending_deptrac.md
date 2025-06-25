# Extending Deptrac

Deptrac defines its extension points by providing a set of **contract** classes
that you can depend on for your implementation. The classes can be found in
the `src/Contract` directory and are covered by
the [backwards compatibility policy promise](bc_policy.md), meaning they will stay stable within major releases.

> **Note**
> In non-code excerpt examples where FQCN is not specified, the base
> namespace `Deptrac\Deptrac\Contract\` is omitted for readability.

Before you decide to extend Deptrac, it is useful to understand how Deptrac
works to see where is a good place to insert your extension.

First, files collected based on `paths` parameter are parsed by a PHP code
parser and an Abstract Syntax Tree (AST) for each file is created. Then we use
reference extractors to find references in each file. These are the first 2
places where you can extend Deptrac. You can write a custom reference extractor
implementing the `Ast\ReferenceExtractorInterface` if you need to create
additional references. If parser as a whole is not to your liking, you can
completely replace it by implementing the `Ast\ParserInterface`. In either case
the result of this step is an `Ast\AstMap\AstMapInterface` that contains all the
references found in each file.

Not every reference automatically consist a dependency. To decide this,
references are passed to dependency emitters implementing
`Dependency\DependencyEmitterInterface`. If you find a reference in your `AstMap`
that is not transformed into a dependency, you can create a custom emitter to do
so. The result of this transformation is a dependency list
`Dependency\DependencyListInterface`.

Every dependency (`Dependency\DependencyInterface`) in the list has a depender
and a dependee. Both of them can belong to one or more layers that you specified
in your configuration. But what if the current layer collector cannot satisfy
your rules for what should belong to a layer? That's where
`Layer\CollectorInterface` comes into play. You can create a custom
implementation of this interface that can decide whether a token does or does
not belong to your layer.

Once the layers for both the depender and the dependee tokens are known, we
dispatch a `Analyser\ProcessEvent`. Now the task is to decide whether this
particular dependency is allowed or not. To help you with that, you can
implement the `\Symfony\Component\EventDispatcher\EventSubscriberInterface` and
subscribe to the `Analyser\ProcessEvent`. The result of this processing for all
dependencies is an `Analysis\AnalysisResult`.

Now, that you have the result of the analysis, the last step is to format the
result. For this you have the option of implementing a custom output formatter
using the `OutputFormatter\OutputFormatterInterface`.

To recap, the main extension points are:
- `Ast\ReferenceExtractorInterface` to extract references from the AST
- `Ast\ParserInterface` to replace the whole PHP parser
- `Dependency\DependencyEmitterInterface` to transform references to dependencies
- `Layer\CollectorInterface` to better define tokens that belong to a layer
- Event subscribers to `Analyser\ProcessEvent` to decide whether a dependency is allowed or not
- `OutputFormatter\OutputFormatterInterface` to customize how the results are displayed

As you can see, there are many ways you can customize Deptrac behavior.

## Reference extractors

Reference extractors implement the `Ast\ReferenceExtractorInterface` and serve
to create references from tokens parsed by the AST parser. Let's look at one of
the default extractors that ship with Deptrac for an example:

```php
use PhpParser\Node\Stmt\Catch_;

/**
 * @implements ReferenceExtractorInterface<Catch_>
 */
final class CatchExtractor implements ReferenceExtractorInterface
{
    public function __construct(private readonly TypeResolverInterface $typeResolver) {}

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, ...$node->types) as $classLikeName) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $node->getLine(), DependencyType::CATCH);
        }
    }

    public function getNodeType(): string
    {
        return Catch_::class;
    }
}
```

Function `getNodeType()` specified for which Nikic PHP Parser types encountered
in the code should this particular extractor be called for. Therefore, for every
`catch` expression (from the try-catch) the `processNode()` function of this
extractor will be called. The first parameter `Node $node` specifies the Nikic
PHP parser node. In this case it is guaranteed to be `\PhpParser\Node\Stmt\Catch_`
because that is what we specified in `getNodeType()`. The second parameter,
`ReferenceBuilderInterface $referenceBuilder` is the builder class where we
define that a reference exists.

Dependencies come in 2 flavors: `dependency()` and `astInherits()`. `dependency()`
is a direct dependency that exists in the code. `astInherits()` is a dependency
that is created because the current source code "inherits" some source code from
somewhere else. That happens in cases of classes implementing an interface,
extending a class or using a trait (they also "inherit" the dependencies of the
interface, parent class or the trait).

The third parameter of `processNode()`, `TypeScope $typeScope` provides you
with the current type scope. It is aware of what `use` statements are in effect
at the current point in code and can therefore resolve a class name to the Fully
Qualified Class Name (FQCN).

Last piece of the puzzle is the `TypeResolverInterface $typeResolver` passed in
the constructor. Type resolver is useful to resolve complex PHP types (like
unions) to the individual class names that are part of the type.

If you want to write your own reference extractor, the best place to start is to
take a look at the default reference extractors that Deptrac ships with in the
`\Deptrac\Deptrac\DefaultBehavior\Ast\Extractors` namespace. You can find some
advanced behaviour there as well, like how to deal with template types.

Lastly, don't forget to register it in the `deptrac.config.php` file:

```php
return static function (DeptracConfig $config, ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CustomExtractor::class)
        ->tag('reference_extractors');
}
```

## AST Parser

It may be the case that the current AST parser implementation using Nikic PHP
parser is insufficient to your needs. It might not capture all the nodes that
you are interested in or does not provide all the context information for you to
decide on a reference. Or the resolution capabilities are not as good as you
might expect from for example PHPStan. In such case you have the option to
replace the AST parser completely.

All you need to do is to implement the `Ast\ParserInterface` and its 2 methods:
- `parseFile(string $file): Ast\AstMap\FileReference`
- `getMethodNamesForClassLikeReference(Ast\AstMap\ClassLikeReference $classReference): array`

`parseFile` takes the path to a file a returns a `Ast\AstMap\FileReference` that
consists of all the references found in that file. `getMethodNamesForClassLikeReference`
should return all the method names found in a particular class-like.

Implementing a custom AST parser is quite a difficult thing to do, and we do not
expect that it is something that most of the users would need to do. If you do,
take a look at the default implementation that ships with Deptrac in the `\Deptrac\Deptrac\DefaultBehavior\Ast\Parser` namespace. It can serve as a
template for your implementation.

Lastly, don't forget to register it in the `deptrac.config.php` file:

```php
return static function (DeptracConfig $config, ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->alias(ParserInterface::class, CustomParser::class);
    $services->set(CustomParser::class);
}
```

There can be only one active parser at the time, so you are replacing it.

## Dependency emitters

Dependency emitters transform the references found between tokens by the AST
parser into a fully fledged dependencies that are recognized by Deptrac and that
you are familiar from the output. Not all references automatically mean a
dependency and also any reference can cause multiple dependencies (like a
reference to a parent class via the `extends` keyword would cause a dependency
for every dependency in the parent class).

If you want to create a custom dependency emitter, you need to implement the
`Dependency\DependencyEmitterInterface`. It has only 2 methods - `getName()` to
give it a name that you can reference in your configuration using the
`AnalyserConfig->types()` to decide whether the emitter should be used and
`applyDependencies(Ast\AstMap\AstMapInterface $astMap, Dependency\DependencyListInterface $dependencyList): void`.

The usage is relatively simple. Iterate over all the references in the
`Ast\AstMap\AstMapInterface` and when you decide that the reference should
cause a dependency, add it to `Dependency\DependencyListInterface`. For
inspiration, you can take a look at the default Deptrac emitters in the
`\Deptrac\DefaultBehavior\Dependency` namespace. Once you have your emitter,
don't forget to register it in your `deptrac.config.php` file:

```php
return static function (DeptracConfig $config, ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services
        ->set(CustomDependencyEmitter::class)
        ->tag('dependency_emitter', ['key' => 'emitter_name_for_config'])
    ;
}
```

## Layer collectors

Layer collectors help you decide whether a particular token should be part of
your configured layer. Deptrac already ships with an extensive collection of
collectors for you to use. But if you need something more custom, you have the
option of adding your own by implementing the `Layer\CollectorInterface`. It has only one function to implement `satisfy(array $config, Ast\AstMap\TokenReferenceInterface $reference): bool`.

The `$config` parameters passes whatever configuration you have given in the
`deptrac.config.php` file to you and the `$reference` is the token for whom you
should decide whether it should be a part of the layer or not. Also don't forget
that you can throw one of the specified exceptions defined in the interface if
you are for some reason not able to make the decision.

Once you have your collector ready, don't forget to register it in your
`deptrac.config.php` file:

```php
return static function (DeptracConfig $config, ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services
        ->set(CustomCollector::class)
        ->tag('collector', ['type' => 'collector_name_to_be_used_in_config'])
    ;
}
```

As always, you can look at the default collectors in the
`\Deptrac\Deptrac\DefaultBehavior\Layer` namespace for inspiration.

## Analyser event subscribers

Analyser events are published for every dependency and its subscribers decide
whether the dependencies are allowed or not. There are two events that you can
subscribe to:
- `Analyser\ProcessEvent` that is published for every dependency
- `Analyser\PostProcessEvent` that is published once all dependencies are processed

All of your subscribers have to implement the `\Symfony\Component\EventDispatcher\EventSubscriberInterface`.

Let's look at one of the default Deptrac event subscribers to see how it works:

```php
use Deptrac\Deptrac\Contract\Analyser\EventHelper;
use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\Contract\Analyser\ViolationCreatingInterface;

final class DependsOnPrivateLayer implements ViolationCreatingInterface
{
    public function __construct(private readonly EventHelper $eventHelper) {}

    public static function getSubscribedEvents()
    {
        return [
            ProcessEvent::class => ['invoke', -3],
        ];
    }

    public function invoke(ProcessEvent $event): void
    {
        $ruleset = $event->getResult();

        foreach ($event->dependentLayers as $dependentLayer => $isPublic) {
            if ($event->dependerLayer !== $dependentLayer && !$isPublic) {
                $this->eventHelper->addSkippableViolation($event, $ruleset, $dependentLayer, $this);
                $event->stopPropagation();
            }
        }
    }

    public function ruleName(): string
    {
        return 'DependsOnPrivateLayer';
    }

    public function ruleDescription(): string
    {
        return 'You are depending on a part of a layer that was defined as private to that layer and you are not part of that layer.';
    }
}
```

`Analyser\ViolationCreatingInterface` extends the `\Symfony\Component\EventDispatcher\EventSubscriberInterface`
and adds two new methods: `ruleName(): string` and `ruleDescription(): string`
Any subscriber that can create rule violations SHALL implement this interface.
The reason is to provide end user with a nice UX where they can clearly see why
the violation occurred. This is especially important when there are multiple
subscribers not no-trivial rules.

`getSubscribedEvents()` specifies that we are listening to the `Analyser\ProcessEvent`
and when we encounter it to call the `invoke` method on this class. Lastly the
number decides in what order should this subscriber be called with respect to
other subscribers. For more details, look at [Symfony documentation](https://symfony.com/doc/current/event_dispatcher.html).

Post process event is dispatched only once when all the dependency processing is
done. It allows you to make last minute changes to the result of rule applications.

For example, you can specify (as Deptrac already does) that all baseline entries
that were not matched to a violation should be Errors:

```php
    public function invoke(PostProcessEvent $event): void
    {
        $ruleset = $event->getResult();

        foreach ($this->eventHelper->unmatchedSkippedViolations() as $tokenA => $tokensB) {
            foreach ($tokensB as $tokenB) {
                $ruleset->addError(new Error(sprintf('Skipped violation "%s" for "%s" was not matched.', $tokenB, $tokenA)));
            }
        }
    }
```

As always, when creating event subscribers, the `\Deptrac\Deptrac\DefaultBehavior`
namespace is your friend, in this case the `\Deptrac\Deptrac\DefaultBehavior\Analyser`.
It contains the default implementations that ship with Deptrac and you can use
them as template for your own and to know when the default ones are called to
correctly schedule your own implementation between them.

And do not forget to register your subscriber in the `deptrac.config.php` file:

```php
return static function (DeptracConfig $config, ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CustomSubscriber::class)
        ->tag('kernel.event_subscriber');
}
```

## Output formatter

Output formatter allows you to transform how the result of the rule application
to the dependencies is displayed to you. If none of the default implementations
in the `\Deptrac\Deptrac\DefaultBehavior\OutputFormatter` namespace is to your
liking, you can create a custom one by implementing the `OutputFormatter\OutputFormatterInterface`.

This interface has only two methods to implement: `getName(): string` that
specifies the name of the formatter that will be later used as the CLI parameter
and `finish(OutputResult $result, OutputInterface $output, OutputFormatterInput $outputFormatterInput): void`.

The `finish()` method is where you will do all of your outputting. To help you,
you are given the result of the rule application ready for outputting in
`OutputResult $result`, CLI output helper in `OutputInterface $output` and all
the optional formatter parameters in `OutputFormatterInput $outputFormatterInput`.

Once your implementation is complete, don't forget to register it in the
`deptrac.config.php` file:

```php
return static function (DeptracConfig $config, ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services
        ->set(CustomFormatter::class)
        ->tag('output_formatter')
    ;
}
```

You can call your formatter by using the `-f` or `--formatter` CLI flag with the
name you defined in the `getName()` method of your formatter.

### Baseline mapper

Connected to output formatting is also creating a baseline. One of the default
Deptrac formatters can create a baseline from the exiting `Violations`. If you
want to change the way this formatter stores the baseline information, you can
create a custom implementation of the `OutputFormatter\BaselineMapperInterface`.

```php
interface BaselineMapperInterface
{
    /**
     * Maps a grouped list of violations to a format that will be stored to a
     * file by the `baseline` formatter.
     *
     * @param array<string,list<string>> $groupedViolations
     */
    public function fromPHPListToString(array $groupedViolations): string;

    /**
     * Load the existing violation to ignore by custom mapper logic.
     *
     * @return array<string,list<string>>
     */
    public function loadViolations(): array;
}
```

All you need to do is to be able to transform the existing list of violation to
and from a PHP array to a string that can be stored to a file. Once done, don't
forget to register you custom mapper in the `deptrac.config.php` file:

```php
return static function (DeptracConfig $config, ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CustomMapper::class)
        ->args([
            '$skippedViolations' => param('skip_violations'),
        ])
    ;
    $services->alias(BaselineMapperInterface::class, CustomMapper::class);
}
```

There can be only one active mapper at the time, so you are replacing it.
