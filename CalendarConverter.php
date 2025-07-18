<?php

class CalendarConverter
{
    // --- ثابت‌های مبدأ روز ژولینی برای هر تقویم ---
    private const GREGORIAN_EPOCH = 1721425.5; // مبدأ میلادی
    private const ISLAMIC_EPOCH = 1948439.5;   // مبدأ قمری

    /**
     * محاسبه باقیمانده ریاضی (Modulo).
     * @param float $a عدد اول
     * @param float $b عدد دوم
     * @return float باقیمانده
     */
    private function _mod(float $a, float $b): float
    {
        return $a - ($b * floor($a / $b));
    }

    // --- تقویم میلادی (GREGORIAN) ---

    /**
     * بررسی می‌کند که آیا یک سال میلادی کبیسه است یا خیر.
     * @param int $year سال میلادی
     * @return bool
     */
    public function isLeapGregorian(int $year): bool
    {
        return (($year % 4) == 0) && (!((($year % 100) == 0) && (($year % 400) != 0)));
    }

    /**
     * یک تاریخ میلادی را به عدد روز ژولینی تبدیل می‌کند.
     * @param int $year سال
     * @param int $month ماه
     * @param int $day روز
     * @return float عدد روز ژولینی
     */
    public function gregorianToJd(int $year, int $month, int $day): float
    {
        return (self::GREGORIAN_EPOCH - 1) +
            (365 * ($year - 1)) +
            floor(($year - 1) / 4) +
            (-floor(($year - 1) / 100)) +
            floor(($year - 1) / 400) +
            floor((((367 * $month) - 362) / 12) + (($month <= 2) ? 0 : ($this->isLeapGregorian($year) ? -1 : -2)) + $day);
    }

    /**
     * یک عدد روز ژولینی را به تاریخ میلادی تبدیل می‌کند.
     * @param float $jd عدد روز ژولینی
     * @return array آرایه‌ای شامل [سال, ماه, روز]
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

    // --- تقویم قمری (ISLAMIC - ALGORITHMIC) ---

    /**
     * بررسی می‌کند که آیا یک سال قمری (بر اساس الگوریتم حسابی) کبیسه است یا خیر.
     * @param int $year سال قمری
     * @return bool
     */
    public function isLeapIslamic(int $year): bool
    {
        return ((($year * 11) + 14) % 30) < 11;
    }

    /**
     * یک تاریخ قمری را به عدد روز ژولینی تبدیل می‌کند.
     * @param int $year سال
     * @param int $month ماه
     * @param int $day روز
     * @return float عدد روز ژولینی
     */
    public function islamicToJd(int $year, int $month, int $day): float
    {
        return ($day + ceil(29.5 * ($month - 1)) + ($year - 1) * 354 + floor((3 + (11 * $year)) / 30) + self::ISLAMIC_EPOCH) - 1;
    }

    /**
     * یک عدد روز ژولینی را به تاریخ قمری تبدیل می‌کند.
     * @param float $jd عدد روز ژولینی
     * @return array آرایه‌ای شامل [سال, ماه, روز]
     */
    public function jdToIslamic(float $jd): array
    {
        $jd = floor($jd) + 0.5;
        $year = (int) floor(((30 * ($jd - self::ISLAMIC_EPOCH)) + 10646) / 10631);
        $month = (int) min(12, ceil(($jd - (29 + $this->islamicToJd($year, 1, 1))) / 29.5) + 1);
        $day = (int) (($jd - $this->islamicToJd($year, $month, 1)) + 1);
        return [$year, $month, $day];
    }
    
    // --- تقویم شمسی با دقت بالا (HIGH-PRECISION PERSIAN CALENDAR) ---
    
    /**
     * بررسی می‌کند که آیا سال شمسی کبیسه است یا خیر.
     * @param int $pYear سال شمسی
     * @return bool
     */
    public function isLeapPersian(int $pYear): bool
    {
        // این الگوریتم دقیق برای سال کبیسه، برای بازه بسیار بزرگی از سال‌ها صحیح عمل می‌کند
        $a = $pYear + 2346;
        $b = 2820;
        $rem = $a % $b;
        if($rem < 21) $rem += $b;
        return (((($rem - 21) % 128) * 31) % 128) < 31;
    }

    /**
     * یک عدد روز ژولینی را به تاریخ شمسی تبدیل می‌کند.
     * @param float $jd عدد روز ژولینی
     * @return array آرایه‌ای شامل [سال, ماه, روز]
     */
    public function jdToPersian(float $jd): array
    {
        // ابتدا عدد ژولینی را به تاریخ میلادی معادل تبدیل می‌کنیم
        list($gYear, $gMonth, $gDay) = $this->jdToGregorian($jd);
        
        // سپس از الگوریتم دقیق تبدیل میلادی به شمسی استفاده می‌کنیم
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $pYear = $gYear - 621;
        $g_day_no = $g_d_m[$gMonth - 1] + $gDay;
        
        if ($this->isLeapGregorian($gYear) && $gMonth > 2) {
            $g_day_no++;
        }
        
        // آفست ۷۹ روزه بین شروع سال میلادی و شمسی
        $p_day_no = $g_day_no - 79;
        
        if ($p_day_no <= 0) {
            $p_day_no += 365 + ($this->isLeapGregorian($gYear - 1) ? 1 : 0);
            $pYear--;
        }
        
        $pMonth = ($p_day_no <= 186) ? ceil($p_day_no / 31) : ceil(($p_day_no - 186) / 30) + 6;
        $pDay = $p_day_no - (($pMonth <= 6) ? (($pMonth - 1) * 31) : (186 + ($pMonth - 7) * 30));

        return [$pYear, $pMonth, $pDay];
    }
    
    /**
     * یک تاریخ شمسی را به عدد روز ژولینی تبدیل می‌کند.
     * @param int $pYear سال
     * @param int $pMonth ماه
     * @param int $pDay روز
     * @return float عدد روز ژولینی
     */
    public function persianToJd(int $pYear, int $pMonth, int $pDay): float
    {
        // ابتدا تاریخ شمسی را به میلادی معادل تبدیل می‌کنیم
        $doy = ($pMonth <= 6) ? (($pMonth - 1) * 31) + $pDay : (186 + ($pMonth - 7) * 30) + $pDay;
        $gYear = $pYear + 621;
        
        $gDoyStart = $this->isLeapGregorian($gYear) ? 80 : 79;
        
        $g_day_no = $doy + $gDoyStart;
        
        if ($g_day_no > (365 + ($this->isLeapGregorian($gYear) ? 1 : 0))) {
            $g_day_no -= (365 + ($this->isLeapGregorian($gYear) ? 1 : 0));
            $gYear++;
        }
        
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        if($this->isLeapGregorian($gYear)) {
            $g_d_m = [0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335];
        }

        $gMonth = 0;
        while ($gMonth < 12 && $g_day_no > $g_d_m[$gMonth]) {
            $gMonth++;
        }
        $gDay = $g_day_no - $g_d_m[$gMonth - 1];
        
        // در نهایت، تاریخ میلادی به دست آمده را به عدد روز ژولینی تبدیل می‌کنیم
        return $this->gregorianToJd($gYear, $gMonth, $gDay);
    }
    
    // --- توابع کمکی تبدیل مستقیم (Convenience Converters) ---
    // این توابع برای راحتی بیشتر فراهم شده‌اند تا فرآیند دو مرحله‌ای تبدیل در یک تابع خلاصه شود.
    public function gregorianToPersian(int $y, int $m, int $d): array { return $this->jdToPersian($this->gregorianToJd($y, $m, $d)); }
    public function persianToGregorian(int $y, int $m, int $d): array { return $this->jdToGregorian($this->persianToJd($y, $m, $d)); }
    public function gregorianToIslamic(int $y, int $m, int $d): array { return $this->jdToIslamic($this->gregorianToJd($y, $m, $d)); }
    public function islamicToGregorian(int $y, int $m, int $d): array { return $this->jdToGregorian($this->islamicToJd($y, $m, $d)); }
    public function persianToIslamic(int $y, int $m, int $d): array { return $this->jdToIslamic($this->persianToJd($y, $m, $d)); }
    public function islamicToPersian(int $y, int $m, int $d): array { return $this->jdToPersian($this->islamicToJd($y, $m, $d)); }

    // --- ابزارها (UTILITY) ---
    
    /**
     * نام روز هفته را برای یک عدد روز ژولینی مشخص برمی‌گرداند.
     * @param float $jd عدد روز ژولینی
     * @param string $locale زبان خروجی (فقط 'fa' پشتیبانی می‌شود)
     * @return string نام روز هفته
     */
    public function getWeekday(float $jd, string $locale = 'fa'): string
    {
        $weekdays = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه'];
        $dayIndex = floor($jd + 1.5) % 7;
        return $weekdays[$dayIndex] ?? 'Unknown';
    }
}