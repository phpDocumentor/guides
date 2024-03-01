=============
Writing tests
=============

This page will help you to understand how to write tests to report a bug or cover a new feature.
In this project we have 3 levels of tests:

Integration tests:
  This level of tests allow you to test the whole process from parsing to rendering. Integration tests are
  not split as the other packages are. They are located in the ``tests/Integration/tests`` directory. and do exist
  of one or more input files and the expected output. They might even have their own configuration file if that's required.

Functional tests:
  Functional tests are located in the ``tests/Functional`` directory. They cover individual elements of the processed
  input. They do exist of one input file and the expected output. As they do not cover the whole process of parsing to
  rendering, they cannot be used to test complex transformations. If you have a more complex use case you should have
  a look at the integration tests.

Unit tests:
  Sometimes it's enough to test a single class in isolation. The unittests are part of the packages the test subject is
  located in. They are located in the ``tests`` directory. The tests are named after the class they are testing.

Integration tests
=================

The integration tests can be seen as the easies way to write tests, as they are just like your normal use case. No internal
knowledge of the parser or renderer is required. You just need to know what you want to test and how the input and output
should look like. To create a new test you need to create a new directory in the ``tests/Integration/tests`` directory.
The name of the directory should be the name of the test. The directory should contain at least one input file and one
output file. Each test should have at least in index file.

To make the output file more stable you can must use the following format::

   <!-- content start -->
       <p>Your output here</p>
    <!-- content end -->

The content start and end tags are used to extract the content from the output file. This will isolate the content from
the rest of the output file. If you need to test the whole output file you should put your test in ``tests/Integration/tests-full``.
