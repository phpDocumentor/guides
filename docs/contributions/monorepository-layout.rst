..  include:: /include.rst.txt

===============
Mono repository
===============

This project uses a mono repository approach. This means that all the
development happens in a single repository, later split into multiple
repositories for distribution.

Consequences:

- The mono repository is not installable as a Composer package, and its
  ``composer.json`` file is not even valid because there is no package
  name. Its purpose is to help installing development dependencies, and
  dependencies of each component.
- The ``require`` section of the root ``composer.json`` file should only
  contain the list of the components.
- The ``autoload-dev`` section of the root ``composer.json`` file should
  make all the components's test support code available.
- The ``require-dev`` section of the root ``composer.json`` file of
  components should only be used to mention optional dependencies, and
  not actual development dependencies.
- Issues and pull requests should be opened in the mono repository, and
  not in the component repositories.

The components are located under the ``packages`` directory.

