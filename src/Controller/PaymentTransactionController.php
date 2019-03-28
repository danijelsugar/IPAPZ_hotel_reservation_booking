<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Dompdf\Dompdf;
use Dompdf\Options;

class PaymentTransactionController extends AbstractController
{

    /**
     * @Symfony\Component\Routing\Annotation\Route("/transaction/pay/{id}", name="paypal-pay")
     * @param Room $room
     * @param ReservationRepository $reservationRepository
     * @param PaymentMethodRepository $paymentMethodRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function payPalShow(
        Room $room,
        ReservationRepository $reservationRepository,
        PaymentMethodRepository $paymentMethodRepository
    ) {

        $method = $paymentMethodRepository->findOneBy(
            [
                'method' => 'Paypal'
            ]
        );
        $status = $method->getEnabled();

        if (!$status) {
            return $this->redirectToRoute(
                'rooms',
                [
                    'message' => 'Plaćanje paypalom je onemogućeno'
                ]
            );
        }

        $session = new Session();
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');
        $res = $reservationRepository->reservationNum($dateFrom, $dateTo, $room);
        if ($res === 0) {
            $gateway = self::gateway();
            $totalDays = $session->get('dateto')->diff($session->get('datefrom'));
            $totalCost = $room->getCost() * $totalDays->days;

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

        /**
         * @var \App\Entity\Reservation $reservation
         */
        $reservation = new Reservation();
        $reservation->setRoom($room);
        $reservation->setUser($this->getUser());
        $reservation->setDatefrom($dateFrom);
        $reservation->setDateto($dateTo);
        $reservation->setPaymentMethod('Paypal');
        $entityManager->persist($reservation);

        /**
         * @var \App\Entity\Transaction $trans
         */
        $trans = new Transaction();
        $trans->setUser($this->getUser());
        $trans->setRoom($room);
        $trans->setTransactionId($transaction->id);
        $trans->setReservation($reservation);
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

    /**
     * @Symfony\Component\Routing\Annotation\Route("/transaction/invoice-payment/{id}", name="invoice-payment")
     * @param Room $room
     * @param EntityManagerInterface $entityManager
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param ReservationRepository $reservationRepository
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function invoicePayment(
        Room $room,
        EntityManagerInterface $entityManager,
        PaymentMethodRepository $paymentMethodRepository,
        ReservationRepository $reservationRepository
    ) {

        $method = $paymentMethodRepository->findOneBy(
            [
                'method' => 'Poduzeće'
            ]
        );
        $status = $method->getEnabled();

        if (!$status) {
            return $this->redirectToRoute(
                'rooms',
                [
                    'message' => 'Plaćanje poduzećem je onemogućeno'
                ]
            );
        }

        $session = new Session();
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');

        $res = $reservationRepository->reservationNum($dateFrom, $dateTo, $room);
        if ($res === 0) {
            $totalDays = $dateTo->diff($dateFrom);
            $costPerNight = $room->getCost();
            $amount = $costPerNight * $totalDays->days;

            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setUser($this->getUser());
            $reservation->setDatefrom($dateFrom);
            $reservation->setDateto($dateTo);
            $reservation->setPaymentMethod('Invoice');
            $entityManager->persist($reservation);

            $trans = new Transaction();
            $trans->setUser($this->getUser());
            $trans->setRoom($room);
            $trans->setReservation($reservation);
            $trans->setMethod('Invoice');
            $trans->onPrePersistChosenAt();
            $entityManager->persist($trans);
            $entityManager->flush();

            self:$this->createPdf($room, $reservation, $entityManager, $trans, $amount);
            $this->addFlash('success', 'Uspiješno ste platili poduzećem');
            return $this->redirectToRoute('rooms');
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
     * @param Room $room
     * @param Reservation $reservation
     * @param EntityManagerInterface $entityManager
     * @param Transaction $transaction
     * @param $amount
     */
    public function createPdf(
        Room $room,
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        Transaction $transaction,
        $amount
    ) {
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in twig file
        $html = $this->renderView(
            'invoice/pdf.html.twig',
            [
                'title' => "Welcome to our PDF Test",
                'room' => $room,
                'reservation' => $reservation,
                'user' => $this->getUser(),
                'amount' => $amount
            ]
        );
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $pdfName = date('Y') . $reservation->getId() . $this->getUser()->getId() . '.pdf';

        $transaction->setTransactionId($pdfName);
        $transaction->setFileName($pdfName);
        $entityManager->persist($transaction);
        $entityManager->flush();

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        $output = $dompdf->output();

        $publicDirectory = '../public/uploads/invoice/';

        $pdfFilePath = $publicDirectory . $pdfName;

        file_put_contents($pdfFilePath, $output);
    }
}
