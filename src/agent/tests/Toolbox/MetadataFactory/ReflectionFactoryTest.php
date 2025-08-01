<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Agent\Tests\Toolbox\MetadataFactory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Agent\Toolbox\Exception\ToolConfigurationException;
use Symfony\AI\Agent\Toolbox\Exception\ToolException;
use Symfony\AI\Agent\Toolbox\ToolFactory\ReflectionToolFactory;
use Symfony\AI\Fixtures\Tool\ToolMultiple;
use Symfony\AI\Fixtures\Tool\ToolRequiredParams;
use Symfony\AI\Fixtures\Tool\ToolWrong;
use Symfony\AI\Platform\Contract\JsonSchema\DescriptionParser;
use Symfony\AI\Platform\Contract\JsonSchema\Factory;
use Symfony\AI\Platform\Tool\ExecutionReference;
use Symfony\AI\Platform\Tool\Tool;

#[CoversClass(ReflectionToolFactory::class)]
#[UsesClass(AsTool::class)]
#[UsesClass(Tool::class)]
#[UsesClass(ExecutionReference::class)]
#[UsesClass(Factory::class)]
#[UsesClass(DescriptionParser::class)]
#[UsesClass(ToolConfigurationException::class)]
#[UsesClass(ToolException::class)]
final class ReflectionFactoryTest extends TestCase
{
    private ReflectionToolFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ReflectionToolFactory();
    }

    public function testInvalidReferenceNonExistingClass(): void
    {
        self::expectException(ToolException::class);
        self::expectExceptionMessage('The reference "invalid" is not a valid tool.');

        iterator_to_array($this->factory->getTool('invalid')); // @phpstan-ignore-line Yes, this class does not exist
    }

    public function testWithoutAttribute(): void
    {
        self::expectException(ToolException::class);
        self::expectExceptionMessage(\sprintf('The class "%s" is not a tool, please add %s attribute.', ToolWrong::class, AsTool::class));

        iterator_to_array($this->factory->getTool(ToolWrong::class));
    }

    public function testGetDefinition(): void
    {
        /** @var Tool[] $metadatas */
        $metadatas = iterator_to_array($this->factory->getTool(ToolRequiredParams::class));

        self::assertToolConfiguration(
            metadata: $metadatas[0],
            className: ToolRequiredParams::class,
            name: 'tool_required_params',
            description: 'A tool with required parameters',
            method: 'bar',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'text' => [
                        'type' => 'string',
                        'description' => 'The text given to the tool',
                    ],
                    'number' => [
                        'type' => 'integer',
                        'description' => 'A number given to the tool',
                    ],
                ],
                'required' => ['text', 'number'],
                'additionalProperties' => false,
            ],
        );
    }

    public function testGetDefinitionWithMultiple(): void
    {
        $metadatas = iterator_to_array($this->factory->getTool(ToolMultiple::class));

        $this->assertCount(2, $metadatas);

        [$first, $second] = $metadatas;

        self::assertToolConfiguration(
            metadata: $first,
            className: ToolMultiple::class,
            name: 'tool_hello_world',
            description: 'Function to say hello',
            method: 'hello',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'world' => [
                        'type' => 'string',
                        'description' => 'The world to say hello to',
                    ],
                ],
                'required' => ['world'],
                'additionalProperties' => false,
            ],
        );

        self::assertToolConfiguration(
            metadata: $second,
            className: ToolMultiple::class,
            name: 'tool_required_params',
            description: 'Function to say a number',
            method: 'bar',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'text' => [
                        'type' => 'string',
                        'description' => 'The text given to the tool',
                    ],
                    'number' => [
                        'type' => 'integer',
                        'description' => 'A number given to the tool',
                    ],
                ],
                'required' => ['text', 'number'],
                'additionalProperties' => false,
            ],
        );
    }

    private function assertToolConfiguration(Tool $metadata, string $className, string $name, string $description, string $method, array $parameters): void
    {
        $this->assertSame($className, $metadata->reference->class);
        $this->assertSame($method, $metadata->reference->method);
        $this->assertSame($name, $metadata->name);
        $this->assertSame($description, $metadata->description);
        $this->assertSame($parameters, $metadata->parameters);
    }
}
