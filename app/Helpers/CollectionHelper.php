<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class CollectionHelper
{
    /**
     * Сортує колекцію за допомогою заданого колбека.
     */
    public static function sortByCollator(Collection $collect, callable|string $callback, int $options = \Collator::SORT_STRING, bool $descending = false): Collection
    {
        $results = [];

        $callback = static::valueRetriever($callback);

        // Спочатку перебираємо всі елементи колекції і отримуємо значення за допомогою колбека.
        foreach ($collect->all() as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        // Використовуємо Collator для сортування масиву з урахуванням локалі.
        static::getCollator()->asort($results, $options);
        if ($descending) {
            $results = array_reverse($results);
        }

        // Після сортування відновлюємо початкові значення з колекції.
        foreach (array_keys($results) as $key) {
            $results[$key] = $collect->get($key);
        }

        return new Collection(array_values($results));
    }

    /**
     * Отримує об'єкт Collator для поточної або заданої локалі.
     */
    public static function getCollator(?string $locale = null): \Collator
    {
        static $collators = [];

        if (! $locale) {
            $locale = App::getLocale();
        }
        if (! Arr::has($collators, $locale)) {
            $collator = new \Collator($locale);

            if ($locale === 'uk') {
                $collator->setStrength(\Collator::PRIMARY);
            }

            $collators[$locale] = $collator;

            return $collator;
        }

        return $collators[$locale];
    }

    /**
     * Отримує колбек для отримання значень.
     */
    protected static function valueRetriever(callable|string $value): callable
    {
        if (! is_string($value) && is_callable($value)) {
            return $value;
        }

        return fn ($item) => data_get($item, $value);
    }
}
