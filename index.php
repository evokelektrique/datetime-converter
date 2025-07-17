<?php
require_once 'calendar.php';

$result = null;
$weekday = null;
$leap_year_status = null;
$input_date = [
    'year' => date('Y'),
    'month' => date('m'),
    'day' => date('d')
];

if (isset($_POST['convert'])) {
    $conversion_type = $_POST['conversion_type'];
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
    $month = filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT);
    $day = filter_input(INPUT_POST, 'day', FILTER_VALIDATE_INT);

    $input_date = compact('year', 'month', 'day');

    if ($year && $month && $day) {
        $jd = 0;
        switch ($conversion_type) {
            case 'gregorian_to_persian':
                $jd = Calendar::gregorian_to_jd($year, $month, $day);
                $result_arr = Calendar::jd_to_persian($jd);
                $result = "تاریخ شمسی: " . implode(' / ', $result_arr);
                $leap_year_status = Calendar::leap_persian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'persian_to_gregorian':
                $jd = Calendar::persian_to_jd($year, $month, $day);
                $result_arr = Calendar::jd_to_gregorian($jd);
                $result = "تاریخ میلادی: " . implode(' / ', $result_arr);
                $leap_year_status = Calendar::leap_gregorian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'gregorian_to_islamic':
                $jd = Calendar::gregorian_to_jd($year, $month, $day);
                $result_arr = Calendar::jd_to_islamic($jd);
                $result = "تاریخ قمری: " . implode(' / ', $result_arr);
                $leap_year_status = Calendar::leap_islamic($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'islamic_to_gregorian':
                $jd = Calendar::islamic_to_jd($year, $month, $day);
                $result_arr = Calendar::jd_to_gregorian($jd);
                $result = "تاریخ میلادی: " . implode(' / ', $result_arr);
                $leap_year_status = Calendar::leap_gregorian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'persian_to_islamic':
                $jd = Calendar::persian_to_jd($year, $month, $day);
                $result_arr = Calendar::jd_to_islamic($jd);
                $result = "تاریخ قمری: " . implode(' / ', $result_arr);
                $leap_year_status = Calendar::leap_islamic($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;
            case 'islamic_to_persian':
                $jd = Calendar::islamic_to_jd($year, $month, $day);
                $result_arr = Calendar::jd_to_persian($jd);
                $result = "تاریخ شمسی: " . implode(' / ', $result_arr);
                $leap_year_status = Calendar::leap_persian($result_arr[0]) ? 'سال کبیسه' : 'سال عادی';
                break;


        }
        $weekday = Calendar::get_persian_weekday($jd);
    }
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مبدل تاریخ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(0,0,0,0.12);
            width: 100%;
            max-width: 480px;
        }
        h1 {
            color: #1d2d35;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #4a5568;
            font-size: 1.1rem;
        }
        input[type="text"], select {
            padding: 0.9rem;
            border: 1px solid #d2d6dc;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Vazirmatn', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
            box-sizing: border-box;
            background-color: #fff;
        }
        input[type="text"]:focus, select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.3);
        }
        button {
            padding: 0.9rem;
            background: linear-gradient(135deg, #3182ce, #4299e1);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            font-family: 'Vazirmatn', sans-serif;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(49, 130, 206, 0.25);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(49, 130, 206, 0.35);
        }
        .result {
            margin-top: 2rem;
            padding: 1.25rem;
            background-color: #edf2f7;
            border-radius: 8px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            color: #1d2d35;
            border: 1px solid #e2e8f0;
        }
        .extra-info {
            margin-top: 1rem;
            font-size: 1rem;
            color: #4a5568;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }
        .extra-info span {
            background-color: #e2e8f0;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>مبدل تاریخ</h1>
        <form method="post" id="date-form">
            <div class="form-group">
                <label for="conversion_type">نوع تبدیل</label>
                <select id="conversion_type" name="conversion_type">
                    <option value="gregorian_to_persian" <?php echo (($_POST['conversion_type'] ?? '') === 'gregorian_to_persian') ? 'selected' : ''; ?>>میلادی به شمسی</option>                    
                    <option value="persian_to_gregorian" <?php echo (($_POST['conversion_type'] ?? '') === 'persian_to_gregorian') ? 'selected' : ''; ?>>شمسی به میلادی</option>                    
                    <option value="gregorian_to_islamic" <?php echo (($_POST['conversion_type'] ?? '') === 'gregorian_to_islamic') ? 'selected' : ''; ?>>میلادی به قمری</option>                    
                    <option value="islamic_to_gregorian" <?php echo (($_POST['conversion_type'] ?? '') === 'islamic_to_gregorian') ? 'selected' : ''; ?>>قمری به میلادی</option>
                    <option value="persian_to_islamic" <?php echo (($_POST['conversion_type'] ?? '') === 'persian_to_islamic') ? 'selected' : ''; ?>>شمسی به قمری</option>
                    <option value="islamic_to_persian" <?php echo (($_POST['conversion_type'] ?? '') === 'islamic_to_persian') ? 'selected' : ''; ?>>قمری به شمسی</option>
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
        <?php if ($result !== null): ?>
            <div class="result">
                <div><?php echo $result; ?></div>
                <div class="extra-info">
                    <span><?php echo $weekday; ?></span>
                    <span><?php echo $leap_year_status; ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fa.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment-hijri@2.1.2/moment-hijri.min.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/gh/abublihi/datepicker-hijri@v1.1/build/datepicker-hijri.esm.js"></script>
    <script>
    $(document).ready(function() {
        const conversionSelect = $('#conversion_type');
        const dateInput = $('#datepicker');
        const yearInput = $('#year');
        const monthInput = $('#month');
        const dayInput = $('#day');

        let flatpickrInstance = null;
        let hijriPicker = null;

        function setHiddenFields(year, month, day) {
            yearInput.val(year);
            monthInput.val(month);
            dayInput.val(day);
        }

        function destroyPickers() {
            if (dateInput.data('datepicker')) {
                dateInput.pDatepicker('destroy');
            }
            if (flatpickrInstance) {
                flatpickrInstance.destroy();
                flatpickrInstance = null;
            }
            if (hijriPicker) {
                const picker = document.querySelector('datepicker-hijri');
                if(picker) {
                    picker.remove();
                }
                hijriPicker = null;
            }
            dateInput.off('change'); // Remove previous event listeners
            dateInput.prop('readonly', false);
        }

        function setupDatepicker(conversionType) {
            destroyPickers();

            const isPersianSource = conversionType.startsWith('persian');
            const isIslamicSource = conversionType.startsWith('islamic');

            if (isPersianSource) {
                dateInput.pDatepicker({
                    initialValue: true,
                    calendar: { persian: { locale: 'fa' } },
                    formatter: (unix) => new persianDate(unix).format('YYYY/MM/DD'),
                    onSelect: (unix) => {
                        const pdate = new persianDate(unix);
                        setHiddenFields(pdate.year(), pdate.month(), pdate.date());
                    },
                    onInit: function() {
                        const initialUnix = this.getState().selected.unixDate;
                        const pdate = new persianDate(initialUnix);
                        setHiddenFields(pdate.year(), pdate.month(), pdate.date());
                    }
                });
            } else if (isIslamicSource) {
                hijriPicker = document.createElement('datepicker-hijri');
                hijriPicker.setAttribute('reference', 'datepicker');
                hijriPicker.setAttribute('placement', 'bottom');
                dateInput.parent().append(hijriPicker);

                const mDate = moment(`${yearInput.val()}-${monthInput.val()}-${dayInput.val()}`, 'iYYYY-iM-iD');
                if (mDate.isValid()) {
                    dateInput.val(mDate.format('iYYYY/iM/iD'));
                } else {
                    const todayHijri = moment();
                    setHiddenFields(todayHijri.iYear(), todayHijri.iMonth() + 1, todayHijri.iDate());
                    dateInput.val(todayHijri.format('iYYYY/iM/iD'));
                }

                hijriPicker.addEventListener('date-selected', (e) => {
                    const date = e.detail.date;
                    setHiddenFields(date.iYear(), date.iMonth() + 1, date.iDate());
                    dateInput.val(date.format('iYYYY/iM/iD'));
                });

                dateInput.on('change', function() {
                    const mDate = moment($(this).val(), 'iYYYY/iM/iD');
                    if(mDate.isValid()) {
                        setHiddenFields(mDate.iYear(), mDate.iMonth() + 1, mDate.iDate());
                    }
                });

            } else { // Gregorian source
                flatpickrInstance = flatpickr(dateInput, {
                    altInput: true,
                    altFormat: "Y/m/d",
                    dateFormat: "Y-m-d",
                    locale: 'en',
                    defaultDate: new Date(parseInt(yearInput.val()), parseInt(monthInput.val()) - 1, parseInt(dayInput.val())),
                    onChange: (selectedDates) => {
                        if (selectedDates.length > 0) {
                            const date = selectedDates[0];
                            setHiddenFields(date.getFullYear(), date.getMonth() + 1, date.getDate());
                        }
                    }
                });
                 if (flatpickrInstance.selectedDates.length > 0) {
                    const date = flatpickrInstance.selectedDates[0];
                    setHiddenFields(date.getFullYear(), date.getMonth() + 1, date.getDate());
                }
            }
        }

        setupDatepicker(conversionSelect.val());

        conversionSelect.on('change', function() {
            setupDatepicker(this.value);
        });
    });
    </script>
</body>
</html>