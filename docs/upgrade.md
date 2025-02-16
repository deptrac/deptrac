# Upgrade from 1.0.2 to 2.0.0

### Dropped functionality

- "Old" collector configurations using specific keys for each collector no longer triggers deprecation warning and no longer work. For fixes and affected collectors, see https://github.com/deptrac/deptrac/issues/800
- Config key `use_relative_path_from_depfile` was unused internally and has been dropped
- Collector `className` has been dropped. It was aliased to `classLike` internally. Going forward, use `classLike` collector instead.
- `%depfileDirectory%` config variable has been dropped. use `%projectDirectory%` instead.

### Known BC breaks
- Newly exceptions are now exposed in the contract (https://github.com/deptrac/deptrac/pull/1079)
- Updated result generation, changing contract signature (https://github.com/deptrac/deptrac/pull/1091)
- `CollectorInterface` can now throw new exception (https://github.com/deptrac/deptrac/pull/1103/files#diff-c8a7cf839a6a42987513abd863ee41f21222cc7ea85f17b38f6ccc6c7eba384f)
- Changed output of `ConsoleFormatter` (https://github.com/deptrac/deptrac/pull/1105)
- Changed default dependency emitters from `CLASS_TOKEN` + `USE_TOKEN` to `CLASS_TOKEN` + `FUNCTION_TOKEN`. You can get the old behaviour by explicitly specifying the old emitters in your config file.
- Method `addWarnings` was removed from `AnalysisResult`. Use `addWarning` instead.
- Method `isViolationSkipped` was removed from `EventHelper`. Use `addSkippableViolation` instead.

# Upgrade from 0.20 to 0.21

## Depfile (Configuration File)

In order to fix an issue where the same parameter from an imported file was
being replaced instead of merged, we needed to reinstate the semantic
configuration that was previously removed. We recommend switching back to
semantic configuration, especially if you rely on imports.

The following parameters can now be moved to `deptrac:`:

   * paths
   * exclude_files
   * layers
   * ruleset
   * skip_violations
   * formatters
   * analyser
   * use_relative_path_from_depfile
   * ignore_uncovered_internal_classes

The examples and documentation were updated accordingly.

# Upgrade from 0.19 to 0.20

## Commands

* You must now use the new `--config-file` option instead of providing the
  configuration file (Depfile) as command argument

## Depfile (Configuration File)

 * The `baseline` parameter was removed. You can use `imports` instead.
 * The `ruleset` is no longer checked for undefined layers. They will be
   silently ignored instead.
 * The parameter `use_relative_path_from_depfile` no longer exists. It is
   replaced by a `projectDirectory` parameter, which by default points to
   `%depfileDirectory%` and can be changed to `%currentWorkingDirectory%` or any
   other base directory you want to use as reference for relative paths.

## Baseline Formatter

* The default filename created by the baseline formatter changed.
    ```
    From: depfile.baseline.yml
    To:   deptrac.baseline.yaml
    ```
If you are not using the `-o|--output=` option, then you will end up with 2 files
(old and new one) and likely import the old one in your main deptrac.yaml. You
can avoid this by using the `-o` option or updating your deptrac.yaml and
removing the old baseline file.

# Upgrade from 0.18 to 0.19

## Depfile (Configuration File)

### What?

* The location of the default configuration file has changed.

    ```
    From: ./depfile.yaml
    To:   ./deptrac.yaml
    ```

* The configuration inside the file must now be nested under `parameters:`
  except for imports. See docs for examples.

### How?

In your `depfile.yaml` you can just add a new section `parameters:` at the top
and then indent the remaining config 1 level under this section. The only
exception is `imports` which should stay on its current level and should not be
nested under parameters.

Example:
From: https://github.com/deptrac/deptrac/blob/0.18.0/depfile.yaml
To: https://github.com/deptrac/deptrac/blob/0.19.0/deptrac.yaml

After that, you can rename the file to `deptrac.yaml` to avoid the displayed
warning and ensure the file will automatically be loaded in future versions.
Alternatively you can keep your filename and make sure you load it via the new
`--config-file` option in the relevant commands instead of as an argument.

### Why?

In the future, we want to allow adding services to the configuration to extend
Deptrac's functionality, e.g. by providing custom collectors. That is why we
must separate the config into dedicated sections parameters and services.
