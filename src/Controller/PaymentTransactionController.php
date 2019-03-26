<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\Transaction;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class PaymentTransactionController extends AbstractController
{

    /**
     * @Symfony\Component\Routing\Annotation\Route("/transaction/pay/{id}", name="paypal-pay")
     * @param Room $room
     * @param RoomRepository $roomRepository
     * @param ReservationRepository $reservationRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function payPalShow(Room $room, RoomRepository $roomRepository, ReservationRepository $reservationRepository)
    {

        $session = new Session();
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');
        $r = $roomRepository->findOneBy(
            [
                'id' => $room
            ]
        );
        $res = $reservationRepository->reservationNum($dateFrom, $dateTo, $r);
        if ($res === 0) {
            $gateway = self::gateway();
            $totalDays = $session->get('dateto')->diff($session->get('datefrom'));
            $totalCost = $room->getCost() * $totalDays->days;
            var_dump($totalCost);

            return $this->render(
                'paypal/paypal.html.twig',
                [
                    'gateway' => $gateway,
                    'room' => $room,
                    'totalCost' => $totalCost
                ]
            );
        } else {
            return $this->redirectToRoute(
                'rooms',
                [
                    'message' => 'Termin nije dostupan. Promijenite termin'
                ]
            );
        }
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/transaction/paypal-payment/{id}", name="paypal-payment")
     * @param Room $room
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function payment(
        Room $room,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $session = new Session();
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');


        $gateway = self::gateway();
        $totalDays = $dateTo->diff($dateFrom);
        $amount = $room->getCost() * $totalDays->days;
        $nonce = $request->get('payment_method_nonce');
        $result = $gateway->transaction()->sale(
            [
                'amount' => $amount,
                'paymentMethodNonce' => $nonce
            ]
        );
        $transaction = $result->transaction;
        if ($transaction == null) {
            $this->addFlash('warning', 'Payment nije prošao');
            return $this->redirectToRoute('rooms');
        }

        $user = $this->getUser();
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');

        /**
         * @var \App\Entity\Reservation $reservation
         */
        $reservation = new Reservation();
        $reservation->setRoom($room);
        $reservation->setUser($user);
        $reservation->setDatefrom($dateFrom);
        $reservation->setDateto($dateTo);
        $entityManager->persist($reservation);

        /**
         * @var \App\Entity\Transaction $trans
         */
        $trans = new Transaction();
        $trans->setUser($reservation->getUser());
        $trans->setRoom($reservation->getRoom());
        $trans->setTransactionId($transaction->id);
        $trans->setMethod('Paypal');
        $trans->onPrePersistChosenAt();
        $trans->onPrePersistPaidAt();
        $entityManager->persist($trans);

        $entityManager->flush();


        $this->addFlash('success', 'Uspiješno ste platili');
        return $this->redirectToRoute('rooms');
    }

    public function gateway()
    {
        $gateway = new \Braintree_Gateway(
            [
                'environment' => 'sandbox',
                'merchantId' => 'xt36rmt86bjgbfwq',
                'publicKey' => '4k3nth4tkc6xt62z',
                'privateKey' => '48c864d7b9ba1c118ace84f0ca3d6c84'
            ]
        );
        return $gateway;
    }
}
