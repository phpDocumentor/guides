======================
Extending the compiler
======================

The compiler of this library is one of the extension points we provide. When the parsing is finished you might
want to do some processing to manipulate the rendered AST. The compiler allows you to add, modify or remove nodes
from the AST, but also to collect information that is consumed later by any service during the rendering.

Node transformer
================

The most basic way of doing transformations is to implement a :php:class:`\phpDocumentor\Guides\Compiler\NodeTransformer`.
Node transformers allow you to do simple transformations on specific nodes. For example when you want to add a class
to all titles. Node transformers are executed per document, so they are not ideal for more complex transformations
that need you to do actions over multiple documents.

First we have to define the method that tells the node traverser what node to enter. This way we can leave out the
complexity of filtering from our transformer and just focus on the logic we need.

.. code:: php
  public class TitleClassTransformer implements phpDocumentor\Guides\Compiler\NodeTransformer
  {
      public function supports(Node $node): bool
      {
        return $node instanceof TitleNode;
      }

      public function enterNode(Node $node): Node
      {
         return $node;
      }

      public function leaveNode(Node $node): Node
      {
         return $node;
      }
  }

Now we have the basic setup we can focus on the implementation of the actual transformation. The ``enterNode`` method
is called when the node traverser enters the node. This method can be used to set the state of your transformer. Like
when you enter a new document, so you can refer to it when entering other nodes or reset a service when you enter a
new section.

.. hint::

   Be aware that the AST is a tree structure. Depending on the type of node you are supporting your internal state might
   be more complex. If you need a real complex state, a node traverser is not the correct approach for you.


.. code:: php
  public class TitleClassTransformer implements phpDocumentor\Guides\Compiler\NodeTransformer
  {
      public function supports(Node $node): bool
      {
        return $node instanceof TitleNode;
      }

      public function enterNode(Node $node): Node
      {
         return $node;
      }

      public function leaveNode(Node $node): Node|null
      {
         $node->setClasses(['my-class']);

         return ;
      }
  }

The leave node method is the best place to do transformations on the current node, the children of the node have been
processed already. So any other transformers have been executed on the nested nodes.

When you want to remove a node from the AST you can simply return ``null`` from the ``leaveNode`` method. The
DocumentNodeTraverser will do the rest. If we want to replace the current node the ``leaveNode`` method should return
the new node. This will remove the current node from the AST and replace it with the new returned node.
