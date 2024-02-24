
..  _guides-xml:

==========
guides.xml
==========

..  todo: Add general docs about guides.xml

..  _guides-xml-add-config-extension:

Add a configuration option (Extension)
======================================

Extension authors can add additional configuration options in the
:xml:`extension` tag (allowed to occur multiple times):

..  code-block:: xml
    :caption: guides.xml

    <extension class="\T3Docs\Typo3DocsTheme\DependencyInjection\Typo3DocsThemeExtension"
               project-home="https://docs.typo3.org/"
               project-contact="https://typo3.slack.com/archives/C028JEPJL"/>

These must then be interpreted by the extension itself. The interpretation
is usually done in a :php:class:`phpDocumentor\Guides\Event\PostProjectNodeCreated`
event.

..  _guides-xml-add-config-core:

Add a configuration option (Contributors, directly in the guides)
=================================================================

In order to add a configuration option to the :file:`guides.xml` the option
must be added to the XML schema, :file:`guides.xsd`. You can find
it at :file:`packages/guides-cli/resources/schema/guides.xsd` in the
mono repository.

Register the new configuration option in the :php:`TreeBuilder` at
:php:func:`phpDocumentor\Guides\DependencyInjection\GuidesExtension::getConfigTreeBuilder`.

..  seealso::

    `Defining and Processing Configuration Values (Symfony Documentation)
    <https://symfony.com/doc/current/components/config/definition.html>`__

Using the tree builder is a topic of itself. Refer to the according symfony documentation.

If the configuration is a setting option to controll the applications workflow you can
save it in the :php:class:`phpDocumentor\Guides\Settings\ProjectSettings`. Examples of
settings options would be `logPath`, `showProgressBar` or `theme`.

If the configuration contains data that should be output in the templates, it
is advised to save the value in the
:php:class:`phpDocumentor\Guides\Nodes\ProjectNode`. Examples would be `copyright`,
`release` or `keywords` (for the metadata area).

Save a configuration value into the :php:`ProjectSettings`
----------------------------------------------------------

To add a configuration value to the :php:`ProjectSettings`,
register the setting in
:php:func:`phpDocumentor\Guides\DependencyInjection\GuidesExtension::load`.

..  code-block:: php
    :caption: phpDocumentor\Guides\DependencyInjection\GuidesExtension::load

    if (isset($config['ignored_domain']) && is_array($config['ignored_domain'])) {
        $projectSettings->setIgnoredDomains($config['ignored_domain']);
    }

Save a configuration value to the :php:`ProjectNode`
----------------------------------------------------

Additional settings can be added to the ProjectNode in a
:php:`phpDocumentor\Guides\Event\PostProjectNodeCreated` event.

..  todo: Add example
