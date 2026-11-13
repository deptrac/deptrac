<?php

namespace FunctionCallsShadowed;

function strlen(string $string): int
{
    return 0;
}

function caller(): void
{
    strlen(strtoupper(''));
}
