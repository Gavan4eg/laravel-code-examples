<?php

namespace App\Services\Invoice;

use App\Models\Offer;
use App\Models\Invoice;
use App\Repositories\Tax\Tax;
use App\Repositories\Money\Money;

class InvoiceCalculatorService
{
    /**
     * @var Invoice
     */
    private $invoice;
    /**
     * @var Tax
     */
    private $tax;

    // Конструктор класу
    public function __construct($invoice)
    {
        // Перевірка, чи переданий об'єкт є екземпляром класу Invoice або Offer
        if(!$invoice instanceof Invoice && !$invoice instanceof Offer ) {
            // Кидання винятку, якщо переданий об'єкт має неправильний тип
            throw new \Exception("Not correct type for Invoice Calculator");
        }
        // Створення нового об'єкту Tax
        $this->tax = new Tax();
        // Збереження переданого об'єкту рахунку або пропозиції у властивості $invoice
        $this->invoice = $invoice;
    }

    // Метод для отримання загальної суми ПДВ
    public function getVatTotal()
    {
        // Отримання підсумкової суми без ПДВ
        $price = $this->getSubTotal()->getAmount();
        // Повернення нової суми Money з урахуванням ставки ПДВ
        return new Money($price * $this->tax->vatRate());
    }

    // Метод для отримання загальної суми рахунку
    public function getTotalPrice(): Money
    {
        $price = 0;
        // Отримання рядків рахунку
        $invoiceLines = $this->invoice->invoiceLines;

        // Обчислення загальної суми, множення кількості на ціну для кожного рядка
        foreach ($invoiceLines as $invoiceLine) {
            $price += $invoiceLine->quantity * $invoiceLine->price;
        }

        // Повернення нової суми Money
        return new Money($price);
    }

    // Метод для отримання підсумкової суми рахунку без ПДВ
    public function getSubTotal(): Money
    {
        $price = 0;
        // Отримання рядків рахунку
        $invoiceLines = $this->invoice->invoiceLines;

        // Обчислення загальної суми, множення кількості на ціну для кожного рядка
        foreach ($invoiceLines as $invoiceLine) {
            $price += $invoiceLine->quantity * $invoiceLine->price;
        }
        // Повернення нової суми Money, поділеної на множник ставки ПДВ
        return new Money($price / $this->tax->multipleVatRate());
    }

    // Метод для отримання суми до оплати
    public function getAmountDue()
    {
        // Повернення нової суми Money, обчисленої як різниця між загальною сумою і сумою платежів
        return new Money($this->getTotalPrice()->getAmount() - $this->invoice->payments()->sum('amount'));
    }

    // Метод для отримання об'єкту рахунку
    public function getInvoice()
    {
        return $this->invoice;
    }

    // Метод для отримання об'єкту податкового розрахунку
    public function getTax()
    {
        return $this->tax;
    }
}
