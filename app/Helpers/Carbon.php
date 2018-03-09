<?php

namespace App\Helpers;

use Carbon\Carbon as CarbonDate;

/**
 * Class Carbon
 * @package App\Helper
 */
class Carbon
{
    /**
     * @param CarbonDate $date
     * @return bool
     */
    public static function isDayOff(CarbonDate $date)
    {
        return $date->isSaturday() || $date->isSunday();
    }
}
