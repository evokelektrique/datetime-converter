<?php
// REQUIRE THE CONVERTER CLASS
// فراخوانی کلاس اصلی مبدل تاریخ که تمام منطق محاسبات در آن قرار دارد
require_once 'CalendarConverter.php';

// INSTANTIATE THE CONVERTER
// ایجاد یک نمونه (instance) از کلاس مبدل برای استفاده در ادامه
$converter = new CalendarConverter();

// تعریف متغیرهای اولیه برای نمایش در صفحه
$result = null;
$weekday = null;
$leap_year_status = null;
$error_message = null;

// Set default date to today's Gregorian date from the server
// تنظیم تاریخ پیش‌فرض برای اولین بارگذاری صفحه، بر اساس تاریخ امروز سرور
$input_date = [
    'year' => date('Y'),
    'month' => date('m'),
    'day' => date('d')
];
$conversion_type = 'gregorian_to_persian'; // نوع تبدیل پیش‌فرض

// بررسی می‌کند که آیا فرم ارسال شده است (کاربر روی دکمه "تبدیل کن" کلیک کرده است)
if (isset($_POST['convert'])) {
    // دریافت نوع تبدیل و تاریخ از فرم ارسال شده
    $conversion_type = $_POST['conversion_type'];
    // دریافت مقادیر سال، ماه و روز به صورت امن
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
    $month = filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT);
    $day = filter_input(INPUT_POST, 'day', FILTER_VALIDATE_INT);

    // به‌روزرسانی آرایه تاریخ ورودی برای نمایش مجدد در فرم
    $input_date = compact('year', 'month', 'day');

    // اعتبارسنجی: بررسی می‌کند که آیا سال، ماه و روز معتبر هستند یا خیر
    if ($year && $month && $day) {
        $result_arr = [];
        $jd = 0; // متغیر برای نگهداری عدد روز ژولینی

        try {
            // بر اساس نوع تبدیل انتخاب شده، عملیات مربوطه را انجام می‌دهد
            switch ($conversion_type) {
                case 'gregorian_to_persian':
                    $jd = $converter->gregorianToJd($year, $month, $day); // تبدیل تاریخ ورودی به عدد روز ژولینی
                    $result_arr = $converter->jdToPersian($jd); // تبدیل عدد ژولینی به تاریخ مقصد
                    $result = "تاریخ شمسی: " . implode(' / ', $result_arr); // آماده‌سازی رشته خروجی
                    $leap_year_status = $converter->isLeapPersian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی'; // بررسی وضعیت سال کبیسه
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

            // اگر تبدیل موفق بود، روز هفته را نیز محاسبه می‌کند
            if ($jd > 0) {
                $weekday = $converter->getWeekday($jd);
            }
        } catch (Exception $e) {
            $error_message = "خطا در تبدیل: " . $e->getMessage();
        }

    } else {
        // اگر تاریخ ورودی معتبر نباشد، این پیام خطا نمایش داده می‌شود
        $error_message = "لطفا تاریخ معتبری را وارد کنید.";
    }
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مبدل تاریخ</title>
    
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr-hijri-calendar@1.0.0/dist/flatpickr-hijri-calendar.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background-color: #f0f2f5; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 2rem; margin: 0; background-image: url(./bg.jpg); background-size: cover; background-position: center; background-repeat: no-repeat; box-sizing: border-box; }
        .container { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); padding: 2.5rem; border-radius: 12px; box-shadow: 0 12px 28px rgba(0,0,0,0.12); width: 100%; max-width: 480px; }
        h1 { color: #1d2d35; text-align: center; margin-bottom: 2rem; font-size: 2rem; font-weight: 700; }
        form { display: flex; flex-direction: column; gap: 1.75rem; }
        .form-group { display: flex; flex-direction: column; }
        label { margin-bottom: 0.75rem; font-weight: 600; color: #4a5568; font-size: 1.1rem; }
        input[type="text"], select { padding: 0.9rem; border: 1px solid #d2d6dc; border-radius: 8px; font-size: 1rem; font-family: 'Vazirmatn', sans-serif; transition: border-color 0.2s, box-shadow 0.2s; width: 100%; box-sizing: border-box; background-color: #fff; }
        input[type="text"]:focus, select:focus { outline: none; border-color: #3182ce; box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.3); }
        button { padding: 0.9rem; background: linear-gradient(135deg, #3182ce, #4299e1); color: #fff; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; font-family: 'Vazirmatn', sans-serif; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 15px rgba(49, 130, 206, 0.25); }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(49, 130, 206, 0.35); }
        .result, .error { margin-top: 2rem; padding: 1.25rem; background-color: #edf2f7; border-radius: 8px; text-align: center; font-size: 1.2rem; font-weight: 600; color: #1d2d35; border: 1px solid #e2e8f0; }
        .error { background-color: #fed7d7; color: #c53030; border-color: #f5c6cb; }
        .extra-info { margin-top: 1rem; font-size: 1rem; color: #4a5568; display: flex; justify-content: center; gap: 1.5rem; }
        .extra-info span { background-color: #e2e8f0; padding: 0.5rem 1rem; border-radius: 20px; }
    </style>
</head>
<body>

    <div class="container">
        <h1>مبدل تاریخ</h1>
        <form method="post" id="date-form">
            <div class="form-group">
                <label for="conversion_type">نوع تبدیل</label>
                <select id="conversion_type" name="conversion_type">
                    <option value="gregorian_to_persian" <?php echo ($conversion_type === 'gregorian_to_persian') ? 'selected' : ''; ?>>میلادی به شمسی</option>
                    <option value="persian_to_gregorian" <?php echo ($conversion_type === 'persian_to_gregorian') ? 'selected' : ''; ?>>شمسی به میلادی</option>
                    <option value="gregorian_to_islamic" <?php echo ($conversion_type === 'gregorian_to_islamic') ? 'selected' : ''; ?>>میلادی به قمری</option>
                    <option value="islamic_to_gregorian" <?php echo ($conversion_type === 'islamic_to_gregorian') ? 'selected' : ''; ?>>قمری به میلادی</option>
                    <option value="persian_to_islamic" <?php echo ($conversion_type === 'persian_to_islamic') ? 'selected' : ''; ?>>شمسی به قمری</option>
                    <option value="islamic_to_persian" <?php echo ($conversion_type === 'islamic_to_persian') ? 'selected' : ''; ?>>قمری به شمسی</option>
                </select>
            </div>
            <div class="form-group">
                <label for="datepicker">تاریخ</label>
                <input type="text" id="datepicker" placeholder="تاریخ را انتخاب کنید">
                <input type="hidden" name="year" id="year" value="<?php echo htmlspecialchars($input_date['year']); ?>">
                <input type="hidden" name="month" id="month" value="<?php echo htmlspecialchars($input_date['month']); ?>">
                <input type="hidden" name="day" id="day" value="<?php echo htmlspecialchars($input_date['day']); ?>">
            </div>
            <button type="submit" name="convert">تبدیل کن</button>
        </form>

        <?php if ($error_message): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php elseif ($result !== null): ?>
            <div class="result">
                <div><?php echo htmlspecialchars($result); ?></div>
                <div class="extra-info">
                    <span><?php echo htmlspecialchars($weekday); ?></span>
                    <span><?php echo htmlspecialchars($leap_year_status); ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/l10n/ar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@2.3.0/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr-hijri-calendar@1.0.0/dist/flatpickr-hijri-calendar.min.js"></script>

    <script>
    // این کد زمانی اجرا می‌شود که کل صفحه به طور کامل بارگذاری شده باشد
    $(document).ready(function() {
        // تعریف متغیرهای ثابت برای دسترسی آسان به عناصر صفحه
        const conversionSelect = $('#conversion_type');
        const dateInput = $('#datepicker');
        const yearInput = $('#year');
        const monthInput = $('#month');
        const dayInput = $('#day');

        // متغیری برای نگهداری نمونه ساخته شده از کتابخانه Flatpickr
        let flatpickrInstance = null;

        // تابعی برای به‌روزرسانی مقادیر فیلدهای مخفی فرم که به سرور ارسال می‌شوند
        function setHiddenFields(year, month, day) {
            yearInput.val(year);
            monthInput.val(month);
            dayInput.val(day);
        }

        // تابعی برای پاک کردن و حذف کامل نمونه‌های قبلی Datepickerها
        // این کار برای جلوگیری از تداخل بین کتابخانه‌ها ضروری است
        function destroyPickers() {
            if (dateInput.data('datepicker')) {
                dateInput.pDatepicker('destroy');
            }
            if (flatpickrInstance) {
                flatpickrInstance.destroy();
                flatpickrInstance = null;
            }
        }

        // یک تابع کمکی برای فرمت کردن و نمایش تاریخ هجری قمری در اینپوت
        function updateHijriDisplay(instance) {
            if (!instance.selectedDates[0]) return;
            const luxonDate = luxon.DateTime.fromJSDate(instance.selectedDates[0]);
            const hijriDate = luxonDate.reconfigure({ outputCalendar: 'islamic' });
            // به‌روزرسانی فیلدهای مخفی برای ارسال به سرور
            setHiddenFields(hijriDate.year, hijriDate.month, hijriDate.day);
            // به‌روزرسانی مقدار قابل مشاهده در اینپوت برای کاربر
            instance.input.value = `${hijriDate.year}/${hijriDate.month}/${hijriDate.day}`;
        }

        // تابع اصلی برای راه‌اندازی و تنظیم Datepicker مناسب بر اساس نوع تبدیل
        function setupDatepicker(conversionType) {
            destroyPickers(); // اولین قدم: پاک کردن تقویم‌های قبلی

            const isIslamicSource = conversionType.startsWith('islamic');
            
            // خواندن مقادیر پیش‌فرض از فیلدهای مخفی که توسط PHP مقداردهی شده‌اند
            const defaultYear = parseInt(yearInput.val());
            const defaultMonth = parseInt(monthInput.val());
            const defaultDay = parseInt(dayInput.val());

            // ** منطق اصلی انتخاب و راه‌اندازی Datepicker **
            if (isIslamicSource) {
                // اگر تقویم مبدأ قمری بود، از Flatpickr استفاده کن
                
                // 1. با استفاده از کتابخانه لاکسان، تاریخ قمری را به یک آبجکت تاریخ میلادی استاندارد جاوا اسکریپت تبدیل می‌کنیم
                // این کار ضروری است چون Flatpickr برای تعیین تاریخ اولیه خود به یک تاریخ میلادی نیاز دارد
                const luxonDateFromHijri = luxon.DateTime.fromObject(
                    { year: defaultYear, month: defaultMonth, day: defaultDay },
                    { calendar: 'islamic' }
                );
                const initialGregorianDate = luxonDateFromHijri.toJSDate();

                // 2. مقداردهی اولیه Flatpickr با تنظیمات مخصوص تقویم قمری
                flatpickrInstance = flatpickr('#datepicker', {
                    locale: 'ar', // استفاده از زبان عربی برای نمایش ماه‌ها و چیدمان راست‌به‌چپ
                    plugins: [
                        hijriCalendarPlugin(luxon.DateTime, {
                            showHijriDates: true,
                            showHijriToggle: false,
                        })
                    ],
                    defaultDate: initialGregorianDate, // تنظیم تاریخ پیش‌فرض
                    // این تابع زمانی اجرا می‌شود که کاربر یک تاریخ جدید انتخاب کند
                    onChange: (selectedDates, dateStr, instance) => {
                        updateHijriDisplay(instance);
                    },
                    // این تابع بلافاصله پس از آماده شدن تقویم اجرا می‌شود تا تاریخ اولیه را به درستی نمایش دهد
                    onReady: (selectedDates, dateStr, instance) => {
                        updateHijriDisplay(instance);
                    }
                });
            } else { 
                // برای تقویم‌های شمسی و میلادی، از Persian-Datepicker استفاده کن
                const isPersian = conversionType.startsWith('persian');
                let options = {
                    initialValue: false, // جلوگیری از پرش به تاریخ امروز
                    // این تابع زمانی اجرا می‌شود که کاربر یک تاریخ جدید انتخاب کند
                    onSelect: function(unix) {
                        const pd = new persianDate(unix);
                        if (isPersian) {
                            setHiddenFields(pd.year(), pd.month(), pd.date());
                        } else { // Gregorian
                            const greg = pd.toCalendar('gregorian');
                            setHiddenFields(greg.year(), greg.month(), greg.date());
                        }
                    }
                };
                
                // تنظیم نوع تقویم و فرمت نمایش بر اساس انتخاب کاربر
                if(isPersian) {
                    options.calendar = { persian: { locale: 'fa' } };
                    options.formatter = (unix) => new persianDate(unix).toLocale('fa').format('YYYY/MM/DD');
                } else { // Gregorian
                    options.calendar = { gregorian: { locale: 'en' } };
                    options.formatter = (unix) => new persianDate(unix).toCalendar('gregorian').toLocale('en').format('YYYY/MM/DD');
                }
                
                dateInput.pDatepicker(options);

                // ساختن یک نمونه تاریخ بر اساس مقادیر پیش‌فرض
                let pDateInstance = isPersian 
                    ? new persianDate([defaultYear, defaultMonth, defaultDay])
                    : new persianDate(new Date(defaultYear, defaultMonth - 1, defaultDay));
                
                // اگر تاریخ معتبر بود، آن را در تقویم نمایش بده و فیلدهای مخفی را هم همزمان کن
                if (pDateInstance && !isNaN(pDateInstance.valueOf())) {
                    dateInput.pDatepicker('setDate', pDateInstance.valueOf());
                    
                    // این بخش باگ مربوط به ارسال فرم خالی در اولین بارگذاری را رفع می‌کند
                    if (isPersian) {
                        setHiddenFields(pDateInstance.year(), pDateInstance.month(), pDateInstance.date());
                    } else { // Gregorian
                        const greg = pDateInstance.toCalendar('gregorian');
                        setHiddenFields(greg.year(), greg.month(), greg.date());
                    }
                }
            }
        }

        // اجرای تابع راه‌اندازی برای اولین بار که صفحه بارگذاری می‌شود
        setupDatepicker(conversionSelect.val());

        // این تابع هر بار که کاربر نوع تبدیل را در منوی کشویی تغییر می‌دهد، اجرا می‌شود
        conversionSelect.on('change', function() {
            // برای جلوگیری از خطا، تاریخ را به "امروز" در تقویم جدید انتخاب شده، ریست می‌کنیم
            const today = new persianDate(); 
            const newModeIsPersian = this.value.startsWith('persian');
            const newModeIsIslamic = this.value.startsWith('islamic');
            
            if (newModeIsPersian) {
                setHiddenFields(today.year(), today.month(), today.date());
            } else if (newModeIsIslamic) {
                const islamic = today.toCalendar('islamic');
                setHiddenFields(islamic.year(), islamic.month(), islamic.date());
            } else { // Gregorian
                const greg = today.toCalendar('gregorian');
                setHiddenFields(greg.year(), greg.month(), greg.date());
            }
            // پس از ریست کردن تاریخ، تقویم جدید را راه‌اندازی می‌کند
            setupDatepicker(this.value);
        });
    });
    </script>
</body>
</html>