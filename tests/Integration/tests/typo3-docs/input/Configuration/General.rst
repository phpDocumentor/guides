..  include:: /Includes.rst.txt
..  index:: Configuration
..  _configuration-general:

=====================
General configuration
=====================

How is the extension configured?
Aim to provide simple instructions detailing how the extension is configured.
Always assume that the user has no prior experience of using the extension.

Try and provide a typical use case for your extension
and detail each of the steps required to get the extension running.


..  index::
    Configuration; Example
    Configuration; Typical
..  _configuration_example:
..  _configuration_typical:

Typical example
===============

*   Does the integrator need to include a static template?
*   For example add a code snippet with comments

Minimal example of TypoScript:

*   Code-blocks have support for syntax highlighting
*   Use any supported language

..  code-block:: typoscript

    plugin.tx_myextension.settings {
       # configure basic email settings
       email {
          subject = Some subject
          from = someemail@example.org
       }
    }
