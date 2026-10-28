==============
Document Title
==============

..  accordion::
    :name: accordionExample

    ..  accordion-item:: Accordion Item #1
        :name: headingOne
        :header-level: 2
        :show:

        **This is the first item's accordion body.** It is shown by default, until the collapse plugin adds the
        appropriate classes that we use to style each element. These classes control the overall appearance,
        as well as the showing and hiding via CSS transitions.

        You can modify any of this with custom CSS
        or overriding our default variables. It's also worth noting that just about any HTML can go within
        the `.accordion-body`, though the transition does limit overflow.

    ..  accordion-item:: Accordion Item #2
        :name: headingTwo
        :parent: accordionExample
        :header-level: 2

        Placeholder content for this accordion, which is intended to demonstrate the .accordion-flush class.
        This is the second item's accordion body. Let's imagine this being filled with some actual content.

    ..  accordion-item:: Accordion Item #3
        :name: headingThree
        :parent: accordionExample
        :header-level: 2

        Placeholder content for this accordion, which is intended to demonstrate the .accordion-flush class.
        This is the third item's accordion body. Nothing more exciting happening here in terms of content, but
        just filling up the space to make it look,
        at least at first glance, a bit more representative of how this would look in a real-world application.
