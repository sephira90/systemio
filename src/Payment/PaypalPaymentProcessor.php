<?php

namespace App\Payment;

class PaypalPaymentProcessor implements PaymentProcessorInterface
{
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @throws \Exception in case of a failed payment
     */
    public function pay(int $price): void
    {
        if ($price > 100) {
            throw new \Exception('Too high price');
        }

        //process payment logic
    }

    /**
     * @return bool true if payment was succeeded, false otherwise
     */
    public function processPayment(int $price): bool
    {
        //process payment logic
        return false;
    }
}