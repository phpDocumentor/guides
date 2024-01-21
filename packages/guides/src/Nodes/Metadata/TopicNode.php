<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Nodes\Metadata;

/**
 * The topic element is a nonrecursive section-like construct which may occur at the top level of a section wherever a
 * body element (list, table, etc.) is allowed. In other words, topic elements cannot nest inside body elements, so
 * you can't have a topic inside a table or a list, or inside another topic.
 *
 * It can be created by the `.. topic::` directive or by a Bibliographic field list containing the keyword `:Abstract:`
 * or `:Dedication:`.
 */
final class TopicNode extends MetadataNode
{
    public function __construct(private readonly string $title, private readonly string $body)
    {
        parent::__construct($body);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
