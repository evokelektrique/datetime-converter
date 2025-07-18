import React, { useState, useEffect } from 'react';
import DatePicker from "react-multi-date-picker";
import DateObject from "react-date-object";

// وارد کردن موتورهای تقویم از کتابخانه
import gregorian from "react-date-object/calendars/gregorian";
import persian from "react-date-object/calendars/persian";
import arabic from "react-date-object/calendars/arabic";

// وارد کردن فایل‌های زبان (شامل نام ماه‌ها، روزها و چیدمان راست‌به‌چپ)
import gregorian_en from "react-date-object/locales/gregorian_en";
import persian_fa from "react-date-object/locales/persian_fa";
import arabic_ar from "react-date-object/locales/arabic_ar";

// وارد کردن استایل‌های کامپوننت
import './App.css';

/**
 * یک تابع کمکی که بر اساس نوع تبدیل انتخاب شده، آبجکت‌های تقویم و زبان مناسب را برمی‌گرداند.
 * @param {string} type - نوع تبدیل انتخاب شده (مثلاً 'persian_to_gregorian').
 * @returns {{calendar: object, locale: object}}
 */
const getSourceCalendarProps = (type) => {
  switch (type) {
    case 'persian_to_gregorian':
    case 'persian_to_islamic':
      return { calendar: persian, locale: persian_fa }; // اگر مبدأ شمسی بود، از تقویم و زبان فارسی استفاده کن
    case 'islamic_to_gregorian':
    case 'islamic_to_persian':
      return { calendar: arabic, locale: arabic_ar }; // اگر مبدأ قمری بود، از تقویم و زبان عربی استفاده کن
    case 'gregorian_to_persian':
    case 'gregorian_to_islamic':
    default:
      return { calendar: gregorian, locale: gregorian_en }; // در غیر این صورت (مبدأ میلادی)، از تقویم و زبان انگلیسی استفاده کن
  }
};

// کامپوننت اصلی اپلیکیشن
export default function App() {
  // --- مدیریت وضعیت (State Management) ---
  // تعریف متغیرهای وضعیت برای نگهداری داده‌های برنامه
  const [conversionType, setConversionType] = useState("gregorian_to_persian"); // وضعیت برای نوع تبدیل انتخاب شده
  const [date, setDate] = useState(new DateObject()); // وضعیت برای تاریخ انتخاب شده در تقویم
  const [result, setResult] = useState(null); // وضعیت برای نگهداری نتیجه دریافت شده از سرور
  const [loading, setLoading] = useState(false); // وضعیت برای نمایش حالت لودینگ هنگام ارسال درخواست
  const [error, setError] = useState(""); // وضعیت برای نمایش پیام‌های خطا

  // --- هوک useEffect ---
  // این هوک هر بار که نوع تبدیل (conversionType) تغییر می‌کند، به صورت خودکار اجرا می‌شود.
  useEffect(() => {
    const today = new DateObject(); // تاریخ امروز را دریافت می‌کند
    // بر اساس نوع تبدیل جدید، تقویم و زبان جدید را از تابع کمکی دریافت می‌کند
    const { calendar: newCalendar, locale: newLocale } = getSourceCalendarProps(conversionType);
    // تاریخ را به تاریخ "امروز" در سیستم تقویم جدید تنظیم می‌کند
    setDate(today.convert(newCalendar, newLocale));
    setResult(null); // نتیجه قبلی را پاک می‌کند
  }, [conversionType]); // وابستگی به conversionType: فقط زمانی اجرا شو که این متغیر تغییر کند

  // --- تابع ارسال فرم ---
  // این تابع زمانی که کاربر روی دکمه "تبدیل کن" کلیک می‌کند، اجرا می‌شود.
  const handleSubmit = async (e) => {
    e.preventDefault(); // جلوگیری از رفرش شدن صفحه
    setLoading(true); // فعال کردن حالت لودینگ
    setError(""); // پاک کردن خطاهای قبلی
    setResult(null); // پاک کردن نتایج قبلی

    try {
      // ارسال درخواست به API نوشته شده با PHP
      const response = await fetch("http://localhost:8000/api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        // ارسال داده‌های تاریخ و نوع تبدیل در قالب JSON
        body: JSON.stringify({
          conversion_type: conversionType,
          year: date.year,
          month: date.month.number,
          day: date.day,
        }),
      });

      if (!response.ok) throw new Error("Network response was not ok");
      
      const data = await response.json();
      
      // بر اساس پاسخ دریافتی از سرور، وضعیت نتیجه یا خطا را به‌روزرسانی می‌کند
      if (data.error) setError(data.error);
      else setResult(data);

    } catch (err) {
      setError("An error occurred while fetching data.");
    } finally {
      setLoading(false); // غیرفعال کردن حالت لودینگ در هر صورت (موفقیت یا خطا)
    }
  };

  // دریافت پراپرتی‌های (تنظیمات) فعلی برای انتخاب‌گر تاریخ
  const pickerProps = getSourceCalendarProps(conversionType);

  // --- رندر کردن کامپوننت ---
  // این بخش کدهای JSX را برای نمایش ساختار HTML صفحه برمی‌گرداند.
  return (
    <div className="container">
      <h1>مبدل تاریخ</h1>
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="conversion_type">نوع تبدیل</label>
          <select id="conversion_type" value={conversionType} onChange={(e) => setConversionType(e.target.value)}>
              <option value="gregorian_to_persian">میلادی به شمسی</option>
              <option value="persian_to_gregorian">شمسی به میلادی</option>
              <option value="gregorian_to_islamic">میلادی به قمری</option>
              <option value="islamic_to_gregorian">قمری به میلادی</option>
              <option value="persian_to_islamic">شمسی به قمری</option>
              <option value="islamic_to_persian">قمری به شمسی</option>
          </select>
        </div>
        <div className="form-group">
          <label>تاریخ</label>
          {/* کامپوننت انتخاب‌گر تاریخ با تنظیمات پویا */}
          <DatePicker
            value={date}
            onChange={setDate}
            calendar={pickerProps.calendar} // تقویم پویا
            locale={pickerProps.locale}     // زبان و چیدمان پویا
            format="YYYY/MM/DD"
            className="rmdp-prime"
          />
        </div>
        <button type="submit" disabled={loading}>
          {loading ? "در حال تبدیل..." : "تبدیل کن"}
        </button>
      </form>

      {/* نمایش شرطی: این بخش‌ها فقط در صورتی نمایش داده می‌شوند که مقدار داشته باشند */}
      {error && <div className="error">{error}</div>}
      {result && (
        <div className="result">
          <div>{result.result}</div>
          <div className="extra-info">
            <span>{result.weekday}</span>
            <span>{result.leap_year_status}</span>
          </div>
        </div>
      )}
    </div>
  );
}