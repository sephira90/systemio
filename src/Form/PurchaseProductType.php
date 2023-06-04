<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PurchaseProductType extends AbstractType
{
    const PayPalPP = 'paypal';
    const StripePP = 'stripe';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Значение product должно быть целым числом'
                    ])
                ],
            ])
            ->add('taxNumber', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^(DE|IT|GR|FR\d{2})\d{9}$/',
                        'message' => 'Некорректный формат налогового номера',
                    ]),
                ],
            ])
            ->add('couponCode', TextType::class, [
                'required' => false,
            ])
            ->add('paymentProcessor', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
