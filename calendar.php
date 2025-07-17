<?php

class Calendar {
    // Julian date of Gregorian epoch: 0000-01-01
    const J0000 = 1721424.5;
    // Julian date at Unix epoch: 1970-01-01
    const J1970 = 2440587.5;
    // Epoch of Modified Julian Date system
    const JMJD = 2400000.5;
    // Epoch (day 1) of Excel 1900 date system (PC)
    const J1900 = 2415020.5;
    // Epoch (day 0) of Excel 1904 date system (Mac)
    const J1904 = 2416480.5;

    const GREGORIAN_EPOCH = 1721425.5;
    const JULIAN_EPOCH = 1721423.5;
    const HEBREW_EPOCH = 347995.5;
    const FRENCH_REVOLUTIONARY_EPOCH = 2375839.5;
    const ISLAMIC_EPOCH = 1948439.5;
    const PERSIAN_EPOCH = 1948320.5;
    const MAYAN_COUNT_EPOCH = 584282.5;

    private static function mod($a, $b)
    {
        return $a - ($b * floor($a / $b));
    }

    public static function jwday($j)
    {
        return self::mod(floor($j + 1.5), 7);
    }

    public static function get_persian_weekday($jd)
    {
        $weekdays = [
            'یکشنبه',
            'دوشنبه',
            'سه‌شنبه',
            'چهارشنبه',
            'پنج‌شنبه',
            'جمعه',
            'شنبه'
        ];
        return $weekdays[self::jwday($jd)];
    }

    // LEAP_GREGORIAN  --  Is a given year in the Gregorian calendar a leap year?
    public static function leap_gregorian($year)
    {
        return (($year % 4) == 0) &&
            (!((($year % 100) == 0) && (($year % 400) != 0)));
    }

    // GREGORIAN_TO_JD  --  Determine Julian day number from Gregorian calendar date
    public static function gregorian_to_jd($year, $month, $day)
    {
        return (self::GREGORIAN_EPOCH - 1) +
            (365 * ($year - 1)) +
            floor(($year - 1) / 4) +
            (-floor(($year - 1) / 100)) +
            floor(($year - 1) / 400) +
            floor((((367 * $month) - 362) / 12) +
            (($month <= 2) ? 0 :
                (self::leap_gregorian($year) ? -1 : -2)
            ) +
            $day);
    }

    // JD_TO_GREGORIAN  --  Calculate Gregorian calendar date from Julian day
    public static function jd_to_gregorian($jd) {
        $wjd = floor($jd - 0.5) + 0.5;
        $depoch = $wjd - self::GREGORIAN_EPOCH;
        $quadricent = floor($depoch / 146097);
        $dqc = self::mod($depoch, 146097);
        $cent = floor($dqc / 36524);
        $dcent = self::mod($dqc, 36524);
        $quad = floor($dcent / 1461);
        $dquad = self::mod($dcent, 1461);
        $yindex = floor($dquad / 365);
        $year = ($quadricent * 400) + ($cent * 100) + ($quad * 4) + $yindex;
        if (!(($cent == 4) || ($yindex == 4))) {
            $year++;
        }
        $yearday = $wjd - self::gregorian_to_jd($year, 1, 1);
        $leapadj = (($wjd < self::gregorian_to_jd($year, 3, 1)) ? 0
                                                      :
                      (self::leap_gregorian($year) ? 1 : 2)
                  );
        $month = floor((((($yearday + $leapadj) * 12) + 373) / 367));
        $day = ($wjd - self::gregorian_to_jd($year, $month, 1)) + 1;

        return [(int)$year, (int)$month, (int)$day];
    }

    // LEAP_JULIAN  --  Is a given year in the Julian calendar a leap year?
    public static function leap_julian($year)
    {
        return self::mod($year, 4) == (($year > 0) ? 0 : 3);
    }

    // JULIAN_TO_JD  --  Determine Julian day number from Julian calendar date
    public static function julian_to_jd($year, $month, $day)
    {
        /* Adjust negative common era years to the zero-based notation we use.  */
        if ($year < 1) {
            $year++;
        }

        /* Algorithm as given in Meeus, Astronomical Algorithms, Chapter 7, page 61 */
        if ($month <= 2) {
            $year--;
            $month += 12;
        }

        return ((floor((365.25 * ($year + 4716))) +
                floor((30.6001 * ($month + 1))) +
                $day) - 1524.5);
    }

    // JD_TO_JULIAN  --  Calculate Julian calendar date from Julian day
    public static function jd_to_julian($td) {
        $td += 0.5;
        $z = floor($td);

        $a = $z;
        $b = $a + 1524;
        $c = floor(($b - 122.1) / 365.25);
        $d = floor(365.25 * $c);
        $e = floor(($b - $d) / 30.6001);

        $month = floor(($e < 14) ? ($e - 1) : ($e - 13));
        $year = floor(($month > 2) ? ($c - 4716) : ($c - 4715));
        $day = $b - $d - floor(30.6001 * $e);

        /*  If year is less than 1, subtract one to convert from
            a zero based date system to the common era system in
            which the year -1 (1 B.C.E) is followed by year 1 (1 C.E.).  */
        if ($year < 1) {
            $year--;
        }

        return [$year, $month, $day];
    }

    //  Is a given Hebrew year a leap year?
    public static function hebrew_leap($year)
    {
        return self::mod((($year * 7) + 1), 19) < 7;
    }

    //  How many months are there in a Hebrew year (12 = normal, 13 = leap)
    public static function hebrew_year_months($year)
    {
        return self::hebrew_leap($year) ? 13 : 12;
    }

    //  Test for delay of start of new year and to avoid
    //  Sunday, Wednesday, and Friday as start of the new year.
    private static function hebrew_delay_1($year)
    {
        $months = floor(((235 * $year) - 234) / 19);
        $parts = 12084 + (13753 * $months);
        $day = ($months * 29) + floor($parts / 25920);

        if (self::mod((3 * ($day + 1)), 7) < 3) {
            $day++;
        }
        return $day;
    }

    //  Check for delay in start of new year due to length of adjacent years
    private static function hebrew_delay_2($year)
    {
        $last = self::hebrew_delay_1($year - 1);
        $present = self::hebrew_delay_1($year);
        $next = self::hebrew_delay_1($year + 1);

        return (($next - $present) == 356) ? 2 :
                                         ((($present - $last) == 382) ? 1 : 0);
    }

    //  How many days are in a Hebrew year?
    public static function hebrew_year_days($year)
    {
        return self::hebrew_to_jd($year + 1, 7, 1) - self::hebrew_to_jd($year, 7, 1);
    }

    //  How many days are in a given month of a given year
    public static function hebrew_month_days($year, $month)
    {
        //  First of all, dispose of fixed-length 29 day months
        if ($month == 2 || $month == 4 || $month == 6 ||
            $month == 10 || $month == 13) {
            return 29;
        }

        //  If it's not a leap year, Adar has 29 days
        if ($month == 12 && !self::hebrew_leap($year)) {
            return 29;
        }

        //  If it's Heshvan, days depend on length of year
        if ($month == 8 && !(self::mod(self::hebrew_year_days($year), 10) == 5)) {
            return 29;
        }

        //  Similarly, Kislev varies with the length of year
        if ($month == 9 && (self::mod(self::hebrew_year_days($year), 10) == 3)) {
            return 29;
        }

        //  Nope, it's a 30 day month
        return 30;
    }

    // HEBREW_TO_JD  --  Determine Julian day from Hebrew date
    public static function hebrew_to_jd($year, $month, $day)
    {
        $months = self::hebrew_year_months($year);
        $jd = self::HEBREW_EPOCH + self::hebrew_delay_1($year) +
             self::hebrew_delay_2($year) + $day + 1;

        if ($month < 7) {
            for ($mon = 7; $mon <= $months; $mon++) {
                $jd += self::hebrew_month_days($year, $mon);
            }
            for ($mon = 1; $mon < $month; $mon++) {
                $jd += self::hebrew_month_days($year, $mon);
            }
        } else {
            for ($mon = 7; $mon < $month; $mon++) {
                $jd += self::hebrew_month_days($year, $mon);
            }
        }

        return $jd;
    }

    /*  JD_TO_HEBREW  --  Convert Julian date to Hebrew date
                          This works by making multiple calls to
                          the inverse function, and is this very
                          slow.  */
    public static function jd_to_hebrew($jd)
    {
        $jd = floor($jd) + 0.5;
        $count = floor((($jd - self::HEBREW_EPOCH) * 98496.0) / 35975351.0);
        $year = $count - 1;
        for ($i = $count; $jd >= self::hebrew_to_jd($i, 7, 1); $i++) {
            $year++;
        }
        $first = ($jd < self::hebrew_to_jd($year, 1, 1)) ? 7 : 1;
        $month = $first;
        for ($i = $first; $jd > self::hebrew_to_jd($year, $i, self::hebrew_month_days($year, $i)); $i++) {
            $month++;
        }
        $day = ($jd - self::hebrew_to_jd($year, $month, 1)) + 1;
        return [$year, $month, $day];
    }

    //  LEAP_ISLAMIC  --  Is a given year a leap year in the Islamic calendar?
    public static function leap_islamic($year)
    {
        return ((($year * 11) + 14) % 30) < 11;
    }

    //  ISLAMIC_TO_JD  --  Determine Julian day from Islamic date
    public static function islamic_to_jd($year, $month, $day)
    {
        return ($day +
                ceil(29.5 * ($month - 1)) +
                ($year - 1) * 354 +
                floor((3 + (11 * $year)) / 30) +
                self::ISLAMIC_EPOCH);
    }

    //  JD_TO_ISLAMIC  --  Calculate Islamic date from Julian day
    public static function jd_to_islamic($jd)
    {
        $jd = floor($jd) + 0.5;
        $year = floor(((30 * ($jd - self::ISLAMIC_EPOCH)) + 10646) / 10631);
        $month = min(12, ceil(($jd - (29 + self::islamic_to_jd($year, 1, 1))) / 29.5) + 1);
        $day = ($jd - self::islamic_to_jd($year, $month, 1)) + 1;
        return [(int)$year, (int)$month, (int)$day];
    }

    //  LEAP_PERSIAN  --  Is a given year a leap year in the Persian calendar?
    public static function leap_persian($year)
    {
        return (((((($year - (($year > 0) ? 474 : 473)) % 2820) + 474) + 38) * 682) % 2816) < 682;
    }

    //  PERSIAN_TO_JD  --  Determine Julian day from Persian date
    public static function persian_to_jd($year, $month, $day)
    {
        $epbase = $year - (($year > 0) ? 474 : 473);
        $epyear = 474 + self::mod($epbase, 2820);

        return $day +
                (($month <= 7) ?
                    (($month - 1) * 31) :
                    ((($month - 1) * 30) + 6)
                ) +
                floor((($epyear * 682) - 110) / 2816) +
                ($epyear - 1) * 365 +
                floor($epbase / 2820) * 1029983 +
                (self::PERSIAN_EPOCH - 1);
    }

    //  JD_TO_PERSIAN  --  Calculate Persian date from Julian day
    public static function jd_to_persian($jd)
    {
        $jd = floor($jd) + 0.5;

        $depoch = $jd - self::persian_to_jd(475, 1, 1);
        $cycle = floor($depoch / 1029983);
        $cyear = self::mod($depoch, 1029983);
        if ($cyear == 1029982) {
            $ycycle = 2820;
        } else {
            $aux1 = floor($cyear / 366);
            $aux2 = self::mod($cyear, 366);
            $ycycle = floor(((2134 * $aux1) + (2816 * $aux2) + 2815) / 1028522) +
                        $aux1 + 1;
        }
        $year = $ycycle + (2820 * $cycle) + 474;
        if ($year <= 0) {
            $year--;
        }
        $yday = ($jd - self::persian_to_jd($year, 1, 1)) + 1;
        $month = ($yday <= 186) ? ceil($yday / 31) : ceil(($yday - 6) / 30);
        $day = ($jd - self::persian_to_jd($year, $month, 1)) + 1;
        return [(int)$year, (int)$month, (int)$day];
    }

    // MAYAN_COUNT_TO_JD  --  Determine Julian day from Mayan long count
    public static function mayan_count_to_jd($baktun, $katun, $tun, $uinal, $kin)
    {
        return self::MAYAN_COUNT_EPOCH +
               ($baktun * 144000) +
               ($katun  *   7200) +
               ($tun    *    360) +
               ($uinal  *     20) +
               $kin;
    }

    // JD_TO_MAYAN_COUNT  --  Calculate Mayan long count from Julian day
    public static function jd_to_mayan_count($jd)
    {
        $jd = floor($jd) + 0.5;
        $d = $jd - self::MAYAN_COUNT_EPOCH;
        $baktun = floor($d / 144000);
        $d = self::mod($d, 144000);
        $katun = floor($d / 7200);
        $d = self::mod($d, 7200);
        $tun = floor($d / 360);
        $d = self::mod($d, 360);
        $uinal = floor($d / 20);
        $kin = self::mod($d, 20);

        return [$baktun, $katun, $tun, $uinal, $kin];
    }

    // INDIAN_CIVIL_TO_JD  --  Obtain Julian day for Indian Civil date
    public static function indian_civil_to_jd($year, $month, $day)
    {
        $gyear = $year + 78;
        $leap = self::leap_gregorian($gyear);     // Is this a leap year?
        $start = self::gregorian_to_jd($gyear, 3, $leap ? 21 : 22);
        $Caitra = $leap ? 31 : 30;

        if ($month == 1) {
            $jd = $start + ($day - 1);
        } else {
            $jd = $start + $Caitra;
            $m = $month - 2;
            $m = min($m, 5);
            $jd += $m * 31;
            if ($month >= 8) {
                $m = $month - 7;
                $jd += $m * 30;
            }
            $jd += $day - 1;
        }

        return $jd;
    }

    // JD_TO_INDIAN_CIVIL  --  Calculate Indian Civil date from Julian day
    public static function jd_to_indian_civil($jd)
    {
        $Saka = 79 - 1;                    // Offset in years from Saka era to Gregorian epoch
        $start = 80;                       // Day offset between Saka and Gregorian

        $jd = floor($jd) + 0.5;
        $greg = self::jd_to_gregorian($jd);       // Gregorian date for Julian day
        $leap = self::leap_gregorian($greg[0]);   // Is this a leap year?
        $year = $greg[0] - $Saka;            // Tentative year in Saka era
        $greg0 = self::gregorian_to_jd($greg[0], 1, 1); // JD at start of Gregorian year
        $yday = $jd - $greg0;                // Day number (0 based) in Gregorian year
        $Caitra = $leap ? 31 : 30;          // Days in Caitra this year

        if ($yday < $start) {
            //  Day is at the end of the preceding Saka year
            $year--;
            $yday += $Caitra + (31 * 5) + (30 * 3) + 10 + $start;
        }

        $yday -= $start;
        if ($yday < $Caitra) {
            $month = 1;
            $day = $yday + 1;
        } else {
            $mday = $yday - $Caitra;
            if ($mday < (31 * 5)) {
                $month = floor($mday / 31) + 2;
                $day = ($mday % 31) + 1;
            } else {
                $mday -= 31 * 5;
                $month = floor($mday / 30) + 7;
                $day = ($mday % 30) + 1;
            }
        }

        return [$year, $month, $day];
    }
}