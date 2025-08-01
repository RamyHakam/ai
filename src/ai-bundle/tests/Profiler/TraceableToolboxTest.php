<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\AIBundle\Tests\Profiler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Agent\Toolbox\ToolboxInterface;
use Symfony\AI\AIBundle\Profiler\TraceableToolbox;
use Symfony\AI\Platform\Result\ToolCall;
use Symfony\AI\Platform\Tool\ExecutionReference;
use Symfony\AI\Platform\Tool\Tool;

#[CoversClass(TraceableToolbox::class)]
#[Small]
final class TraceableToolboxTest extends TestCase
{
    public function testGetMap(): void
    {
        $metadata = new Tool(new ExecutionReference('Foo\Bar'), 'bar', 'description', null);
        $toolbox = $this->createToolbox(['tool' => $metadata]);
        $traceableToolbox = new TraceableToolbox($toolbox);

        $map = $traceableToolbox->getTools();

        $this->assertSame(['tool' => $metadata], $map);
    }

    public function testExecute(): void
    {
        $metadata = new Tool(new ExecutionReference('Foo\Bar'), 'bar', 'description', null);
        $toolbox = $this->createToolbox(['tool' => $metadata]);
        $traceableToolbox = new TraceableToolbox($toolbox);
        $toolCall = new ToolCall('foo', '__invoke');

        $result = $traceableToolbox->execute($toolCall);

        $this->assertSame('tool_result', $result);
        $this->assertCount(1, $traceableToolbox->calls);
        $this->assertSame($toolCall, $traceableToolbox->calls[0]['call']);
        $this->assertSame('tool_result', $traceableToolbox->calls[0]['result']);
    }

    /**
     * @param Tool[] $tools
     */
    private function createToolbox(array $tools): ToolboxInterface
    {
        return new class($tools) implements ToolboxInterface {
            public function __construct(
                private readonly array $tools,
            ) {
            }

            public function getTools(): array
            {
                return $this->tools;
            }

            public function execute(ToolCall $toolCall): string
            {
                return 'tool_result';
            }
        };
    }
}
