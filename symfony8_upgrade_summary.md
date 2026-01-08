# Symfony 8.0 Compatibility Upgrade Summary

## 1. ConfigBuilderInterface Removal

### Problem
Symfony's `ConfigBuilderInterface` was deprecated in Symfony 7.4 and removed in Symfony 8.0.
The `DeptracConfig` class was implementing this Symfony interface.

### Root Cause
- Symfony removed the fluent PHP Config Builder format in favor of array-based configuration
- However, Deptrac uses `ConfigBuilderInterface` for its own public API (not Symfony's config system)
- Users create Deptrac configuration using `DeptracConfig` class methods

### Solution
Created a custom `ConfigBuilderInterface` within Deptrac's namespace to replace Symfony's deprecated interface.

#### Changes Made
1. Created `/src/Contract/Config/ConfigBuilderInterface.php` - Deptrac's own interface
2. Updated `DeptracConfig` to use the new Deptrac interface instead of Symfony's

### Benefits
- ✅ Maintains backward compatibility - user code doesn't change
- ✅ Works with Symfony 6.4, 7.4, and 8.0
- ✅ No dependency on deprecated Symfony components
- ✅ Clear ownership - it's now explicitly a Deptrac API contract

---

## 2. Psalm String Interpolation Crash

### Problem
Psalm crashed when analyzing files with string interpolation syntax due to incompatibility with newer php-parser versions.

### Root Cause
- String interpolation syntax (`"{$var}"`) in `AstInherit.php` caused Psalm's AST analyzer to fail
- Error: `PhpParser\Node\InterpolatedStringPart` cannot be passed where `PhpParser\Node\Expr` is expected

### Solution
Replaced string interpolation with `sprintf()` for safer parsing.

#### Changes Made
1. Updated `src/Contract/Ast/AstMap/AstInherit.php` - Replaced interpolation in `__toString()` method

### Benefits
- ✅ Psalm runs without crashes
- ✅ More explicit and maintainable string formatting
- ✅ Compatible with all php-parser versions

---

## 3. ConfigBuilderGenerator Deprecation

### Problem
`ConfigBuilderGenerator` usage in `ServiceContainerBuilder` was deprecated in Symfony 7.4 and removed in Symfony 8.0, causing Psalm warnings.

### Root Cause
- Symfony deprecated the entire config builder generation system
- `PhpFileLoader` no longer requires the `generator` parameter in Symfony 8.0
- The parameter was optional in Symfony 7.x and completely removed in 8.0

### Solution
Removed the deprecated `generator` parameter from `PhpFileLoader` instantiation.

#### Changes Made
1. Removed import of `Symfony\Component\Config\Builder\ConfigBuilderGenerator`
2. Removed `generator: new ConfigBuilderGenerator('.')` from `PhpFileLoader` constructor in `ServiceContainerBuilder.php`

### Benefits
- ✅ No deprecation warnings
- ✅ Compatible with Symfony 6.4, 7.4, and 8.0
- ✅ Cleaner code without unused parameters

---

## 4. DeptracConfig Resolution in PHP Config Files

### Problem
After removing `ConfigBuilderGenerator`, Deptrac couldn't resolve the `DeptracConfig` argument in PHP configuration files.
Error: `Could not resolve argument "Deptrac\Deptrac\Contract\Config\DeptracConfig $config" for "deptrac.php"`

### Root Cause
- Symfony's `PhpFileLoader` used `ConfigBuilderGenerator` to automatically provide config builder instances to closures
- Deptrac's `deptrac.php` config file uses closure format: `function (DeptracConfig $config, ContainerConfigurator $containerConfigurator)`
- Without the generator, the loader couldn't inject `DeptracConfig` into the closure

### Solution
Created a custom `DeptracPhpConfigLoader` that extends Symfony's `PhpFileLoader` to manually handle `DeptracConfig` injection.

#### Changes Made
1. Created `/src/Supportive/DependencyInjection/DeptracPhpConfigLoader.php` - Custom loader that:
   - Detects closures expecting `DeptracConfig` as first parameter
   - Instantiates `DeptracConfig` and `ContainerConfigurator`
   - Calls the closure with both parameters
   - Loads resulting configuration into Symfony's extension system
2. Updated `ServiceContainerBuilder.php` to use `DeptracPhpConfigLoader` instead of standard `PhpFileLoader`

### Benefits
- ✅ Backward compatible - existing `deptrac.php` configs work without changes
- ✅ No deprecated code dependencies
- ✅ Works with Symfony 6.4, 7.4, and 8.0
- ✅ Clean isolation in custom loader class

---

## Testing Results
- ✅ `make psalm` passes with no errors
- ✅ `make deptrac` passes successfully
- ✅ All configuration loading works correctly
- ✅ Full compatibility with Symfony 6.4, 7.4, and 8.0

## Summary
The project is now fully compatible with Symfony 8.0 while maintaining backward compatibility with Symfony 6.4 and 7.4.
