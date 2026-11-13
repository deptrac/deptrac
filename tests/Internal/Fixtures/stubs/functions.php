<?php

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;

/**
 * @param resource $stream
 */
function consumes_resource($stream): bool {}

/**
 * @return resource|false a stream, or false on failure
 */
function produces_resource(string $filename) {}

/**
 * @param string $filename
 * @param resource|null $context [optional]
 */
function accepts_optional_context(string $filename, $context = null): bool {}

/**
 * @param array<int, resource> $streams
 */
function consumes_resources_in_array(array $streams): int {}

/**
 * @param resource &$stream
 */
function consumes_resource_by_reference(&$stream): void {}

function migrated_parameter(#[LanguageLevelTypeAware(['8.0' => 'MigratedHandle'], default: 'resource')] $handle): bool {}

#[LanguageLevelTypeAware(['8.1' => 'Migrated\Connection|false'], default: 'resource|false')]
function migrated_return_value(string $host) {}

function consumes_migrated_handle(MigratedHandle $handle): void {}

function produces_migrated_handle(): ?Migrated\Connection {}

/**
 * @param resource|DocumentedHandle $handle
 */
function migrated_according_to_documentation(DocumentedHandle $handle): void {}

function produces_documented_handle(): DocumentedHandle|false {}

/**
 * @param resource $stream
 */
function Mixed_Case($stream): void {}

/**
 * @param resource $handle
 */
#[PhpStormStubsElementAvailable(to: '7.4')]
function declared_for_several_php_versions($handle): void {}

#[PhpStormStubsElementAvailable(from: '8.0')]
function declared_for_several_php_versions(string $handle): void {}

/**
 * @param resource|int|string|GMP $num
 * @return resource|GMP
 */
function number_that_used_to_be_a_resource(GMP|int|string $num): GMP {}

/**
 * @return array{resource|GMP, resource|GMP}
 */
function numbers_that_used_to_be_resources(GMP $num): array {}

/**
 * @param resource $value the documentation was not updated when the type was declared
 */
function declared_type_leaves_no_room_for_resource(string $value): string {}

/**
 * @param string $string
 * @return int
 */
function no_resource(string $string): int {}

function no_documentation($value) {}
