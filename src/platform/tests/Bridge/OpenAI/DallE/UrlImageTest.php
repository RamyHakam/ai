<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Bridge\OpenAI\DallE;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\OpenAI\DallE\UrlImage;

#[CoversClass(UrlImage::class)]
#[Small]
final class UrlImageTest extends TestCase
{
    public function testItCreatesUrlImage(): void
    {
        $urlImage = new UrlImage('https://example.com/image.jpg');

        $this->assertSame('https://example.com/image.jpg', $urlImage->url);
    }

    public function testItThrowsExceptionWhenUrlIsEmpty(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('The image url must be given.');

        new UrlImage('');
    }
}
