..  include:: /include.rst.txt

=============
Contributions
=============

.. toctree::
    :glob:
    :hidden:

    *

Thank you for considering contributing to the this project. We welcome contributions from everyone.
Before you start contributing, please read the following guidelines. They will help you to understand
the different ways you can contribute to the project. And this already starts with reporting an issue.

Report an issue
===============

If you find a bug in the code, or have a suggestion for a new feature, please report it in the issue tracker.
This will help us to understand the problem you are facing. This will also make sure that you are not working
on something that might be rejected later. Please use the issue templates to report the issue.

Now that you have reported the issue, you can start working on the issue. For bugs its always good to have a :ref:`failing test <writing-tests>`.
to reproduce the issue.

Clone the mono repository
=========================

This project uses a :doc:`mono repository </contributions/monorepository-layout>`,
meaning a single git repository regroups several different Composer packages,
built from several git repositories split from this repository.
Contributions need to be made against that mono repository.

To clone the repository, run the following command::

    git clone git@github.com:phpDocumentor/guides.git

Run the tests
=============

The project comes with a Makefile that will run the tests and other
checks for you.
It relies on ``docker``, but you can run any make target natively by
using the ``PHP_BIN`` make variable.
For instance, to run the pre-commit checks, you can run::

    make PHP_BIN=php pre-commit-test

Submit changes (Pull Request)
=============================

To submit a change, you need to create a pull request against the mono repository. Pull requests must
always target the ``main`` branch. We will not accept pull requests against other branches unless discussed
with the maintainers. The pull request should pass the tests and checks. you can run the checks locally like described
in :ref:`Run the tests <run-the-tests>`.

If all checks pass, the pull request
will be reviewed, we try to do this as fast as possible, but as this is a community project, it might take
while before we can review your pull request.

Documentation
=============

As this is a documentation project, we also welcome contributions to the documentation. If you find a typo or an
error in the documentation, please report it in the issue tracker. If you want to fix it yourself, you can create a pull
request with the changes. We will review the changes and merge them if they are correct.

Our documentation is written in reStructuredText and built with phpDocumentor. And can be found in the ``docs`` directory.

Need help?
==========

If you need help with contributing, please ask in the issue tracker. We are happy to help you with your contribution.
