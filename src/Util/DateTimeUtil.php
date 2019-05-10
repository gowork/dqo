<?php declare(strict_types=1);

namespace GW\DQO\Util;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

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
            return DateTime::createFromFormat(
                self::PRECISE_FORMAT,
                $input->format(self::PRECISE_FORMAT),
                $input->getTimezone()
            );
        }

        return new DateTime($input, new \DateTimeZone('UTC'));
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

        if ($input instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromFormat(
                self::PRECISE_FORMAT,
                $input->format(self::PRECISE_FORMAT),
                $input->getTimezone()
            );
        }

        return new DateTimeImmutable($input, new \DateTimeZone('UTC'));
    }
}
