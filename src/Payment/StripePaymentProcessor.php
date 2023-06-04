<?php

namespace App\Payment;

class StripePaymentProcessor implements PaymentProcessorInterface
{

    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }


    /**
     * @throws Exception in case of a failed payment
     */
    public function pay(int $price): void
    {
        return;
    }

    /**
     * @return bool true if payment was succeeded, false otherwise
     */
    public function processPayment(int $price): bool
    {
        if ($price < 10) {
            return false;
        }

        //process payment logic
        return true;
    }
}