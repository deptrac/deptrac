<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Ast;

/**
 * Specifies the type of AST dependency.
 *
 * You can use this information to enrich the displayed output to the user in
 * your output formatter.
 */
enum DependencyType : string
{
    case USE = 'use';
    case INHERIT = 'inherit';
    case RETURN_TYPE = 'returntype';
    case PARAMETER = 'parameter';
    case NEW = 'new';
    case STATIC_PROPERTY = 'static_property';
    case STATIC_METHOD = 'static_method';
    case INSTANCEOF = 'instanceof';
    case CATCH = 'catch';
    // Class-like property or @var tag annotation
    case VARIABLE = 'variable';
    case THROW = 'throw';
    case CONST = 'const';
    case ANONYMOUS_CLASS_EXTENDS = 'anonymous_class_extends';
    case ANONYMOUS_CLASS_IMPLEMENTS = 'anonymous_class_implements';
    case ANONYMOUS_CLASS_TRAIT = 'anonymous_class_trait';
    case ATTRIBUTE = 'attribute';
    case SUPERGLOBAL_VARIABLE = 'superglobal_variable';
    // Function call that could not be resolved at parse-time and can only be
    // resolved at run-time. Usually means an internal PHP function call.
    case UNRESOLVED_FUNCTION_CALL = 'unresolved_function_call';
}
