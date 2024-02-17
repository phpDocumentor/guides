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

namespace phpDocumentor\Guides\Nodes;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

/**
 * The author element holds the name of the author of a document, section or code-block
 *
 * @extends AbstractNode<string>
 */
final class AuthorNode extends AbstractNode
{
    public const CONTEXT_DOCUMENT = 'document';
    public const CONTEXT_SECTION = 'section';
    public const CONTEXT_CODE = 'code';

    /** @param Node[] $children */
    public function __construct(
        string $value,
        private readonly array $children,
        private readonly string $context = self::CONTEXT_DOCUMENT,
        private string|null $email = null,
    ) {
        $this->value = $value;
        if (filter_var($email ?? '', FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $this->email = null;
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getEmail(): string|null
    {
        return $this->email;
    }

    public function getContext(): string
    {
        return $this->context;
    }
}
