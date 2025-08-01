<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Agent\Memory;

use Symfony\AI\Agent\Input;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class StaticMemoryProvider implements MemoryProviderInterface
{
    /**
     * @var array<string>
     */
    private array $memory;

    public function __construct(string ...$memory)
    {
        $this->memory = $memory;
    }

    public function loadMemory(Input $input): array
    {
        if (0 === \count($this->memory)) {
            return [];
        }

        $content = '## Static Memory'.\PHP_EOL;

        foreach ($this->memory as $memory) {
            $content .= \PHP_EOL.'- '.$memory;
        }

        return [new Memory($content)];
    }
}
