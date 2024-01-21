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
 * The contact element holds contact information for the author (individual or group) of the document, or a
 * third-party contact. It is typically used for an email or web address.
 */
final class ContactNode extends MetadataNode
{
    public function __construct(private readonly string $email)
    {
        parent::__construct($email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
