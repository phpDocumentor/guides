This library uses a simplified compiler design. This basically means that our pipeline contains less steps
than a regular compiler. But its following the same semantics.

Lexing and Parsing

A typical compiler will have separate lexing, syntax analisys. However the parser
was designed to do part of the lexing because of all context dependend logic of most Markup languages.
We call this the parsing phase. This will result into an AST that is mostly close to the original source. It
might contain some optimizations for later use.

Semantic analysis and Intermediate code generation

The semantic analysis phase of this library is performing a number of steps to collect information of the parsed markup
language. An good example is the collection of the table of contents and the metadata of the parsed documents.
This is the moment where document node traversers are executed.

Code optimization

Do some pre-rendering stuff, like buiding the TOC content and other rendering preparations before the real rendering starts.

Code generation

Code generation a.k. rendering. We do deliver a headless
