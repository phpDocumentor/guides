=========================
Directives in table cells
=========================

..  list-table::
    :header-rows: 1

    *   -   Content
        -   Description
    *   -   ..  image:: /images/icon.svg
                :alt: An image with options
        -   An image with options
    *   -   ..  image:: /images/icon.svg
        -   An image without options
    *   -   Text before

            ..  image:: /images/icon.svg
                :alt: An image after a paragraph
        -   An image after a paragraph
    *   -   ..  note::

                A note
        -   A note

+-------------------+---------------+
| Content           | Description   |
+===================+===============+
| ..  note::        | A note        |
|                   |               |
|     A note        |               |
+-------------------+---------------+
