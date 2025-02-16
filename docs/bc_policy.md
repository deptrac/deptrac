# Backwards Compatibility Policy

Deptrac adheres to [Semantic Versioning 2.0.0](https://semver.org/spec/v2.0.0.html).
That means, we will not introduce any breaking change in a minor or patch
release starting with the first stable release 1.0.0. Within the 0.x.y major
release, we may introduce breaking changes in minor releases.

> Given a version number MAJOR.MINOR.PATCH, increment the:
>
> 1. MAJOR version when you make incompatible API changes
> 2. MINOR version when you add functionality in a backwards compatible manner
> 3. PATCH version when you make backwards compatible bug fixes

First and foremost Deptrac is a command line utility, not a library. This
governs what we consider a breaking change and might differ from your
expectations from other packages. The following policy outlines what we
consider breaking changes.

We strongly suggest reading the [Upgrade Guide](upgrade.md) for information on
any planned breaking changes and how to deal with them. If you encounter a
breaking change that is not documented, feel free to open an issue.

## Security

Security fixes may break backwards compatibility at any point. For more details
on security related issues, please refer to the [Security Guide](SECURITY.md).

## Commands

Deptrac provides a series of commands such as `analyse`. The following things
are considered breaking changes:

* Renaming or removing the command
* Renaming or removing options or arguments
* Changing the argument order
* Changing any of the expected values provided by Deptrac, e.g. formatter names
* Output (with some exceptions outlined below)

Please notice, that the output is partly controlled by your system, e.g. line
length. You might experience different output on different systems. However,
you should be able to expect the same output on the same machine between version
upgrades. We might make minor changes, such as fixing typos in a patch or minor
release, if we assume the impact to users will be low. Generally speaking,
formatters targeting CI systems, such as the Github-formatter, will be treated
more strictly than for example the graphviz dot formatter, which is assumed to
not be used directly in a CI pipeline.

## Configuration

Users of Deptrac should expect their initial configuration to work across newer
minor and patch releases. As such, we will not make any breaking changes to
configuration. Adding new (optional) configuration sections is allowed, but we
will not alter existing fields and their expected types. You can expect the
following breaking changes only to happen in a major release:

* Renaming or moving the default config file(name)
* Renaming or removing fields
* Making a previously optional field required
* Changing types of the config values

## Code

The following backwards compatibility promise extends only to code in the
`Deptrac\Deptrac\Contract\` namespace. Other code may change within a major
release, unless it is marked explicitly as `@public`. Conversely, if anything in
the Contract namespace is marked as `@internal` the BC promise does not apply.

We try to follow [Symfony's backwards compatibility promise](https://symfony.com/doc/current/contributing/code/bc.html)
as closely as possible. Please refer to their guide for a detailed explanation.

In short, our interfaces and classes will not change unexpectedly within a major
release.

## Features

Within a major version we will not unexpectedly remove or change features, e.g.
remove a collectors arguments or its name or removing it entirely.

Deptrac provides a series of extension points, e.g. events to hook into. You
can expect these events to not be renamed or existing arguments, you might
be using, to be altered within a major release.
