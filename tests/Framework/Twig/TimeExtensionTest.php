<?php

namespace Tests\Framework\Twig;

use DateTime;
use Framework\Twig\TimeExtension;
use PHPUnit\Framework\TestCase;

class TimeExtensionTest extends TestCase
{
    private TimeExtension $timeExtension;

    public function setUp(): void
    {
        $this->timeExtension = new TimeExtension();
    }

    public function testDateFormat(): void
    {
        $date = new DateTime();
        $format = 'd/m/Y H:i';
        $result = "<span class='timeago' datetime='{$date->format(DateTime::ISO8601)}'>{$date->format($format)}</span>";
        $this->assertEquals($result, $this->timeExtension->ago($date));
    }
}
