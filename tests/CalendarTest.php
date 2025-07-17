<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../calendar.php';

class CalendarTest extends TestCase
{
    public function testGregorianToJd()
    {
        // Test case from a known source
        $jd = Calendar::gregorian_to_jd(2024, 7, 18);
        $this->assertEquals(2460509.5, $jd);
    }

    public function testJdToGregorian()
    {
        $date = Calendar::jd_to_gregorian(2460509.5);
        $this->assertEquals([2024, 7, 18], $date);
    }

    public function testGregorianRoundtrip()
    {
        $year = 2024;
        $month = 7;
        $day = 18;
        $jd = Calendar::gregorian_to_jd($year, $month, $day);
        $date = Calendar::jd_to_gregorian($jd);
        $this->assertEquals([$year, $month, $day], $date);
    }

    public function testLeapGregorian()
    {
        $this->assertTrue(Calendar::leap_gregorian(2024));
        $this->assertFalse(Calendar::leap_gregorian(2023));
        $this->assertFalse(Calendar::leap_gregorian(1900));
        $this->assertTrue(Calendar::leap_gregorian(2000));
    }

    public function testIslamicToJd()
    {
        // Test case for the implemented algorithm
        $jd = Calendar::islamic_to_jd(1445, 12, 12);
        $this->assertEquals(2460481.5, $jd);
    }

    public function testJdToIslamic()
    {
        $date = Calendar::jd_to_islamic(2460481.5);
        $this->assertEquals([1445, 12, 12], $date);
    }

    public function testIslamicRoundtrip()
    {
        $year = 1445;
        $month = 12;
        $day = 12;
        $jd = Calendar::islamic_to_jd($year, $month, $day);
        $date = Calendar::jd_to_islamic($jd);
        $this->assertEquals([$year, $month, $day], $date);
    }

    public function testLeapIslamic()
    {
        $this->assertTrue(Calendar::leap_islamic(1445));
        $this->assertFalse(Calendar::leap_islamic(1444));
    }

    public function testPersianToJd()
    {
        // Test case from a known source
        $jd = Calendar::persian_to_jd(1403, 4, 28);
        $this->assertEquals(2460509.5, $jd);
    }

    public function testJdToPersian()
    {
        $date = Calendar::jd_to_persian(2460509.5);
        $this->assertEquals([1403, 4, 28], $date);
    }

    public function testPersianRoundtrip()
    {
        $year = 1403;
        $month = 4;
        $day = 28;
        $jd = Calendar::persian_to_jd($year, $month, $day);
        $date = Calendar::jd_to_persian($jd);
        $this->assertEquals([$year, $month, $day], $date);
    }

    public function testLeapPersian()
    {
        $this->assertTrue(Calendar::leap_persian(1399)); // 1399 is a leap year in this algorithm
        $this->assertFalse(Calendar::leap_persian(1403)); // 1403 is NOT a leap year in this specific algorithm
        $this->assertFalse(Calendar::leap_persian(1402));
    }
}
