..  include:: /include.rst.txt

..  _extension_structure:

=================
General Structure
=================

You can extend the guides with your own Composer-based Symfony extension.

..  _extension_composer:

composer.json
=============

Each Composer package must have a file `composer.json`. See an example here:

..  literalinclude:: _composer.json
    :language: json
    :caption: your-extension/composer.json
    :lineos:

The PHP sources can be found in the directory `src` then as is stated in line 8
in the `composer.json`. 

..  _extension_symfony:

Create an extension
===================

For the PHP package to be an extension you need a class
extending `\Symfony\Component\DependencyInjection\Extension\Extension` by 
implementing the interface 
`Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface` we
can also add our ow configurations to our extension:

..  literalinclude:: _YourExtension.php
    :language: php
    :caption: your-extension/DependencyInjection/YourExtension.php
    :lineos:

Lines 24 to 28 load a :ref:`Dependency Injection configuration <extension_di_configuration>` 
file. Lines 29 to 36 configure the directory overriding the default templates. 
Read chapter :ref:`extending_templates` to learn more about this.

..  note::
    This is a Symfony extension, not a TYPO3 extension. See also the
    `Symfony documentation about Extensions <https://symfony.com/doc/current/bundles/extension.html>`__.

..  _extension_di_configuration:

Dependency Injection configuration
===================================

..  literalinclude:: _your-extension.php
    :language: php
    :caption: your-extension/resources/config/your-extension.php
    :lineos:

This is the place to register custom classes such as directives, text roles,
custom compiler passes etc. You can also read about configuration files here:
https://symfony.com/doc/current/bundles/configuration.html
