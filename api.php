<?php
// --- هدرهای HTTP ---

// (CORS) اجازه می‌دهد تا فرانت‌اند (که روی پورت دیگری در حال اجراست) به این API دسترسی داشته باشد
header("Access-Control-Allow-Origin: *");
// اجازه می‌دهد تا هدرهایی مانند Content-Type در درخواست وجود داشته باشد
header("Access-Control-Allow-Headers: Content-Type");
// به مرورگر اعلام می‌کند که متدهای POST و OPTIONS مجاز هستند
header("Access-Control-Allow-Methods: POST, OPTIONS");
// نوع محتوای پاسخ را به عنوان JSON با انکدینگ UTF-8 تنظیم می‌کند
header("Content-Type: application/json; charset=UTF-8");

// --- مدیریت درخواست پیشواز (Preflight Request) ---

// مرورگرها قبل از ارسال درخواست POST، یک درخواست از نوع OPTIONS برای بررسی مجوزها ارسال می‌کنند
// این بخش به آن درخواست پاسخ موفقیت‌آمیز (204) می‌دهد و اسکریپت را متوقف می‌کند
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

// --- منطق اصلی API ---

// فراخوانی کلاس اصلی مبدل تاریخ
require_once 'CalendarConverter.php';
// ایجاد یک نمونه از کلاس مبدل
$converter = new CalendarConverter();

// دریافت داده‌های JSON ارسال شده از فرانت‌اند و تبدیل آن به آرایه PHP
$post_data = json_decode(file_get_contents('php://input'), true);

// اعتبارسنجی اولیه: بررسی می‌کند که آیا داده‌ای ارسال شده است یا خیر
if (!$post_data || !isset($post_data['year'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// استخراج داده‌ها از آرایه ورودی
$conversion_type = $post_data['conversion_type'];
$year = (int)$post_data['year'];
$month = (int)$post_data['month'];
$day = (int)$post_data['day'];

// آرایه‌ای برای نگهداری پاسخ نهایی
$response = [];

// اعتبارسنجی ثانویه: بررسی می‌کند که آیا مقادیر تاریخ معتبر هستند
if ($year && $month && $day) {
    try {
        // بر اساس نوع تبدیل، عملیات مربوطه را اجرا می‌کند
        switch ($conversion_type) {
            case 'gregorian_to_persian':
                $jd = $converter->gregorianToJd($year, $month, $day);
                $result_arr = $converter->jdToPersian($jd);
                $result = "تاریخ شمسی: " . implode(' / ', $result_arr);
                $leap_year_status = $converter->isLeapPersian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'persian_to_gregorian':
                $jd = $converter->persianToJd($year, $month, $day);
                $result_arr = $converter->jdToGregorian($jd);
                $result = "تاریخ میلادی: " . implode(' / ', $result_arr);
                $leap_year_status = $converter->isLeapGregorian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'gregorian_to_islamic':
                $jd = $converter->gregorianToJd($year, $month, $day);
                $result_arr = $converter->jdToIslamic($jd);
                $result = "تاریخ قمری: " . implode(' / ', $result_arr);
                $leap_year_status = $converter->isLeapIslamic($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'islamic_to_gregorian':
                $jd = $converter->islamicToJd($year, $month, $day);
                $result_arr = $converter->jdToGregorian($jd);
                $result = "تاریخ میلادی: " . implode(' / ', $result_arr);
                $leap_year_status = $converter->isLeapGregorian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'persian_to_islamic':
                $jd = $converter->persianToJd($year, $month, $day);
                $result_arr = $converter->jdToIslamic($jd);
                $result = "تاریخ قمری: " . implode(' / ', $result_arr);
                $leap_year_status = $converter->isLeapIslamic($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'islamic_to_persian':
                $jd = $converter->islamicToJd($year, $month, $day);
                $result_arr = $converter->jdToPersian($jd);
                $result = "تاریخ شمسی: " . implode(' / ', $result_arr);
                $leap_year_status = $converter->isLeapPersian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
        }
        
        // اگر تبدیل موفقیت‌آمیز بود و عدد روز ژولینی محاسبه شده بود
        if (isset($jd) && $jd > 0) {
            // روز هفته را نیز محاسبه می‌کند
            $weekday = $converter->getWeekday($jd);
            // پاسخ کامل را در آرایه قرار می‌دهد
            $response = ['result' => $result, 'weekday' => $weekday, 'leap_year_status' => $leap_year_status];
        }
    } catch (Exception $e) {
        // مدیریت خطاهای احتمالی در حین محاسبات
        http_response_code(500); // Internal Server Error
        $response = ['error' => 'An error occurred during conversion.'];
    }
} else {
    // در صورت نامعتبر بودن تاریخ ورودی
    $response = ['error' => 'لطفا تاریخ معتبری را وارد کنید.'];
}

// --- خروجی نهایی ---
// پاسخ نهایی را به فرمت JSON انکود کرده و برای فرانت‌اند ارسال می‌کند
echo json_encode($response);