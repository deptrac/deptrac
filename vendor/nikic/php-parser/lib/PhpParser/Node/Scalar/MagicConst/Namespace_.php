<?php

declare (strict_types=1);
namespace DEPTRAC_202404\PhpParser\Node\Scalar\MagicConst;

use DEPTRAC_202404\PhpParser\Node\Scalar\MagicConst;
class Namespace_ extends MagicConst
{
    public function getName() : string
    {
        return '__NAMESPACE__';
    }
    public function getType() : string
    {
        return 'Scalar_MagicConst_Namespace';
    }
}
