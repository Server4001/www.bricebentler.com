<?php declare(strict_types=1);
/**
 * @category     Helpers
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace BentlerDesign\Helpers;

use DateTime;
use DateTimeZone;

class Dates
{
    public static function currentTime(string $timezone): string
    {
        $d = new DateTime('now');
        $d->setTimezone(new DateTimeZone($timezone));

        return $d->format('Y-m-d H:i:s e');
    }
}
