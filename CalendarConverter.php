<?php

/**
 * A professional PHP class for converting dates between Gregorian, Islamic, and Persian calendars.
 *
 * This is a backend-focused conversion of the core logic from the original calendar.js
 * script by John Walker, structured for modern PHP applications.
 *
 * @see http://www.fourmilab.ch/documents/calendar/
 */
class CalendarConverter
{
    // --- Julian Day Epoch Constants ---
    private const GREGORIAN_EPOCH = 1721425.5;
    private const ISLAMIC_EPOCH = 1948439.5;
    private const PERSIAN_EPOCH = 1948320.5;

    /**
     * A mathematical modulo function.
     *
     * @param float $a The dividend.
     * @param float $b The divisor.
     * @return float The remainder.
     */
    private function _mod(float $a, float $b): float
    {
        return $a - ($b * floor($a / $b));
    }

    // --- CORE JULIAN DAY CONVERTERS ---

    // --- GREGORIAN CALENDAR ---

    /**
     * Checks if a year is a leap year in the Gregorian calendar.
     *
     * @param int $year The year to check.
     * @return bool True if the year is a leap year, false otherwise.
     */
    public function isLeapGregorian(int $year): bool
    {
        return (($year % 4) == 0) && (!((($year % 100) == 0) && (($year % 400) != 0)));
    }

    /**
     * Converts a Gregorian date to a Julian Day number.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return float The Julian Day number.
     */
    public function gregorianToJd(int $year, int $month, int $day): float
    {
        return (self::GREGORIAN_EPOCH - 1) +
            (365 * ($year - 1)) +
            floor(($year - 1) / 4) +
            (-floor(($year - 1) / 100)) +
            floor(($year - 1) / 400) +
            floor(
                (((367 * $month) - 362) / 12) +
                (($month <= 2) ? 0 : ($this->isLeapGregorian($year) ? -1 : -2)) +
                $day
            );
    }

    /**
     * Converts a Julian Day number to a Gregorian date.
     *
     * @param float $jd The Julian Day number.
     * @return array An array containing [year, month, day].
     */
    public function jdToGregorian(float $jd): array
    {
        $wjd = floor($jd - 0.5) + 0.5;
        $depoch = $wjd - self::GREGORIAN_EPOCH;
        $quadricent = floor($depoch / 146097);
        $dqc = $this->_mod($depoch, 146097);
        $cent = floor($dqc / 36524);
        $dcent = $this->_mod($dqc, 36524);
        $quad = floor($dcent / 1461);
        $dquad = $this->_mod($dcent, 1461);
        $yindex = floor($dquad / 365);

        $year = (int) (($quadricent * 400) + ($cent * 100) + ($quad * 4) + $yindex);
        if (!($cent == 4 || $yindex == 4)) {
            $year++;
        }

        $yearday = $wjd - $this->gregorianToJd($year, 1, 1);
        $leapadj = ($wjd < $this->gregorianToJd($year, 3, 1)) ? 0 : ($this->isLeapGregorian($year) ? 1 : 2);
        $month = (int) floor((((($yearday + $leapadj) * 12) + 373) / 367));
        $day = (int) (($wjd - $this->gregorianToJd($year, $month, 1)) + 1);

        return [$year, $month, $day];
    }

    // --- ISLAMIC CALENDAR ---

    /**
     * Checks if a year is a leap year in the Islamic calendar.
     *
     * @param int $year The year to check.
     * @return bool True if the year is a leap year, false otherwise.
     */
    public function isLeapIslamic(int $year): bool
    {
        return ((($year * 11) + 14) % 30) < 11;
    }

    /**
     * Converts an Islamic date to a Julian Day number.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return float The Julian Day number.
     */
    public function islamicToJd(int $year, int $month, int $day): float
    {
        return ($day +
                ceil(29.5 * ($month - 1)) +
                ($year - 1) * 354 +
                floor((3 + (11 * $year)) / 30) +
                self::ISLAMIC_EPOCH) - 1;
    }

    /**
     * Converts a Julian Day number to an Islamic date.
     *
     * @param float $jd The Julian Day number.
     * @return array An array containing [year, month, day].
     */
    public function jdToIslamic(float $jd): array
    {
        $jd = floor($jd) + 0.5;
        $year = (int) floor(((30 * ($jd - self::ISLAMIC_EPOCH)) + 10646) / 10631);
        $month = (int) min(12, ceil(($jd - (29 + $this->islamicToJd($year, 1, 1))) / 29.5) + 1);
        $day = (int) (($jd - $this->islamicToJd($year, $month, 1)) + 1);

        return [$year, $month, $day];
    }

    // --- PERSIAN (ARITHMETIC) CALENDAR ---

    /**
     * Checks if a year is a leap year in the Persian arithmetic calendar.
     *
     * @param int $year The year to check.
     * @return bool True if the year is a leap year, false otherwise.
     */
    public function isLeapPersian(int $year): bool
    {
        return (((((($year - (($year > 0) ? 474 : 473)) % 2820) + 474) + 38) * 682) % 2816) < 682;
    }

    /**
     * Converts a Persian date to a Julian Day number.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return float The Julian Day number.
     */
    public function persianToJd(int $year, int $month, int $day): float
    {
        $epbase = $year - (($year >= 0) ? 474 : 473);
        $epyear = 474 + $this->_mod($epbase, 2820);
        $monthContribution = ($month <= 7) ? (($month - 1) * 31) : ((($month - 1) * 30) + 6);

        return $day +
            $monthContribution +
            floor((($epyear * 682) - 110) / 2816) +
            ($epyear - 1) * 365 +
            floor($epbase / 2820) * 1029983 +
            (self::PERSIAN_EPOCH - 1);
    }

    /**
     * Converts a Julian Day number to a Persian date.
     *
     * @param float $jd The Julian Day number.
     * @return array An array containing [year, month, day].
     */
    public function jdToPersian(float $jd): array
    {
        $jd = floor($jd) + 0.5;

        $depoch = $jd - $this->persianToJd(475, 1, 1);
        $cycle = floor($depoch / 1029983);
        $cyear = $this->_mod($depoch, 1029983);

        if ($cyear == 1029982) {
            $ycycle = 2820;
        } else {
            $aux1 = floor($cyear / 366);
            $aux2 = $this->_mod($cyear, 366);
            $ycycle = floor(((2134 * $aux1) + (2816 * $aux2) + 2815) / 1028522) + $aux1 + 1;
        }

        $year = (int) ($ycycle + (2820 * $cycle) + 474);
        if ($year <= 0) {
            $year--;
        }

        $yday = ($jd - $this->persianToJd($year, 1, 1)) + 1;
        $month = (int) (($yday <= 186) ? ceil($yday / 31) : ceil(($yday - 6) / 30));
        $day = (int) (($jd - $this->persianToJd($year, $month, 1)) + 1);

        return [$year, $month, $day];
    }

    // --- CONVENIENCE CONVERTERS ---

    /**
     * Converts a Persian date to a Gregorian date.
     * @param int $year Persian year.
     * @param int $month Persian month.
     * @param int $day Persian day.
     * @return array A Gregorian date array [year, month, day].
     */
    public function persianToGregorian(int $year, int $month, int $day): array
    {
        $jd = $this->persianToJd($year, $month, $day);
        return $this->jdToGregorian($jd);
    }

    /**
     * Converts a Persian date to an Islamic date.
     * @param int $year Persian year.
     * @param int $month Persian month.
     * @param int $day Persian day.
     * @return array An Islamic date array [year, month, day].
     */
    public function persianToIslamic(int $year, int $month, int $day): array
    {
        $jd = $this->persianToJd($year, $month, $day);
        return $this->jdToIslamic($jd);
    }

    /**
     * Converts an Islamic date to a Persian date.
     * @param int $year Islamic year.
     * @param int $month Islamic month.
     * @param int $day Islamic day.
     * @return array A Persian date array [year, month, day].
     */
    public function islamicToPersian(int $year, int $month, int $day): array
    {
        $jd = $this->islamicToJd($year, $month, $day);
        return $this->jdToPersian($jd);
    }

    /**
     * Converts an Islamic date to a Gregorian date.
     * @param int $year Islamic year.
     * @param int $month Islamic month.
     * @param int $day Islamic day.
     * @return array A Gregorian date array [year, month, day].
     */
    public function islamicToGregorian(int $year, int $month, int $day): array
    {
        $jd = $this->islamicToJd($year, $month, $day);
        return $this->jdToGregorian($jd);
    }

    /**
     * Converts a Gregorian date to a Persian date.
     * @param int $year Gregorian year.
     * @param int $month Gregorian month.
     * @param int $day Gregorian day.
     * @return array A Persian date array [year, month, day].
     */
    public function gregorianToPersian(int $year, int $month, int $day): array
    {
        $jd = $this->gregorianToJd($year, $month, $day);
        return $this->jdToPersian($jd);
    }

    /**
     * Converts a Gregorian date to an Islamic date.
     * @param int $year Gregorian year.
     * @param int $month Gregorian month.
     * @param int $day Gregorian day.
     * @return array An Islamic date array [year, month, day].
     */
    public function gregorianToIslamic(int $year, int $month, int $day): array
    {
        $jd = $this->gregorianToJd($year, $month, $day);
        return $this->jdToIslamic($jd);
    }

    /**
     * Calculates the day of the week for a given Julian Day.
     * Sunday is 0, Monday is 1, etc.
     *
     * @param float $jd The Julian Day number.
     * @param string $locale The locale for the weekday name ('fa' for Persian, 'en' for English).
     * @return string The name of the weekday.
     */
    public function getWeekday(float $jd, string $locale = 'fa'): string
    {
        $weekdays = [
            'fa' => ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه'],
            'en' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
        ];

        // Standard formula to get weekday index (0 for Sunday)
        $dayIndex = floor($jd + 1.5) % 7;

        return $weekdays[$locale][$dayIndex] ?? 'Unknown';
    }
}
