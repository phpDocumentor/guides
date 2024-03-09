Title
=====

..  code-block:: python
    :emphasize-lines: 3,5

    def some_function():
        interesting = False
        print('This line is highlighted.')
        print('This one is not...')
        print('...but this one is.')

..  code-block:: yaml
    :linenos:
    :emphasize-lines: 3-

    Email:
        formEditor:
            predefinedDefaults:
                defaultValue: ''
                validators:
                    -
                    identifier: EmailAddress
