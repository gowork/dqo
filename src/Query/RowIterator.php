<?php declare(strict_types=1);

namespace GW\DQO\Query;

use GW\DQO\DatabaseSelectBuilder;
use IteratorAggregate;
use Traversable;
use function array_map;
use function count;

/**
 * @template T
 * @implements IteratorAggregate<int, T>
 */
final class RowIterator implements IteratorAggregate
{
    public const DEFAULT_CHUNK_SIZE = 400;
    private DatabaseSelectBuilder $builder;
    /** @var callable(array<string,mixed> $raw):T */
    private $hydrator;
    private int $chunkSize;
    private int $startOffset;

    /** @param callable(array<string,mixed> $raw):T $hydrator */
    public function __construct(
        DatabaseSelectBuilder $builder,
        callable $hydrator,
        int $chunkSize = self::DEFAULT_CHUNK_SIZE,
        int $startOffset = 0
    ) {
        $this->builder = $builder;
        $this->hydrator = $hydrator;
        $this->chunkSize = $chunkSize;
        $this->startOffset = $startOffset;
    }

    /** @return Traversable<int, T> */
    public function getIterator(): Traversable
    {
        if ($this->builder->isSliced()) {
            $records = $this->builder->fetchAll();
            yield from array_map($this->hydrator, $records);
            return;
        }

        $offset = $this->startOffset;

        do {
            $builder = $this->builder->offsetLimit($offset, $this->chunkSize);
            $records = $builder->fetchAll();

            yield from array_map($this->hydrator, $records);

            $offset += $this->chunkSize;
        } while (count($records) > 0);
    }
}
