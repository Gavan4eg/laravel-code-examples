<?php

namespace App\Helpers;

use Carbon\Carbon;

class BirthdayHelper
{
    /**
     * Обчислює вік людини на основі дати народження з урахуванням часового поясу.
     *
     * @param Carbon  $birthdate Дата народження людини
     * @param string  $timezone Часовий пояс користувача
     * @return int    Вік людини
     */
    public static function age(Carbon $birthdate, string $timezone = null): int
    {
        // Отримуємо поточну дату і час з урахуванням заданого часового поясу
        $now = Carbon::now($timezone);
        // Створюємо новий об'єкт Carbon тільки з року, місяця та дня
        $now = Carbon::create($now->year, $now->month, $now->day);

        // Повертаємо різницю в роках між поточною датою і датою народження
        return $birthdate->diffInYears($now, true);
    }

    /**
     * Вказує, чи є задана дата днем народження в наступні X днів.
     * @param Carbon $startDate Початкова дата
     * @param Carbon $birthdate Дата народження
     * @param int $numberOfDays Кількість днів
     * @return bool
     */
    public static function isBirthdayInXDays(Carbon $startDate, Carbon $birthdate, int $numberOfDays): bool
    {
        // Копіюємо початкову дату і додаємо до неї кількість днів
        $future = $startDate->copy()->addDays($numberOfDays);
        // Встановлюємо рік дати народження таким же, як і рік початкової дати
        $birthdate->year = $startDate->year;

        // Якщо день народження вже пройшов у поточному році, повертаємо false
        if ($birthdate->isPast()) {
            return false;
        }

        // Перевіряємо, чи потрапляє день народження в заданий інтервал днів
        return $birthdate->lessThanOrEqualTo($future) && $future->greaterThanOrEqualTo($startDate);
    }

    /**
     * Перевіряє, чи відбудеться день народження в заданому діапазоні дат.
     *
     * @param Carbon $birthdate Дата народження
     * @param Carbon $minDate Мінімальна дата
     * @param Carbon $maxDate Максимальна дата
     * @return bool
     */
    public static function isBirthdayInRange(Carbon $birthdate, Carbon $minDate, Carbon $maxDate): bool
    {
        // Перевіряє, чи знаходиться дата народження в заданому діапазоні дат
        return $birthdate->between($minDate, $maxDate);
    }
}
