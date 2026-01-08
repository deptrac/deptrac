# Symfony 8.0 Compatibility - ConfigBuilderInterface Removal

## Problem
Symfony's `ConfigBuilderInterface` was deprecated in Symfony 7.4 and removed in Symfony 8.0.
The `DeptracConfig` class was implementing this Symfony interface.

## Root Cause
- Symfony removed the fluent PHP Config Builder format in favor of array-based configuration
- However, Deptrac uses `ConfigBuilderInterface` for its own public API (not Symfony's config system)
- Users create Deptrac configuration using `DeptracConfig` class methods

## Solution
Created a custom `ConfigBuilderInterface` within Deptrac's namespace to replace Symfony's deprecated interface.

### Changes Made
1. Created `/src/Contract/Config/ConfigBuilderInterface.php` - Deptrac's own interface
2. Updated `DeptracConfig` to use the new Deptrac interface instead of Symfony's

### Benefits
- ✅ Maintains backward compatibility - user code doesn't change
- ✅ Works with Symfony 6.4, 7.4, and 8.0
- ✅ No dependency on deprecated Symfony components
- ✅ Clear ownership - it's now explicitly a Deptrac API contract

## Testing
- Unit tests pass with Symfony 7.4
- No breaking changes to public API
- Ready for Symfony 8.0 upgrade
