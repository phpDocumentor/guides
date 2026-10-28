..  _start:

==============
Document Title
==============

See :card:`some-card` and :card:`another-card`.

..  card:: Card Header
    :class: w-50

    Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
    eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
    sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.
    Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
    Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
    nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
    sed diam voluptua. At vero eos et accusam et justo duo dolores et
    ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est
    Lorem ipsum dolor sit amet.

..  card:: :ref:`Linked Card Header <start>`
    :class: w-50 text-center

    **Lorem ipsum dolor sit amet,** consetetur sadipscing elitr, sed diam nonumy
    eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
    sed diam voluptua.

    ..  card-footer::

        *   `12-dev <https://docs.typo3.org/m/typo3/tutorial-editors/main/en-us/Pages/Index.html>`__
        *   `11.5 <https://docs.typo3.org/m/typo3/tutorial-editors/11.5/en-us/Pages/Index.html>`__
        *   `10.4 <https://docs.typo3.org/m/typo3/tutorial-editors/10.4/en-us/Pages/Index.html>`__

..  card::
    :class: w-50

    ..  card-header:: :ref:`Linked Card Header <start>`

    **Lorem ipsum dolor sit amet,** consetetur sadipscing elitr, sed diam nonumy
    eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
    sed diam voluptua.

    ..  card-footer:: :ref:`Read more <start>`

..  card::
    :class: w-50

    ..  card-image:: hero-illustration.svg
        :alt: Hero Illustration

    ..  card-header:: :ref:`Linked Card Header <start>`

    **Lorem ipsum dolor sit amet,** consetetur sadipscing elitr, sed diam nonumy
    eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
    sed diam voluptua.

    ..  card-footer:: :ref:`Read more <start>`


..  card::
    :class: w-50
    :name: some-card

    ..  card-image:: hero-illustration.svg
        :alt: Hero Illustration

        ..  rubric:: Overlay
            :class: h3

        **Lorem ipsum dolor sit amet,** consetetur sadipscing elitr, sed diam nonumy
        eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
        sed diam voluptua.

    ..  card-footer:: :ref:`Read more <start>`

..  card:: :ref:`Linked Card Header <start>`
    :class: w-50
    :name: another-card

    ..  card-image:: hero-illustration.svg
        :alt: Hero Illustration
        :position: bottom

    **Lorem ipsum dolor sit amet,** consetetur sadipscing elitr, sed diam nonumy
    eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
    sed diam voluptua.

    ..  card-footer:: :ref:`Read more <start>`
