<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentTransactionController extends AbstractController
{

    /**
     * @Symfony\Component\Routing\Annotation\Route("/transaction/pay{id}", name="pay")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paypalShow()
    {
        $gateway = self::gateway();

        return $this->render(
            'paypal/paypal.html.twig',
            [
              'gateway' => $gateway
            ]
        );
    }

    public function gateway()
    {
        return $gateway = new \Braintree_Gateway([
            'environment' => 'sandbox',
            'merchantId' => 'xt36rmt86bjgbfwq',
            'publicKey' => '4k3nth4tkc6xt62z',
            'privateKey' => '48c864d7b9ba1c118ace84f0ca3d6c84'
        ]);
    }
}
