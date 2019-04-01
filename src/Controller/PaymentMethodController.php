<?php

namespace App\Controller;

use App\Repository\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentMethodController extends AbstractController
{
    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/payment-methods", name="admin/payment-methods")
     * @param PaymentMethodRepository $paymentMethodRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentMethods(PaymentMethodRepository $paymentMethodRepository)
    {

        $methods = $paymentMethodRepository->findAll();

        return $this->render(
            'admin/payment_methods.html.twig',
            [
                'methods' => $methods
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/disable-payment-method/{id}", name="admin/disable-payment-method")
     * @param $id
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disablePaymentMethod(
        $id,
        PaymentMethodRepository $paymentMethodRepository,
        EntityManagerInterface $entityManager
    ) {
        $method = $paymentMethodRepository->findOneBy(
            [
                'id' => $id
            ]
        );
        $method->setEnabled(0);
        $entityManager->flush();

        return $this->redirectToRoute(
            'admin/payment-methods'
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/enable-payment-method/{id}", name="admin/enable-payment-method")
     * @param $id
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enablePaymentMethod(
        $id,
        PaymentMethodRepository $paymentMethodRepository,
        EntityManagerInterface $entityManager
    ) {
        $method = $paymentMethodRepository->findOneBy(
            [
                'id' => $id
            ]
        );
        $method->setEnabled(1);
        $entityManager->flush();

        return $this->redirectToRoute(
            'admin/payment-methods'
        );
    }
}
