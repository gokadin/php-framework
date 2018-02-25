<?php

namespace Library\Scheduler;

use Cron\CronExpression;
use Closure;
use Exception;

class Event
{
    protected $name;
    protected $expression;
    protected $closure;

    public function __construct($name, Closure $closure)
    {
        $this->name = $name;
        $this->closure = $closure;
    }

    public function name()
    {
        return $this->name;
    }

    public function isDue()
    {
        return CronExpression::factory($this->expression)->isDue();
    }

    public function run()
    {
        try
        {
            $this->closure();

            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    public function cron($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    public function hourly()
    {
        return $this->cron('0 * * * *');
    }

    public function daily()
    {
        return $this->cron('0 0 * * *');
    }

    public function at($time)
    {
        return $this->dailyAt($time);
    }

    public function dailyAt($time)
    {
        $segments = explode(':', $time);

        return $this->insertAtPosition(1, sizeof($segments) == 2 ? $segments[1] : '0')
            ->insertAtPosition(2, $segments[0]);
    }

    public function twiceDaily($first, $second, $minutes = 0)
    {
        return $this->insertAtPosition(1, $minutes)
            ->insertAtPosition(2, $first.','.$second);
    }

    public function weekdays()
    {
        return $this->insertAtPosition(5, '1-5');
    }

    public function mondays()
    {
        return $this->insertAtPosition(5, '1');
    }

    public function tuesdays()
    {
        return $this->insertAtPosition(5, '2');
    }

    public function wednesdays()
    {
        return $this->insertAtPosition(5, '3');
    }

    public function thursdays()
    {
        return $this->insertAtPosition(5, '4');
    }

    public function fridays()
    {
        return $this->insertAtPosition(5, '5');
    }

    public function saturdays()
    {
        return $this->insertAtPosition(5, '6');
    }

    public function sundays()
    {
        return $this->insertAtPosition(5, '0');
    }

    protected function insertAtPosition($pos, $str)
    {
        $this->expression = substr_replace($this->expression, $str, ($pos - 1) * 2, 1);

        return $this;
    }

    public function weekly()
    {
        return $this->cron('0 0 * * 0 *');
    }

    public function monthly()
    {
        return $this->cron('0 0 1 * * *');
    }

    public function yearly()
    {
        return $this->cron('0 0 1 1 * *');
    }

    public function everyMinute()
    {
        return $this->cron('* * * * * *');
    }

    public function everyFiveMinutes()
    {
        return $this->cron('*/5 * * * * *');
    }

    public function everyTenMinutes()
    {
        return $this->cron('*/10 * * * * *');
    }

    public function everyThirtyMinutes()
    {
        return $this->cron('0,30 * * * * *');
    }
}