<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\References;

use phpDocumentor\Guides\References\Resolver\Resolver;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Span\CrossReferenceNode;
use PHPUnit\Framework\TestCase;

final class ReferenceResolverTest extends TestCase
{
    public function testReferenceResolverDoesReturnNullWithoutMatchingRole(): void
    {
        $referenceResolver = new ReferenceResolver([]);

        self::assertNull(
            $referenceResolver->resolve(
                new CrossReferenceNode('id', 'role', 'literal'),
                $this->createMock(RenderContext::class)
            )
        );
    }

    public function testReferenceResolverCallsMatchingResolver(): void
    {
        $crossReference = new CrossReferenceNode('id', 'role', 'literal');
        $expected = new ResolvedReference('file', 'text', 'url');

        $noMatch = $this->createMock(Resolver::class);
        $noMatch->method('supports')->willReturn(false);
        $noMatch->expects(self::never())->method('resolve');

        $matching = $this->createMock(Resolver::class);
        $matching->method('supports')->willReturn(true);
        $matching->expects(self::once())->method('resolve')
            ->with($crossReference, $this->anything())
            ->willReturn($expected);

        $referenceResolver = new ReferenceResolver([$noMatch, $matching]);

        $result = $referenceResolver->resolve($crossReference, $this->createMock(RenderContext::class));

        self::assertSame($expected, $result);
    }
}
