<?php

namespace App\Payment;

interface PaymentProcessorInterface
{
    public function pay(int $price): void;
    public function processPayment(int $price): bool;
}