<?php declare(strict_types=1);

namespace GW\DQO\Util;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use RuntimeException;
use Stringable;
use function gettype;
use function is_int;
use function is_string;

final class DateTimeUtil
{
    public const immutable = [self::class, 'immutable'];
    private const PRECISE_FORMAT = 'Y-m-d H:i:s.u';

    public static function mutable(mixed $input = null): DateTime
    {
        if ($input instanceof DateTime) {
            return clone $input;
        }

        if ($input instanceof DateTimeInterface) {
            $date = DateTime::createFromFormat(
                self::PRECISE_FORMAT,
                $input->format(self::PRECISE_FORMAT),
                $input->getTimezone()
            );

            if ($date === false) {
                throw new RuntimeException("Cannot create date from {$input->format(self::PRECISE_FORMAT)}");
            }

            return $date;
        }

        if (is_int($input)) {
            $input = (string)$input;
        }

        if ($input === null) {
            $input = 'now';
        }

        if (!$input instanceof Stringable && !is_string($input)) {
            throw new RuntimeException("Cannot create date from input of type " . gettype($input));
        }

        return new DateTime((string)$input, new DateTimeZone('UTC'));
    }

    public static function immutable(mixed $input = null): DateTimeImmutable
    {
        if ($input instanceof DateTimeImmutable) {
            return $input;
        }

        if ($input instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($input);
        }

        if (is_int($input)) {
            $input = (string)$input;
        }

        if ($input === null) {
            $input = 'now';
        }

        if (!$input instanceof Stringable && !is_string($input)) {
            throw new RuntimeException("Cannot create date from input of type " . gettype($input));
        }

        return new DateTimeImmutable((string)$input, new DateTimeZone('UTC'));
    }
}
