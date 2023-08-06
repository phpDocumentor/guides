..  include:: /include.rst.txt

==================================
Extending the phpdocumentor/guides
==================================

``phpdocumentor/guides`` relies on `Symfony Dependency Injection
Container
<https://symfony.com/doc/current/components/dependency_injection.html#setting-up-the-container-with-configuration-files>`__
extensions. This means that to extend the guides, you need to define
such an extension, after what it becomes possible to make the guides CLI
aware of it by creating a ``guides.xml`` file in the directory from
which you invoke the CLI.

It should look like this::

..  code-block:: xml
    :caption: your_project/guides.xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <guides>
        <extension class="YourName\YourExtension\DependencyInjection\YourExtension"/>
    </guides>

Internally, the guides CLI defines and uses default extensions.
Once you have that set up, you can create PHP classes, define services
from it, and tag them so that they are recognized and usable by the
guides CLI.

Some ways to extend the guides:

.. toctree::

    structure
    templates
    text-roles
