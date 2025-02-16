<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PhpParser\Lexer\TokenEmulator;

use DEPTRAC_INTERNAL\PhpParser\PhpVersion;
final class PropertyTokenEmulator extends KeywordEmulator
{
    public function getPhpVersion() : PhpVersion
    {
        return PhpVersion::fromComponents(8, 4);
    }
    public function getKeywordString() : string
    {
        return '__property__';
    }
    public function getKeywordToken() : int
    {
        return \DEPTRAC_INTERNAL\T_PROPERTY_C;
    }
}
