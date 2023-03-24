#############
Inline markup
#############

ReStructuredText format has an extendable syntax regarding `text-roles`_. To be able to make it possible for
developers to extend the way text is processed we have a extendable parser to process text in nodes.

The internal node of a paragraph is called a ``SpanNode``. This is the smallest node we know in our AST. But as RST is
supporting inline markup we needed some more levels inside this node. This is what we called :php:class:`\phpDocumentor\Guides\Span\SpanToken`.
The ``SpanNode`` is the only node containing these tokens representing the text roles.

The SpanNode is produced by the :php:class:`\phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkupRule`.
The diagram below shows a small design overview of the ``SpanNode``

.. uml::

    class SpanNode
    class SpanToken

    class LiteralToken
    class CrossReferenceToken
    class EmphasisToken

    EmphasisToken -up-|> SpanToken
    CrossReferenceToken -up-|> SpanToken
    LiteralToken -up-|> SpanToken

    SpanNode --* SpanToken

**********
The parser
**********

As the document parsing is focused on a line-by-line approach spans do not fit in this design. So we had to implement
a different type of parser, being able to process text. To do this we tokenize the content of a span into tokens.
each token starting with a ``space`` or linestart. and ending with a ``space`` or line end.

.. contents:: maybe we should move the block below into a docblock as it is describing code. I don't like it here.

After input text has been tokenized the parser will iterate over the ``TextRoles`` to find a matching ``TextRole``.
When a ``TextRole`` applies it should consume tokens until it doesn't apply anymore. And return a ``Token``. If the ending
node is not found it should return a null. This will cause the parser to continue the iteration until a matching ``TextRole``
is found.

The default ``TextRole`` representing plain text is allways applied as a fallback when no other nodes are matching a token.


*****************
Adding text roles
*****************

<tbd>

.. _text-roles: https://docutils.sourceforge.io/docs/ref/rst/roles.html
