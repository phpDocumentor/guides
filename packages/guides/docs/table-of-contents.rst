TocTree directive

- contains a list of files, or globs that should result into list of files
- depending on the level it may contain other headings of the documents in the list

A full table of contents will contain all documents and there titles and sections.
If a section has a toctree that is visible, the documents and their titles and sections will be rendered as tree nodes
of the toctree at that level.

- document 1
  - section 1
     - section 2
       Toctree
         - document 2

- document 2
  - section 1

will result in:

- document 1
  - section 1
    - section 2
       - document 2#section 1

This means that we will have to collect all toctrees based on titles and their references to other documents.
During a second iteration we can compile the references into their final state.

Tree
  document 1
  - title 1
    - title 1.1
  document 2
  - title 2

