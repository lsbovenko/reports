<?php

namespace App\Dto;

class ReportsStatistics
{
    public $workedMinutes;    //the number of minutes worked per month
    public $plannedMinutes;   //the number of working minutes per month on the production calendar
    public $differenceMinutes;    //left to work minutes per month or overtime minutes per month

    //formatted strings based on the internationalization - Xh:Ym (Xч:Yм), where X - number of hours, Y - number of minutes
    public $formattedWorkedTime;
    public $formattedPlannedTime;
    public $formattedDifferenceTime;

    public $percent;    //the ratio of already worked minutes per month to the number of working minutes per month according to the production calendar as a percentage
                        // or the ratio of working minutes per month according to the production calendar to the number of already worked minutes per month as a percentage

    public $isExistsOvertime;   //is there overtimes in the month
}
