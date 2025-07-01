---
hide:
  - navigation
  - toc
---

# Installing Deptrac using `bamarni/composer-bin-plugin`

Jun 28, 2025 - 3 min read

---

Have you tried installing Deptrac as a `composer --dev` dependency and
encountered Composer conflicts? This blog post is for you! We will show you how
to install Deptrac as a Composer dependency in a separate scoped installation
that will not conflict with any of your existing Composer dependencies.

---

Deptrac as a library itself has its own dependencies as many PHP packages do. This may become a problem if the dependencies of Deptrac are not compatible with already existing dependencies in your project. Most notably, Deptrac depends on a number of `symfony` packages.

Imagine a situation where you depend on newer major version of `symfony/console` than Deptrac does. For example your `composer.json` file looks like this:

```json
{
    "require": {
        "symfony/console": "^7.0"
    }
}
```

If you try to install the newest version of Deptrac at the time of writing
(`4.0.x-dev`), Composer won't let you:

```console
$ composer require --dev deptrac/deptrac:4.0.x-dev -W
./composer.json has been updated
Running composer update deptrac/deptrac --with-all-dependencies
Loading composer repositories with package information
Updating dependencies
Your requirements could not be resolved to an installable set of packages.

  Problem 1
    - Root composer.json requires deptrac/deptrac 4.0.x-dev -> satisfiable by deptrac/deptrac[4.0.x-dev].
    - deptrac/deptrac 4.0.x-dev requires symfony/console ^6.0 -> found symfony/console[v6.0.0, ..., v6.4.23] but it conflicts with your root composer.json require (^7.0).


Installation failed, reverting ./composer.json and ./composer.lock to their original content.
```

Luckily, there is a solution for this exact problem of conflicting dev
dependencies. It comes in the form of the [bamarni/composer-bin-plugin](https://github.com/bamarni/composer-bin-plugin). First, let's install the plugin:

```console
$ composer require --dev bamarni/composer-bin-plugin
./composer.json has been updated
Running composer update bamarni/composer-bin-plugin
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
  - Locking bamarni/composer-bin-plugin (1.8.2)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 1 install, 0 updates, 0 removals
bamarni/composer-bin-plugin contains a Composer plugin which is currently not in your allow-plugins config. See https://getcomposer.org/allow-plugins
Do you trust "bamarni/composer-bin-plugin" to execute code and wish to enable it now? (writes "allow-plugins" to composer.json) [y,n,d,?] y
  - Installing bamarni/composer-bin-plugin (1.8.2): Extracting archive
Generating autoload files
[bamarni-bin] The setting "extra.bamarni-bin.bin-links" will be set to "false" from 2.x onwards. If you wish to keep it to "true", you need to set it explicitly.
[bamarni-bin] The setting "extra.bamarni-bin.forward-command" will be set to "true" from 2.x onwards. If you wish to keep it to "false", you need to set it explicitly.
8 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
No security vulnerability advisories found.
Using version ^1.8 for bamarni/composer-bin-plugin
```

Now you can install a scoped version of Deptrac in the `deptrac` namespace:

```console
$ composer bin deptrac require --dev deptrac/deptrac:4.0.x-dev -W
[bamarni-bin] Checking namespace vendor-bin/deptrac
./composer.json has been updated
Running composer update deptrac/deptrac --with-all-dependencies
Loading composer repositories with package information
Updating dependencies
Lock file operations: 29 installs, 0 updates, 0 removals
  - Locking composer/pcre (3.3.2)
  - Locking composer/xdebug-handler (3.0.5)
  - Locking deptrac/deptrac (4.0.x-dev 189fd13)
  - Locking doctrine/deprecations (1.1.5)
  - Locking jetbrains/phpstorm-stubs (v2024.1)
  - Locking nikic/php-parser (v5.5.0)
  - Locking phpdocumentor/graphviz (2.1.0)
  - Locking phpdocumentor/reflection-common (2.2.0)
  - Locking phpdocumentor/type-resolver (1.10.0)
  - Locking phpstan/phpdoc-parser (1.33.0)
  - Locking psr/container (2.0.2)
  - Locking psr/event-dispatcher (1.0.0)
  - Locking psr/log (3.0.2)
  - Locking symfony/config (v6.4.22)
  - Locking symfony/console (v6.4.23)
  - Locking symfony/dependency-injection (v6.4.23)
  - Locking symfony/deprecation-contracts (v3.6.0)
  - Locking symfony/event-dispatcher (v6.4.13)
  - Locking symfony/event-dispatcher-contracts (v3.6.0)
  - Locking symfony/filesystem (v6.4.13)
  - Locking symfony/finder (v6.4.17)
  - Locking symfony/polyfill-ctype (v1.32.0)
  - Locking symfony/polyfill-intl-grapheme (v1.32.0)
  - Locking symfony/polyfill-intl-normalizer (v1.32.0)
  - Locking symfony/polyfill-mbstring (v1.32.0)
  - Locking symfony/service-contracts (v3.6.0)
  - Locking symfony/string (v7.3.0)
  - Locking symfony/var-exporter (v7.3.0)
  - Locking symfony/yaml (v6.4.23)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 29 installs, 0 updates, 0 removals
  - Downloading deptrac/deptrac (4.0.x-dev 189fd13)
  - Installing composer/pcre (3.3.2): Extracting archive
  - Installing symfony/polyfill-ctype (v1.32.0): Extracting archive
  - Installing symfony/deprecation-contracts (v3.6.0): Extracting archive
  - Installing symfony/yaml (v6.4.23): Extracting archive
  - Installing symfony/finder (v6.4.17): Extracting archive
  - Installing symfony/polyfill-mbstring (v1.32.0): Extracting archive
  - Installing symfony/filesystem (v6.4.13): Extracting archive
  - Installing psr/event-dispatcher (1.0.0): Extracting archive
  - Installing symfony/event-dispatcher-contracts (v3.6.0): Extracting archive
  - Installing symfony/event-dispatcher (v6.4.13): Extracting archive
  - Installing symfony/var-exporter (v7.3.0): Extracting archive
  - Installing psr/container (2.0.2): Extracting archive
  - Installing symfony/service-contracts (v3.6.0): Extracting archive
  - Installing symfony/dependency-injection (v6.4.23): Extracting archive
  - Installing symfony/polyfill-intl-normalizer (v1.32.0): Extracting archive
  - Installing symfony/polyfill-intl-grapheme (v1.32.0): Extracting archive
  - Installing symfony/string (v7.3.0): Extracting archive
  - Installing symfony/console (v6.4.23): Extracting archive
  - Installing symfony/config (v6.4.22): Extracting archive
  - Installing phpstan/phpdoc-parser (1.33.0): Extracting archive
  - Installing phpdocumentor/reflection-common (2.2.0): Extracting archive
  - Installing doctrine/deprecations (1.1.5): Extracting archive
  - Installing phpdocumentor/type-resolver (1.10.0): Extracting archive
  - Installing phpdocumentor/graphviz (2.1.0): Extracting archive
  - Installing nikic/php-parser (v5.5.0): Extracting archive
  - Installing jetbrains/phpstorm-stubs (v2024.1): Extracting archive
  - Installing psr/log (3.0.2): Extracting archive
  - Installing composer/xdebug-handler (3.0.5): Extracting archive
  - Installing deptrac/deptrac (4.0.x-dev 189fd13): Extracting archive
Generating autoload files
18 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
No security vulnerability advisories found.
```

Your Deptrac executable is now in
`vendor-bin/deptrac/vendor/deptrac/deptrac/deptrac`. To make calling it easier,
you might want to create a Composer alias for it in your `composer.json` file:

```json
{
  "scripts": {
    "deptrac": "vendor-bin/deptrac/vendor/deptrac/deptrac/deptrac -c deptrac.php --report-uncovered"
  }
}
```

You can now call `composer deptrac` to run Deptrac itself.

---
Do you like Deptrac and use it every day? [Consider supporting further development of Deptrac by sponsoring me on GitHub Sponsors](https://github.com/sponsors/patrickkusebauch). I’d really appreciate it!

Author: [patrickkusebauch](https://github.com/patrickkusebauch)
