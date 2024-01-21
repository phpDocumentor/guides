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
 * The address element holds the surface mailing address information for the author (individual or group) of the
 * document, or a third-party contact address. Its structure is identical to that of the literal_block
 * element: whitespace is significant, especially newlines.
 */
final class AddressNode extends MetadataNode
{
    public function __construct(private readonly string $body)
    {
        parent::__construct($body);
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
