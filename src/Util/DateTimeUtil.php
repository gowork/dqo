<?php declare(strict_types=1);

namespace GW\DQO\Util;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use RuntimeException;

final class DateTimeUtil
{
    public const mutable = [self::class, 'mutable'];
    public const immutable = [self::class, 'immutable'];
    private const PRECISE_FORMAT = 'Y-m-d H:i:s.u';

    /**
     * @param DateTimeInterface|string|null $input null means "now"
     */
    public static function mutable($input = null): DateTime
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

        return new DateTime($input ?? 'now', new DateTimeZone('UTC'));
    }

    /**
     * @param DateTimeInterface|string|null $input null means "now"
     */
    public static function immutable($input = null): DateTimeImmutable
    {
        if ($input instanceof DateTimeImmutable) {
            return $input;
        }

        if ($input instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($input);
        }

        return new DateTimeImmutable($input ?? 'now', new DateTimeZone('UTC'));
    }
}
