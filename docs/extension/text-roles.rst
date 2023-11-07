..  include:: /include.rst.txt

==========
Text Roles
==========

If restructured-text parsing is used, custom `text roles <https://docutils.sourceforge.io/docs/ref/rst/roles.html>`__
can be defined.

If the text role does not need special parsing you can use the generic template. In this case it is sufficient
to introduce a new template in path :file:`resources/template/html/guides/inline/textroles/`.
See also :ref:`basic-text-role`.

For more complex examples you can implement `\phpDocumentor\Guides\RestructuredText\TextRoles\TextRole`
and let the method `processNode` return a custom `\phpDocumentor\Guides\Nodes\InlineToken`. If this token extends
`\phpDocumentor\Guides\Nodes\GenericTextRoleToken` the template will be automatically resolved by the name of the type.
For more control rendering you can also implement your own token renderer.

Complex text roles that need processing during compilation can be adjusted in the passes. Have a look at the
`\phpDocumentor\Guides\Compiler\Passes\ReferenceResolverPass` for an example.

.. _basic-text-role:

Example: Introduce a basic custom text role
===========================================

Let us assume we want to introduce a text role `yaml-code`.  Create a new template
:file:`resources/template/html/guides/inline/textroles/yaml-code.twig.html`:

..  code-block:: html

    <code class="yaml some-styling">{{ textrole.content }}</code>

Example: Introduce an extended custom text role
===============================================

#.  Extend class `\phpDocumentor\Guides\RestructuredText\TextRoles\GenericTextRoleToken`. You can have a look at
    `\phpDocumentor\Guides\RestructuredText\TextRoles\AbbreviationTextRole` for an example.
#.  Register the new class with the tag `phpdoc.guides.parser.rst.text_role` in the dependency injection configuration.
#.  Create a template using your introduced additional values.
