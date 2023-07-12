..  include:: /include.rst.txt

=============
Contributions
=============

Clone the mono repository
=========================

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
