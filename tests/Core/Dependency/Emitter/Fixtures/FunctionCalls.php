<?php

namespace FunctionCalls {
    use function Elsewhere\fopen as fopenElsewhere;

    function strlen(string $string): int
    {
        return 0;
    }

    function caller(callable $callable): void
    {
        fopen('php://memory', 'r');
        \fclose(STDIN);
        FWRITE(STDOUT, '');
        strlen('');
        \strlen('');
        globalHelper();
        \Brotli\compress_init();
        notDeclaredAnywhere();
        \Elsewhere\fopen();
        fopenElsewhere();
        namespace\fread();
        $callable();
        acos(cos(1.0));
    }

    final class Caller
    {
        public function call(): void
        {
            file_get_contents('php://memory');
        }
    }

    readfile('php://memory');
}

namespace {
    function globalHelper(): void {}
}
