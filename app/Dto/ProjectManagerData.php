<?php

namespace App\Dto;

/**
 * Class ProjectManagerData
 * @package App\Dto
 */
class ProjectManagerData
{
    public $employee;    //employee full name
    public $hours;       //project worked time (only hours)
    public $minutes;     //project worked time (only minutes)
    public $timeDec;     //project worked time in hours in decimal format

    /**
     * ProjectManagerData constructor.
     * @param string $employee
     * @param int $hours
     * @param string $minutes
     * @param float $timeDec
     */
    public function __construct(string $employee, int $hours, string $minutes, float $timeDec)
    {
        $this->employee = $employee;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->timeDec = $timeDec;
    }
}