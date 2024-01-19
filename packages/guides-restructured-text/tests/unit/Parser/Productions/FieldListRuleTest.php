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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use Monolog\Logger;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FieldListNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Metadata\OrganizationNode;
use phpDocumentor\Guides\Nodes\Metadata\TopicNode;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\AbstractFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\AddressFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\AuthorFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\AuthorsFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\ContactFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\CopyrightFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\DateFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\DedicationFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\NocommentsFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\NosearchFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\OrganizationFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\OrphanFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\ProjectFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\RevisionFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\TocDepthFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\VersionFieldListItemRule;
use PHPUnit\Framework\Attributes\DataProvider;

final class FieldListRuleTest extends RuleTestCase
{
    private FieldListRule $rule;

    protected function setUp(): void
    {
        $fieldListItemRules = [];
        $fieldListItemRules[] = new AbstractFieldListItemRule();
        $fieldListItemRules[] = new AddressFieldListItemRule();
        $fieldListItemRules[] = new AuthorFieldListItemRule();
        $fieldListItemRules[] = new AuthorsFieldListItemRule();
        $fieldListItemRules[] = new ContactFieldListItemRule();
        $fieldListItemRules[] = new CopyrightFieldListItemRule();
        $fieldListItemRules[] = new DateFieldListItemRule();
        $fieldListItemRules[] = new DedicationFieldListItemRule();
        $fieldListItemRules[] = new NocommentsFieldListItemRule();
        $fieldListItemRules[] = new NosearchFieldListItemRule();
        $fieldListItemRules[] = new OrganizationFieldListItemRule();
        $fieldListItemRules[] = new OrphanFieldListItemRule();
        $fieldListItemRules[] = new ProjectFieldListItemRule(new Logger('test'));
        $fieldListItemRules[] = new RevisionFieldListItemRule();
        $fieldListItemRules[] = new TocDepthFieldListItemRule();
        $fieldListItemRules[] = new VersionFieldListItemRule(new Logger('test'));
        $this->rule = new FieldListRule($this->givenCollectAllRuleContainer(), $fieldListItemRules);
    }

    #[DataProvider('definitionListProvider')]
    public function testAppliesReturnsTrueOnValidInput(string $input): void
    {
        $context = $this->createContext($input);
        self::assertTrue($this->rule->applies($context));
    }

    #[DataProvider('isDefinitionListFalseProvider')]
    public function testAppliesReturnsFalseOnInvalidInput(string $input): void
    {
        $context = $this->createContext($input);
        self::assertFalse($this->rule->applies($context));
    }

    #[DataProvider('fieldListApplicationProvider')]
    public function testApply(string $input, FieldListNode|null $expected, string|null $nextLine): void
    {
        $context = $this->createContext($input);


        $result = $this->rule->apply($context);

        self::assertEquals($expected, $result);
        self::assertRemainingEquals($nextLine ?? '', $context->getDocumentIterator());
    }

    /** @param MetadataNode[] $expectedNodesArray */
    #[DataProvider('projectProvider')]
    public function testProjectTitle(string $input, string $expectedTitle, array $expectedNodesArray, string|null $nextLine): void
    {
        $context = $this->createContext($input);

        $documentNode = new DocumentNode('', '');
        $documentNode->setTitleFound(false);

        $result = $this->rule->apply($context, $documentNode);

        self::assertNull($result);
        self::assertEquals($expectedTitle, $context->getDocumentParserContext()->getProjectNode()->getTitle());
        self::assertEquals($expectedNodesArray, $documentNode->getHeaderNodes());
        self::assertRemainingEquals($nextLine ?? '', $context->getDocumentIterator());
    }

    /** @param MetadataNode[] $expectedNodesArray */
    #[DataProvider('versionProvider')]
    public function testProjectVersion(string $input, string $expectedVersion, array $expectedNodesArray, string|null $nextLine): void
    {
        $context = $this->createContext($input);

        $documentNode = new DocumentNode('', '');
        $documentNode->setTitleFound(false);

        $result = $this->rule->apply($context, $documentNode);

        self::assertNull($result);
        self::assertEquals($expectedVersion, $context->getDocumentParserContext()->getProjectNode()->getVersion());
        self::assertEquals($expectedNodesArray, $documentNode->getHeaderNodes());
        self::assertRemainingEquals($nextLine ?? '', $context->getDocumentIterator());
    }

    /** @param MetadataNode[] $expectedArray */
    #[DataProvider('metadataProvider')]
    public function testApplyAsMetadata(string $input, array $expectedArray, string|null $nextLine): void
    {
        $context = $this->createContext($input);

        $documentNode = new DocumentNode('', '');
        $documentNode->setTitleFound(false);

        $result = $this->rule->apply($context, $documentNode);

        self::assertNull($result);
        self::assertEquals($expectedArray, $documentNode->getHeaderNodes());
        self::assertRemainingEquals($nextLine ?? '', $context->getDocumentIterator());
    }

    /** @return array<string, mixed[]> */
    public static function projectProvider(): array
    {
        return [
            'testProjectOneLine' => [

                <<<'RST'
:project: The project

This is normal text again.

RST,
                'The project',
                [],
                'This is normal text again.' . "\n",
            ],

            'testProjectNextLine' => [

                <<<'RST'
:project: 
    The project

This is normal text again.

RST,
                'The project',
                [],
                'This is normal text again.' . "\n",
            ],
        ];
    }

    /** @return array<string, mixed[]> */
    public static function versionProvider(): array
    {
        return [
            'testVersionAlone' => [

                <<<'RST'
:version: 
    3.1.4

This is normal text again.

RST,
                '3.1.4',
                [],
                'This is normal text again.' . "\n",
            ],
            'testProjectAndVersion' => [

                <<<'RST'
:project: 
    The project
:version:
    3.1.4

This is normal text again.

RST,
                '3.1.4',
                [],
                'This is normal text again.' . "\n",
            ],
            'testProjectAndVersionSameLine' => [

                <<<'RST'
:project: The project
:version: 3.1.4

This is normal text again.

RST,
                '3.1.4',
                [],
                'This is normal text again.' . "\n",
            ],

        ];
    }

    /** @return array<string, mixed[]> */
    public static function metadataProvider(): array
    {
        return [
            'testAbstract' => [

                <<<'RST'
:abstract: This is the Abstract!

This is normal text again.

RST,
                [new TopicNode('abstract', 'This is the Abstract!')],
                'This is normal text again.' . "\n",
            ],
            'testOrganization' => [

                <<<'RST'
:organization: phpDocumentor

This is normal text again.

RST,
                [
                    new OrganizationNode(
                        'phpDocumentor',
                        [new RawNode('phpDocumentor')],
                    ),
                ],
                'This is normal text again.' . "\n",
            ],
        ];
    }

    /** @return array<string, mixed[]> */
    public static function fieldListApplicationProvider(): array
    {
        return [
            'testEmptyFieldList' => [
                <<<'RST'
:term 1:

This is normal text again.

RST,
                new FieldListNode(
                    [
                        new FieldListItemNode(
                            'term 1',
                            '',
                            [],
                        ),
                    ],
                ),
                'This is normal text again.' . "\n",
            ],
            'test3EmptyFieldList' => [
                <<<'RST'
:term 1:
:term 2:
:term 3:

This is normal text again.

RST,
                new FieldListNode(
                    [
                        new FieldListItemNode(
                            'term 1',
                            '',
                            [],
                        ),
                        new FieldListItemNode(
                            'term 2',
                            '',
                            [],
                        ),
                        new FieldListItemNode(
                            'term 3',
                            '',
                            [],
                        ),
                    ],
                ),
                'This is normal text again.' . "\n",
            ],
            'testFieldWithDirectContent' => [
                <<<'RST'
:term 1: content 1

This is normal text again.

RST,
                new FieldListNode(
                    [
                        new FieldListItemNode(
                            'term 1',
                            'content 1',
                            [
                                new RawNode('content 1'),
                            ],
                        ),
                    ],
                ),
                'This is normal text again.' . "\n",
            ],
        ];
    }

    /** @return array<string, string[]> */
    public static function definitionListProvider(): array
    {
        return [
            'Empty field' => [':empty:'],
            'Field with value' => [':field: Value'],
            'Whitespace in Fieldname' => [':field with whitespace: Some values'],
        ];
    }

    /** @return array<string, string[]> */
    public static function isDefinitionListFalseProvider(): array
    {
        return [
            'empty lines' => [''],
            'directive' => ['.. directive:: something'],
            'escaped colons' => ['\:escaped\:'],
        ];
    }
}
