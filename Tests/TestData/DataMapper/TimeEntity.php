<?php

namespace Tests\TestData\DataMapper;

use Carbon\Carbon;
use Library\DataMapper\DataMapperPrimaryKey;
use Library\DataMapper\DataMapperTimestamps;

/** @Entity */
class TimeEntity
{
    use DataMapperPrimaryKey, DataMapperTimestamps;

    /** @Column(type="datetime") */
    private $date;

    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    public function date()
    {
        return $this->date;
    }

    public function setDate(Carbon $date)
    {
        $this->date = $date;
    }
}