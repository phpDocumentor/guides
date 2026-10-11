..  include:: /include.rst.txt

..  _custom_violation_formats:

=================
Violation formats
=================

Problems found in the documentation source, like an empty code block, are logged. With the ``violation-format``
setting they are reported in another format in addition. Formats for GitHub Actions and GitLab CI are included, see
:ref:`violation_formats_ci`. An extension can add a format, for example for another code review tool.

Implement `\phpDocumentor\Guides\Cli\Validation\ViolationFormat`. During the run, ``report()`` receives each
violation. Once at the end of the run, ``finish()`` writes them wherever the format needs them: the output of the
command, a file, or the API of a code review tool.

..  code-block:: php
    :caption: your-extension/src/Validation/YourViolationFormat.php

    final class YourViolationFormat implements ViolationFormat
    {
        /** @var list<Violation> */
        private array $violations = [];

        public function report(Violation $violation): void
        {
            $this->violations[] = $violation;
        }

        public function finish(OutputInterface $output): void
        {
            foreach ($this->violations as $violation) {
                $output->writeln(sprintf(
                    '%s:%s %s (%s)',
                    $violation->context['rst-file'] ?? '',
                    $violation->context['currentLineNumber'] ?? '',
                    $violation->message,
                    $violation->identifier,
                ));
            }
        }
    }

Register it with the tag ``phpdoc.guides.violation_format`` and the name of the format as ``format`` attribute:

..  code-block:: php
    :caption: your-extension/resources/config/your-extension.php

    $container->services()
        ->defaults()->autowire()
        ->set(YourViolationFormat::class)
        ->tag('phpdoc.guides.violation_format', ['format' => 'your-format']);

The format is then used with ``--violation-format=your-format``, or ``violation-format="your-format"`` in
``guides.xml``. `\phpDocumentor\Guides\Cli\Validation\AnnotationDetails` gives you the path of the source file, the line
and the message of a violation. Have a look at `\phpDocumentor\Guides\Cli\Validation\GithubActionsViolationFormat`,
which prints to the output, and `\phpDocumentor\Guides\Cli\Validation\GitlabViolationFormat`, which writes a file, for
complete examples.
