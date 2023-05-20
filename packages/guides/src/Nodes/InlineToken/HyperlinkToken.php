<?php

namespace phpDocumentor\Guides\Nodes\InlineToken;

class HyperlinkToken extends AbstractLinkToken
{
    public const TYPE = 'hyperlink';

    public function __construct(string $id, private readonly string $url, private readonly string|null $text = null)
    {
        parent::__construct(self::TYPE, $id, $text??$url);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getText(): string
    {
        return $this->text??$this->url;
    }
}
