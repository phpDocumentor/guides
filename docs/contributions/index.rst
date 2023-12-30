..  include:: /include.rst.txt

=============
Contributions
=============

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

Submit a Pull Request
=====================
