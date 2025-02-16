===============
Getting started
===============

On this page you will find a brief introduction on how to use the TypeResolver in your project.

Installation
============

The TypeResolver is available on Packagist and can be installed using Composer:

.. code:: bash
    composer require phpdocumentor/type-resolver


General usage
===========

After you installed the TypeResolver you can use it in your project. This can be done by creating a new instance
of the :php:class:`\phpDocumentor\Reflection\TypeResolver` class and calling
:php:method:`\phpDocumentor\Reflection\TypeResolver::resolve()` with the type you want to resolve.

.. code:: php
    $typeResolver = new \phpDocumentor\Reflection\TypeResolver();
    $type = $typeResolver->resolve('string');
    echo get_class($type); // phpDocumentor\Reflection\Types\String_

The real power of this resolver is in its capability to expand partial class names into fully qualified class names;
but in order to do that we need an additional :php:class:`\phpDocumentor\Reflection\Types\Context` class that
will inform the resolver in which namespace the given expression occurs and which namespace aliases (or imports) apply.

Read more about the Context class in the next section.
