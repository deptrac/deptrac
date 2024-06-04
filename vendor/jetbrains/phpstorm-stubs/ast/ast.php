<?php

namespace DEPTRAC_INTERNAL\ast;

/** Metadata entry for a single AST kind, as returned by ast\get_metadata(). */
class Metadata
{
    /** @var string[] List of supported flags. The flags are given as names of constants, such as "ast\flags\TYPE_STRING". */
    public $flags;
    /** @var bool Whether the flags are exclusive or combinable. Exclusive flags should be checked using ===, while combinable flags should be checked using &. */
    public $flagsCombinable;
    /** @var int AST node kind (one of the `ast\AST_*` constants). */
    public $kind;
    /** @var string Name of the node kind (e.g. "AST_NAME"). */
    public $name;
}
/** This class describes a single node in a PHP AST. */
class Node
{
    /** @var array Child nodes (may be empty) */
    public $children;
    /** @var int Line the node ends on. */
    public $endLineno;
    /** @var int Certain node kinds have flags that can be set. These will be a bitfield of `ast\flags\*` constants. */
    public $flags;
    /** @var int AST Node Kind. Values are one of `ast\AST_*` constants. */
    public $kind;
    /** @var int Line the node starts on. */
    public $lineno;
    /**
     * A constructor which accepts any types for the properties.
     * For backwards compatibility reasons, all values are optional and can be any type, and properties default to null.
     *
     * @param int|null $kind
     * @param int|null $flags
     * @param array|null $children
     * @param int|null $lineno
     */
    public function __construct(?int $kind = null, ?int $flags = null, ?array $children = null, ?int $lineno = null)
    {
    }
}
/**
 * @param int $kind AST_* constant value defining the kind of an AST node
 * @return string String representation of AST kind value
 */
function get_kind_name(int $kind) : string
{
}
/**
 * Provides metadata for the AST kinds.
 *
 * The returned array is a map from AST kind to a Metadata object.
 *
 * @return Metadata[] Metadata about AST kinds
 */
function get_metadata() : array
{
}
/**
 * Returns currently supported AST versions.
 *
 * @param bool $exclude_deprecated Whether to exclude deprecated versions
 * @return int[] Array of supported AST versions
 */
function get_supported_versions(bool $exclude_deprecated = \false) : array
{
}
/**
 * @param int $kind AST_* constant value defining the kind of an AST node
 * @return bool Returns true if AST kind uses flags
 */
function kind_uses_flags(int $kind) : bool
{
}
/**
 * Parses code string and returns an AST root node.
 *
 * @param string $code Code to parse
 * @param int    $version  AST version
 * @param string $filename file name for ParseError messages
 * @return Node Root node of AST
 *
 * @see https://github.com/nikic/php-ast for version information
 */
function parse_code(string $code, int $version, string $filename = 'string code') : Node
{
}
/**
 * Parses code from a file and returns an AST root node.
 *
 * @param string $filename file name to parse
 * @param int    $version  AST version
 * @return Node Root node of AST
 *
 * @see https://github.com/nikic/php-ast for version information
 */
function parse_file(string $filename, int $version) : Node
{
}
\define('ast\\AST_ARG_LIST', 128);
\define('ast\\AST_ARRAY', 129);
\define('ast\\AST_ARRAY_ELEM', 526);
\define('ast\\AST_ARROW_FUNC', 71);
\define('ast\\AST_ASSIGN', 518);
\define('ast\\AST_ASSIGN_OP', 520);
\define('ast\\AST_ASSIGN_REF', 519);
\define('ast\\AST_ATTRIBUTE', 547);
\define('ast\\AST_ATTRIBUTE_GROUP', 146);
\define('ast\\AST_ATTRIBUTE_LIST', 145);
\define('ast\\AST_BINARY_OP', 521);
\define('ast\\AST_BREAK', 286);
\define('ast\\AST_CALL', 516);
\define('ast\\AST_CAST', 261);
\define('ast\\AST_CATCH', 773);
\define('ast\\AST_CATCH_LIST', 135);
\define('ast\\AST_CLASS', 70);
\define('ast\\AST_CLASS_CONST', 517);
\define('ast\\AST_CLASS_CONST_DECL', 140);
\define('ast\\AST_CLASS_CONST_GROUP', 546);
\define('ast\\AST_CLASS_NAME', 276);
\define('ast\\AST_CLONE', 266);
\define('ast\\AST_CLOSURE', 68);
\define('ast\\AST_CLOSURE_USES', 137);
\define('ast\\AST_CLOSURE_VAR', 2049);
\define('ast\\AST_CONDITIONAL', 771);
\define('ast\\AST_CONST', 257);
\define('ast\\AST_CONST_DECL', 139);
\define('ast\\AST_CONST_ELEM', 776);
\define('ast\\AST_CONTINUE', 287);
\define('ast\\AST_DECLARE', 538);
\define('ast\\AST_DIM', 512);
\define('ast\\AST_DO_WHILE', 534);
\define('ast\\AST_ECHO', 283);
\define('ast\\AST_EMPTY', 262);
\define('ast\\AST_ENCAPS_LIST', 130);
\define('ast\\AST_EXIT', 267);
\define('ast\\AST_EXPR_LIST', 131);
\define('ast\\AST_FOR', 1024);
\define('ast\\AST_FOREACH', 1025);
\define('ast\\AST_FUNC_DECL', 67);
\define('ast\\AST_GLOBAL', 277);
\define('ast\\AST_GOTO', 285);
\define('ast\\AST_GROUP_USE', 545);
\define('ast\\AST_HALT_COMPILER', 282);
\define('ast\\AST_IF', 133);
\define('ast\\AST_IF_ELEM', 535);
\define('ast\\AST_INCLUDE_OR_EVAL', 269);
\define('ast\\AST_INSTANCEOF', 528);
\define('ast\\AST_ISSET', 263);
\define('ast\\AST_LABEL', 280);
\define('ast\\AST_LIST', 255);
\define('ast\\AST_MAGIC_CONST', 0);
\define('ast\\AST_MATCH', 548);
\define('ast\\AST_MATCH_ARM', 549);
\define('ast\\AST_MATCH_ARM_LIST', 147);
\define('ast\\AST_METHOD', 69);
\define('ast\\AST_METHOD_CALL', 768);
\define('ast\\AST_METHOD_REFERENCE', 541);
\define('ast\\AST_NAME', 2048);
\define('ast\\AST_NAMED_ARG', 550);
\define('ast\\AST_NAMESPACE', 542);
\define('ast\\AST_NAME_LIST', 141);
\define('ast\\AST_NEW', 527);
\define('ast\\AST_NULLABLE_TYPE', 2050);
\define('ast\\AST_NULLSAFE_METHOD_CALL', 769);
\define('ast\\AST_NULLSAFE_PROP', 514);
\define('ast\\AST_PARAM', 1280);
\define('ast\\AST_PARAM_LIST', 136);
\define('ast\\AST_POST_DEC', 274);
\define('ast\\AST_POST_INC', 273);
\define('ast\\AST_PRE_DEC', 272);
\define('ast\\AST_PRE_INC', 271);
\define('ast\\AST_PRINT', 268);
\define('ast\\AST_PROP', 513);
\define('ast\\AST_PROP_DECL', 138);
\define('ast\\AST_PROP_ELEM', 775);
\define('ast\\AST_PROP_GROUP', 774);
\define('ast\\AST_REF', 281);
\define('ast\\AST_RETURN', 279);
\define('ast\\AST_SHELL_EXEC', 265);
\define('ast\\AST_STATIC', 532);
\define('ast\\AST_STATIC_CALL', 770);
\define('ast\\AST_STATIC_PROP', 515);
\define('ast\\AST_STMT_LIST', 132);
\define('ast\\AST_SWITCH', 536);
\define('ast\\AST_SWITCH_CASE', 537);
\define('ast\\AST_SWITCH_LIST', 134);
\define('ast\\AST_THROW', 284);
\define('ast\\AST_TRAIT_ADAPTATIONS', 142);
\define('ast\\AST_TRAIT_ALIAS', 544);
\define('ast\\AST_TRAIT_PRECEDENCE', 540);
\define('ast\\AST_TRY', 772);
\define('ast\\AST_TYPE', 1);
\define('ast\\AST_TYPE_UNION', 144);
\define('ast\\AST_UNARY_OP', 270);
\define('ast\\AST_UNPACK', 258);
\define('ast\\AST_UNSET', 278);
\define('ast\\AST_USE', 143);
\define('ast\\AST_USE_ELEM', 543);
\define('ast\\AST_USE_TRAIT', 539);
\define('ast\\AST_VAR', 256);
\define('ast\\AST_WHILE', 533);
\define('ast\\AST_YIELD', 529);
\define('ast\\AST_YIELD_FROM', 275);
namespace DEPTRAC_INTERNAL\ast\flags;

\define('ast\\flags\\ARRAY_ELEM_REF', 1);
\define('ast\\flags\\ARRAY_SYNTAX_LIST', 1);
\define('ast\\flags\\ARRAY_SYNTAX_LONG', 2);
\define('ast\\flags\\ARRAY_SYNTAX_SHORT', 3);
\define('ast\\flags\\BINARY_ADD', 1);
\define('ast\\flags\\BINARY_BITWISE_AND', 10);
\define('ast\\flags\\BINARY_BITWISE_OR', 9);
\define('ast\\flags\\BINARY_BITWISE_XOR', 11);
\define('ast\\flags\\BINARY_BOOL_AND', 259);
\define('ast\\flags\\BINARY_BOOL_OR', 258);
\define('ast\\flags\\BINARY_BOOL_XOR', 15);
\define('ast\\flags\\BINARY_COALESCE', 260);
\define('ast\\flags\\BINARY_CONCAT', 8);
\define('ast\\flags\\BINARY_DIV', 4);
\define('ast\\flags\\BINARY_IS_EQUAL', 18);
\define('ast\\flags\\BINARY_IS_GREATER', 256);
\define('ast\\flags\\BINARY_IS_GREATER_OR_EQUAL', 257);
\define('ast\\flags\\BINARY_IS_IDENTICAL', 16);
\define('ast\\flags\\BINARY_IS_NOT_EQUAL', 19);
\define('ast\\flags\\BINARY_IS_NOT_IDENTICAL', 17);
\define('ast\\flags\\BINARY_IS_SMALLER', 20);
\define('ast\\flags\\BINARY_IS_SMALLER_OR_EQUAL', 21);
\define('ast\\flags\\BINARY_MOD', 5);
\define('ast\\flags\\BINARY_MUL', 3);
\define('ast\\flags\\BINARY_POW', 12);
\define('ast\\flags\\BINARY_SHIFT_LEFT', 6);
\define('ast\\flags\\BINARY_SHIFT_RIGHT', 7);
\define('ast\\flags\\BINARY_SPACESHIP', 170);
\define('ast\\flags\\BINARY_SUB', 2);
\define('ast\\flags\\CLASS_ABSTRACT', 64);
\define('ast\\flags\\CLASS_ANONYMOUS', 4);
\define('ast\\flags\\CLASS_FINAL', 32);
\define('ast\\flags\\CLASS_INTERFACE', 1);
\define('ast\\flags\\CLASS_TRAIT', 2);
\define('ast\\flags\\CLOSURE_USE_REF', 1);
\define('ast\\flags\\DIM_ALTERNATIVE_SYNTAX', 2);
\define('ast\\flags\\EXEC_EVAL', 1);
\define('ast\\flags\\EXEC_INCLUDE', 2);
\define('ast\\flags\\EXEC_INCLUDE_ONCE', 4);
\define('ast\\flags\\EXEC_REQUIRE', 8);
\define('ast\\flags\\EXEC_REQUIRE_ONCE', 16);
\define('ast\\flags\\FUNC_GENERATOR', 16777216);
\define('ast\\flags\\FUNC_RETURNS_REF', 4096);
\define('ast\\flags\\MAGIC_CLASS', 378);
\define('ast\\flags\\MAGIC_DIR', 377);
\define('ast\\flags\\MAGIC_FILE', 376);
\define('ast\\flags\\MAGIC_FUNCTION', 381);
\define('ast\\flags\\MAGIC_LINE', 375);
\define('ast\\flags\\MAGIC_METHOD', 380);
\define('ast\\flags\\MAGIC_NAMESPACE', 382);
\define('ast\\flags\\MAGIC_TRAIT', 379);
\define('ast\\flags\\MODIFIER_ABSTRACT', 64);
\define('ast\\flags\\MODIFIER_FINAL', 32);
\define('ast\\flags\\MODIFIER_PRIVATE', 4);
\define('ast\\flags\\MODIFIER_PROTECTED', 2);
\define('ast\\flags\\MODIFIER_PUBLIC', 1);
\define('ast\\flags\\MODIFIER_STATIC', 16);
\define('ast\\flags\\NAME_FQ', 0);
\define('ast\\flags\\NAME_NOT_FQ', 1);
\define('ast\\flags\\NAME_RELATIVE', 2);
\define('ast\\flags\\PARAM_MODIFIER_PRIVATE', 4);
\define('ast\\flags\\PARAM_MODIFIER_PROTECTED', 2);
\define('ast\\flags\\PARAM_MODIFIER_PUBLIC', 1);
\define('ast\\flags\\PARAM_REF', 8);
\define('ast\\flags\\PARAM_VARIADIC', 16);
\define('ast\\flags\\PARENTHESIZED_CONDITIONAL', 1);
\define('ast\\flags\\RETURNS_REF', 4096);
\define('ast\\flags\\TYPE_ARRAY', 7);
\define('ast\\flags\\TYPE_BOOL', 17);
\define('ast\\flags\\TYPE_CALLABLE', 12);
\define('ast\\flags\\TYPE_DOUBLE', 5);
\define('ast\\flags\\TYPE_FALSE', 2);
\define('ast\\flags\\TYPE_ITERABLE', 13);
\define('ast\\flags\\TYPE_LONG', 4);
\define('ast\\flags\\TYPE_MIXED', 16);
\define('ast\\flags\\TYPE_NULL', 1);
\define('ast\\flags\\TYPE_OBJECT', 8);
\define('ast\\flags\\TYPE_STATIC', 15);
\define('ast\\flags\\TYPE_STRING', 6);
\define('ast\\flags\\TYPE_VOID', 14);
\define('ast\\flags\\UNARY_BITWISE_NOT', 13);
\define('ast\\flags\\UNARY_BOOL_NOT', 14);
\define('ast\\flags\\UNARY_MINUS', 262);
\define('ast\\flags\\UNARY_PLUS', 261);
\define('ast\\flags\\UNARY_SILENCE', 260);
\define('ast\\flags\\USE_CONST', 4);
\define('ast\\flags\\USE_FUNCTION', 2);
\define('ast\\flags\\USE_NORMAL', 1);
