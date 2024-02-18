..  include:: /include.rst.txt

.. _compiler-component:

========
Compiler
========

This library uses a simplified compiler design. This basically means that our pipeline contains less steps
than a regular compiler. But its following the same semantics.

Stages
======

Lexing and Parsing
------------------

A typical compiler will have separate lexing, syntax analysis. However the parser
was designed to do part of the lexing because of all context-dependent logic of most Markup languages.
We call this the parsing phase. This will result into an AST that is mostly close to the original source. It
might contain some optimizations for later use.

Semantic analysis and Intermediate code generation
--------------------------------------------------

The semantic analysis phase of this library is performing a number of steps to collect information of the parsed markup
language. A good example is the collection of the table of contents and the metadata of the parsed documents.
This is the moment where document node traversers are executed.

Code optimization
-----------------

Do some pre-rendering stuff, like buiding the TOC content and other rendering preparations before the real rendering starts.

Code generation
---------------

Code generation a.k.a. rendering. This is the phase where the AST is transformed into the final output format.

Extending
=========

The compiler is designed to be extended. This allows you to remove or add nodes to the AST or mutate the AST in any way
you like. Compiler extension is mostly done when you want want to do dynamic calculations based on the AST. The mutations
will not have direct impact on the rendering process. Style changes should be done in the rendering phase.

To read more about extending the compiler, see :ref:`extending_compiler`.
