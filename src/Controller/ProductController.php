<?php

namespace App\Controller;

use App\Form\CalculatePriceType;
use App\Form\PurchaseProductType;
use App\Payment\PaymentProcessorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;

class ProductController extends AbstractController
{
    private PaymentProcessorInterface $paymentProcessor;
    private $formFactory;
    private $formType;

    public function __construct(PaymentProcessorInterface $paymentProcessor, FormFactoryInterface $formFactory, CalculatePriceType $formType)
    {
        $this->paymentProcessor = $paymentProcessor;
	    $this->formFactory = $formFactory;
	    $this->formType = $formType;
    }

    /**
     * @Route("/calculate-price", name="calculate_price", methods={"GET"})
     */
    public function calculatePrice(Request $request): Response
    {
        $form = $this->createForm(CalculatePriceType::class);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form), Response::HTTP_BAD_REQUEST);
        }

        $data = $form->getData();
        $price = $this->calculateProductPrice($data['product'], $data['taxNumber'], $data['couponCode']);

        return $this->json(['price' => $price], Response::HTTP_OK);
    }

    /**
     * @Route("/purchase-product", name="purchase_product", methods={"POST"})
     */
    public function purchaseProduct(Request $request): Response
    {
        $form = $this->createForm(PurchaseProductType::class);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form), Response::HTTP_BAD_REQUEST);
        }

        $data = $form->getData();
        $price = $this->calculateProductPrice($data['product'], $data['taxNumber'], $data['couponCode']);

        try {
            $success = false;
	        switch ($data['paymentProcessor']) {
                case PurchaseProductType::PayPalPP:
                    $this->paymentProcessor->pay($price);
                    break;
                case PurchaseProductType::StripePP:
                    $success = $this->paymentProcessor->processPayment($price);
                    break;

                if (!$success) {
                    throw new \Exception('Платеж не прошел', -1);
                }
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['Платеж успешен'], Response::HTTP_OK);
    }

    private function calculateProductPrice(int $product, string $taxNumber, ?string $couponCode): float
    {
        $productPrice = 0;

        if ($product === CalculatePriceType::PRODUCT_IPHONE) {
            $productPrice = 100;
        } elseif ($product === CalculatePriceType::PRODUCT_NAUSHNIKI) {
            $productPrice = 20;
        } elseif ($product === CalculatePriceType::PRODUCT_CHEHOL) {
            $productPrice = 10;
        }

        $taxRate = 0;
        if (strpos($taxNumber, 'DE') === 0) {
            $taxRate = 19;
        } elseif (strpos($taxNumber, 'IT') === 0) {
            $taxRate = 22;
        } elseif (strpos($taxNumber, 'GR') === 0) {
            $taxRate = 24;
        } elseif (strpos($taxNumber, 'FR') === 0) {
            $taxRate = 20;
        }

        $price = $productPrice + ($productPrice * ($taxRate / 100));

        if ($couponCode) {
            $couponDiscount = 0;

            if ($couponCode === 'D15') {
                $couponDiscount = 15;
            } elseif ($couponCode === 'P10') {
                $couponDiscount = $price * 0.1;
            }

            $price -= $couponDiscount;
        }

        return round($price, 2);
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }
}
