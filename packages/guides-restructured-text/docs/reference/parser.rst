######
Parser
######

The :php:class:`\phpDocumentor\Guides\RestructuredText\MarkupLanguageParser` is the core of this component. The parser
itself is basically nothing more than a starting point to trigger the actual parsing. The parser is using a ``startingRule``
to initiate the parsing of a document. The final result of the parser is a tree of nodes representing the content of
the parsed document. We call this node structure an abstract syntax tree (AST).

As the name states it, the nodes produced by the parser are forming a nested tree of objects. This tree can be used
to manipulate or render the content of a document in a different format. Rendering a document is not part of this library.

The ``startingRule`` is an implementation of :php:class:`\phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule`
able to produce a :php:class:`\phpDocumentor\Guides\Nodes\DocumentNode`.

*****
Rules
*****

Rules are a state of the parser with behavior to produce a certain node. Each rule will produce exactly one node.
And will consume lines from the :php:class:`\phpDocumentor\Guides\RestructuredText\Parser\LinesIterator` until it detects
the end of the node content.

.. info::

   In most cases, the end of a node is a white line or the end of an indentation level. However, it is up to the Rule
   when to stop consuming lines. When returning a node, the line iterator must be at the next line to process. This can
   either be a content line or an empty line.

As rules can have nested nodes, they can also require a set of other rules to call. It is recommended to use the
:php:class:`\phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleContainer`.

The nodes produced by a rule must be in a state that can be stored to cache. To speed up the parsing process, documents
are stored into a cache-storage by the consuming library.

Extending the parser
====================

The parser can be extended by adding more :ref:`directives <adding_directives>` or add your own :ref:`text roles <adding_text_roles>`.
This will give you the ability to implement your own custom rendering in documents.

Another way to extend the parser rules to the :php:class:`\phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule`.
right now there is no configuration available to configure the parser from scratch. But we might introduce that in the
future to make it more flexible and add your own rules.

*********************
DocumentParserContext
*********************

During the parsing process, there are a number of context-dependent settings we need to store. For example, the type of
characters used in a document to mark the level of a heading. As the rules used by the parser are context-independent, they
should never keep any contextual information in their own state. And all contextual information must be stored in the
``DocumentParserContext``.

When needed, the ``DocumentParserContext`` can also provide the current processed document. Or give access to the
``ParserContext`` that is shared over all parsed documents.
