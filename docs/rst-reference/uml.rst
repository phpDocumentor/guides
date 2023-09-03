..  include:: /include.rst.txt


=================
PlantUML diagrams
=================

In order to render diagrams in the TYPO3 documentation,
`PlantUML <https://plantuml.com/>`_ is integrated into the rendering process.

..  contents:: Types of diagrams:
    :backlinks: top
    :class: compact-list
    :depth: 1
    :local:

..  index:: Diagrams; Activity

Activity diagram
================

..  uml::

    (*) --> "ClickServlet.handleRequest()"
    --> "new Page"

    if "Page.onSecurityCheck" then
        ->[true] "Page.onInit()"

        if "isForward?" then
            ->[no] "Process controls"

            if "continue processing?" then
                -->[yes] ===RENDERING===
            else
                -->[no] ===REDIRECT_CHECK===
            endif
        else
            -->[yes] ===RENDERING===
        endif

        if "is Post?" then
            -->[yes] "Page.onPost()"
            --> "Page.onRender()" as render
            --> ===REDIRECT_CHECK===
        else
            -->[no] "Page.onGet()"
            --> render
        endif
    else
        -->[false] ===REDIRECT_CHECK===
    endif

    if "Do redirect?" then
        ->[yes] "redirect request"
        --> ==BEFORE_DESTROY===
    else
        if "Do Forward?" then
            -left->[yes] "Forward request"
            --> ==BEFORE_DESTROY===
        else
            -right->[no] "Render page template"
            --> ==BEFORE_DESTROY===
        endif
    endif

    --> "Page.onDestroy()"
    -->(*)

..  code-block:: rest

    ..  uml::

        (*) --> "ClickServlet.handleRequest()"
        --> "new Page"

        if "Page.onSecurityCheck" then
            ->[true] "Page.onInit()"

            if "isForward?" then
                ->[no] "Process controls"

                if "continue processing?" then
                    -->[yes] ===RENDERING===
                else
                    -->[no] ===REDIRECT_CHECK===
                endif
            else
                -->[yes] ===RENDERING===
            endif

            if "is Post?" then
                -->[yes] "Page.onPost()"
                --> "Page.onRender()" as render
                --> ===REDIRECT_CHECK===
            else
                -->[no] "Page.onGet()"
                --> render
            endif
        else
            -->[false] ===REDIRECT_CHECK===
        endif

        if "Do redirect?" then
            ->[yes] "redirect request"
            --> ==BEFORE_DESTROY===
        else
            if "Do Forward?" then
                -left->[yes] "Forward request"
                --> ==BEFORE_DESTROY===
            else
                -right->[no] "Render page template"
                --> ==BEFORE_DESTROY===
            endif
        endif

        --> "Page.onDestroy()"
        -->(*)

Docs: https://plantuml.com/activity-diagram-legacy


..  index:: Diagrams; Class

Class diagram
=============

..  uml::

    class Foo1 {
        You can use
        several lines
        ..
        as you want
        and group
        ==
        things together.
        __
        You can have as many groups
        as you want
        --
        End of class
    }

    class User {
        ..  Simple Getter ..
+ getName()
+ getAddress()
..  Some setter ..
+ setName()
__ private data __
int age
-- encrypted --
String password
}

..  code-block:: rest

    ..  uml::

        class Foo1 {
            You can use
            several lines
            ..
            as you want
            and group
            ==
            things together.
            __
            You can have as many groups
            as you want
            --
            End of class
        }

        class User {
            ..  Simple Getter ..
+ getName()
+ getAddress()
..  Some setter ..
+ setName()
__ private data __
int age
-- encrypted --
String password
}

Docs: https://plantuml.com/class-diagram

..  index:: Diagrams; Component

Component diagram
=================

..  uml::

    package "Some Group" {
        HTTP - [First Component]
        [Another Component]
    }

    node "Other Groups" {
        FTP - [Second Component]
        [First Component] --> FTP
    }

    cloud {
        [Example 1]
    }

    database "MySql" {
        folder "This is my folder" {
            [Folder 3]
        }
        frame "Foo" {
            [Frame 4]
        }
    }

    [Another Component] --> [Example 1]
    [Example 1] --> [Folder 3]
    [Folder 3] --> [Frame 4]

..  code-block:: rest

    ..  uml::

        package "Some Group" {
            HTTP - [First Component]
            [Another Component]
        }

        node "Other Groups" {
            FTP - [Second Component]
            [First Component] --> FTP
        }

        cloud {
            [Example 1]
        }

        database "MySql" {
            folder "This is my folder" {
                [Folder 3]
            }
            frame "Foo" {
                [Frame 4]
            }
        }

        [Another Component] --> [Example 1]
        [Example 1] --> [Folder 3]
        [Folder 3] --> [Frame 4]

Docs: https://plantuml.com/component-diagram

..  index:: Diagrams; Deployment

Deployment diagram
==================

..  uml::

    artifact Foo1 {
        folder Foo2
    }

    folder Foo3 {
        artifact Foo4
    }

    frame Foo5 {
        database Foo6
    }

    cloud vpc {
        node ec2 {
            stack stack
        }
    }

..  code-block:: rest

    ..  uml::

        artifact Foo1 {
            folder Foo2
        }

        folder Foo3 {
            artifact Foo4
        }

        frame Foo5 {
            database Foo6
        }

        cloud vpc {
            node ec2 {
                stack stack
            }
        }

Docs: https://plantuml.com/deployment-diagram


..  index:: pair:Diagrams; Icons

Icons
=====

There are two ways to integrate icons into your diagrams: Either by using the
supplied *PlantUML Standard Library*, which comes with a suitable set of symbols,
or by using remote font sets. The standard library can be used for offline
rendering, while the remote font sets always contain the latest symbols.

Standard Library
----------------

The PlantUML Standard Library contains dumps of various well-known third-party
font sets such as Font Awesome (v4 and v5), Devicon, etc. The available icons
can best be searched by checking out the `project repository
<https://github.com/plantuml/plantuml-stdlib/tree/e58cee3f88e462e45d9ef0581a517a9e6dd6d69a>`_
with the release date of PlantUML v2018.13, which is used in the current
rendering process of the TYPO3 documentation.

..  uml::

    !include <tupadr3/common>
    !include <tupadr3/devicons/mysql>
    !include <tupadr3/devicons/nginx>
    !include <tupadr3/devicons/php>
    !include <tupadr3/devicons/redis>
    !include <tupadr3/font-awesome-5/typo3>
    !include <cloudinsight/elasticsearch>
    !include <cloudinsight/haproxy>

    skinparam defaultTextAlignment center

    rectangle "<$elasticsearch>\nElastic\nSearch" as elastic
    rectangle "<$haproxy>\nHAProxy" as haproxy

    DEV_MYSQL(mysql,Mysql,database)
    DEV_NGINX(nginx,Nginx,rectangle)
    DEV_NGINX(nginx2,Nginx,rectangle)
    DEV_PHP(php,PHP + TYPO3,rectangle)
    DEV_PHP(php2,PHP + TYPO3,rectangle)
    DEV_REDIS(redis,Redis,database)

    FA5_TYPO3(typo3,TYPO3\nShared,rectangle,#f49700)

    haproxy <--> nginx
    haproxy <--> nginx2
    nginx <--> php
    nginx2 <--> php2
    php <--> typo3
    php <--> redis
    php <--> mysql
    php <--> elastic
    php2 <--> typo3
    php2 <--> redis
    php2 <--> mysql
    php2 <--> elastic

..  code-block:: rest

    ..  uml::

        !include <tupadr3/common>
        !include <tupadr3/devicons/mysql>
        !include <tupadr3/devicons/nginx>
        !include <tupadr3/devicons/php>
        !include <tupadr3/devicons/redis>
        !include <tupadr3/font-awesome-5/typo3>
        !include <cloudinsight/elasticsearch>
        !include <cloudinsight/haproxy>

        skinparam defaultTextAlignment center

        rectangle "<$elasticsearch>\nElastic\nSearch" as elastic
        rectangle "<$haproxy>\nHAProxy" as haproxy

        DEV_MYSQL(mysql,Mysql,database)
        DEV_NGINX(nginx,Nginx,rectangle)
        DEV_NGINX(nginx2,Nginx,rectangle)
        DEV_PHP(php,PHP + TYPO3,rectangle)
        DEV_PHP(php2,PHP + TYPO3,rectangle)
        DEV_REDIS(redis,Redis,database)

        FA5_TYPO3(typo3,TYPO3\nShared,rectangle,#f49700)

        haproxy <--> nginx
        haproxy <--> nginx2
        nginx <--> php
        nginx2 <--> php2
        php <--> typo3
        php <--> redis
        php <--> mysql
        php <--> elastic
        php2 <--> typo3
        php2 <--> redis
        php2 <--> mysql
        php2 <--> elastic


Remote font sets
----------------

..  note::

    Including icons this way requires an online connection during the rendering
    process.

The latest icons can be integrated directly via remote url.

..  uml::

    !define ICONURL https://raw.githubusercontent.com/tupadr3/plantuml-icon-font-sprites/v2.1.0
    !includeurl ICONURL/common.puml
    !includeurl ICONURL/devicons/typo3.puml

    DEV_TYPO3(typo3,"TYPO3",participant,orange)

..  code-block:: rest

    ..  uml::

        !define ICONURL https://raw.githubusercontent.com/tupadr3/plantuml-icon-font-sprites/v2.1.0
        !includeurl ICONURL/common.puml
        !includeurl ICONURL/devicons/typo3.puml

        DEV_TYPO3(typo3,"TYPO3",participant,orange)

Docs: https://plantuml.com/stdlib


..  index:: pair: Diagrams; Maths

Maths
=====

..  uml::

    :<math>int_0^1f(x)dx</math>;
    :<math>x^2+y_1+z_12^34</math>;
    note right
    Try also
    <math>d/dxf(x)=lim_(h->0)(f(x+h)-f(x))/h</math>
    <latex>P(y|\mathbf{x}) \mbox{ or } f(\mathbf{x})+\epsilon</latex>
    end note

..  code-block:: rest

    ..  uml::

        :<math>int_0^1f(x)dx</math>;
        :<math>x^2+y_1+z_12^34</math>;
        note right
        Try also
        <math>d/dxf(x)=lim_(h->0)(f(x+h)-f(x))/h</math>
        <latex>P(y|\mathbf{x}) \mbox{ or } f(\mathbf{x})+\epsilon</latex>
        end note

Docs: https://plantuml.com/ascii-math


Misc
====

..  uml::

    title My title
    header My header
    footer My footer

    actor Bob [[http://plantuml.com/sequence]]
    actor "This is [[http://plantuml.com/sequence Alice]] actor" as Alice
    Bob -> Alice [[http://plantuml.com/start]] : hello
    Alice -> Bob : hello with [[http://plantuml.com/start{Tooltip for message} some link]]
    note left of Bob
    You can use [[http://plantuml.com/start links in notes]] also.
    end note

..  code-block:: rest

    ..  uml::

        title My title
        header My header
        footer My footer

        actor Bob [[http://plantuml.com/sequence]]
        actor "This is [[http://plantuml.com/sequence Alice]] actor" as Alice
        Bob -> Alice [[http://plantuml.com/start]] : hello
        Alice -> Bob : hello with [[http://plantuml.com/start{Tooltip for message} some link]]
        note left of Bob
        You can use [[http://plantuml.com/start links in notes]] also.
        end note

Docs: https://plantuml.com/link | https://plantuml.com/sequence-diagram


..  index:: Diagrams; Object

Object diagram
==============

..  uml::

    object Object01 {
        name = "Dummy"
        id = 123
    }
    object Object02
    object Object03
    object Object04
    object Object05
    object Object06
    object Object07
    object Object08

    Object01 <|-- Object02
    Object03 *-- Object04
    Object05 o-- "4" Object06
    Object07 ..  Object08 : some labels

..  code-block:: rest

    ..  uml::

        object Object01 {
            name = "Dummy"
            id = 123
        }
        object Object02
        object Object03
        object Object04
        object Object05
        object Object06
        object Object07
        object Object08

        Object01 <|-- Object02
        Object03 *-- Object04
        Object05 o-- "4" Object06
        Object07 ..  Object08 : some labels

Docs: https://plantuml.com/object-diagram


..  index:: Diagrams; Sequence

Sequence diagram
================

..  uml::

    == Initialization ==

    Alice -> Bob: Authentication Request
    Bob --> Alice: Authentication Response

    == Repetition ==

    Alice -> Bob: Another authentication Request
    Alice <-- Bob: another authentication Response

..  code-block:: rest

    ..  uml::

        == Initialization ==

        Alice -> Bob: Authentication Request
        Bob --> Alice: Authentication Response

        == Repetition ==

        Alice -> Bob: Another authentication Request
        Alice <-- Bob: another authentication Response

Docs: https://plantuml.com/sequence-diagram


..  index:: Diagrams; State

State diagram
=============

..  uml::

    [*] --> NotShooting

    state NotShooting {
        [*] --> Idle
        Idle --> Configuring : EvConfig
        Configuring --> Idle : EvConfig
    }

    state Configuring {
        [*] --> NewValueSelection
        NewValueSelection --> NewValuePreview : EvNewValue
        NewValuePreview --> NewValueSelection : EvNewValueRejected
        NewValuePreview --> NewValueSelection : EvNewValueSaved

        state NewValuePreview {
            State1 -> State2
        }
    }

..  code-block:: rest

    ..  uml::

        [*] --> NotShooting

        state NotShooting {
            [*] --> Idle
            Idle --> Configuring : EvConfig
            Configuring --> Idle : EvConfig
        }

        state Configuring {
            [*] --> NewValueSelection
            NewValueSelection --> NewValuePreview : EvNewValue
            NewValuePreview --> NewValueSelection : EvNewValueRejected
            NewValuePreview --> NewValueSelection : EvNewValueSaved

            state NewValuePreview {
                State1 -> State2
            }
        }

Docs: https://plantuml.com/state-diagram


..  index:: Diagrams; Timing

Timing diagram
==============

..  uml::

    robust "Web Browser" as WB
    concise "Web User" as WU

    WB is Initializing
    WU is Absent

    @WB
    0 is idle
    +200 is Processing
    +100 is Waiting
    WB@0 <-> @50 : {50 ms lag}

    @WU
    0 is Waiting
    +500 is ok
    @200 <-> @+150 : {150 ms}

..  code-block:: rest

    ..  uml::

        robust "Web Browser" as WB
        concise "Web User" as WU

        WB is Initializing
        WU is Absent

        @WB
        0 is idle
        +200 is Processing
        +100 is Waiting
        WB@0 <-> @50 : {50 ms lag}

        @WU
        0 is Waiting
        +500 is ok
        @200 <-> @+150 : {150 ms}

Docs: https://plantuml.com/timing-diagram


..  index:: Diagrams; Use case

Use Case diagram
================

..  uml::

    left to right direction
    skinparam packageStyle rectangle

    actor customer
    actor clerk
    rectangle checkout {
        customer -- (checkout)
        (checkout) .> (payment) : include
        (help) .> (checkout) : extends
        (checkout) -- clerk
    }

..  code-block:: rest

    ..  uml::

        left to right direction
        skinparam packageStyle rectangle

        actor customer
        actor clerk
        rectangle checkout {
            customer -- (checkout)
            (checkout) .> (payment) : include
            (help) .> (checkout) : extends
            (checkout) -- clerk
        }

Docs: https://plantuml.com/use-case-diagram
