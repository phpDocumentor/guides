Subpage
=======

A `:reversed:` toctree that mixes internal pages and an external link is reversed once, for the page
and for the navigation menu alike. The menu is no longer reversed a second time on top of the order
taken from the toctree.

..  toctree::
    :reversed:

    alpha
    External A <https://example.com/a>
    beta
