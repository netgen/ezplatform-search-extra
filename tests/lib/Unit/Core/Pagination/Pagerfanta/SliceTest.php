<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Tests\Unit\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Netgen\EzPlatformSearchExtra\Core\Pagination\Pagerfanta\Slice;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @group pager
 */
class SliceTest extends TestCase
{
    public function testIteration(): void
    {
        $slice = $this->getSlice();

        self::assertCount(2, $slice);

        foreach ($slice as $searchHit) {
            self::assertIsString($searchHit);
        }
    }

    public function testIteratorArrayAccess(): void
    {
        $slice = $this->getSlice();
        $iterator = $slice->getIterator();

        self::assertEquals('one', $iterator[0]);
        self::assertEquals('two', $iterator[1]);
    }

    public function testArrayAccessGet(): void
    {
        $slice = $this->getSlice();

        self::assertEquals('one', $slice[0]);
        self::assertEquals('two', $slice[1]);
    }

    public function testArrayAccessExists(): void
    {
        $slice = $this->getSlice();

        self::assertTrue(isset($slice[0]));
        self::assertTrue(isset($slice[1]));
        self::assertFalse(isset($slice[2]));
    }

    public function testArrayAccessSet(): void
    {
        $this->expectException(RuntimeException::class);

        $slice = $this->getSlice();

        $slice[0] = 1;
    }

    public function testArrayAccessUnset(): void
    {
        $this->expectException(RuntimeException::class);

        $slice = $this->getSlice();

        unset($slice[0]);
    }

    public function testGetSearchHits(): void
    {
        $slice = $this->getSlice();

        self::assertEquals(
            $this->getSearchHits(),
            $slice->getSearchHits()
        );
    }

    protected function getSearchHits(): array
    {
        return [
            new SearchHit(['valueObject' => 'one']),
            new SearchHit(['valueObject' => 'two']),
        ];
    }

    protected function getSlice(): Slice
    {
        return new Slice($this->getSearchHits());
    }
}
