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
use Dompdf\Dompdf;
use Dompdf\Options;

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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function invoicePayment(Room $room, EntityManagerInterface $entityManager)
    {
        $session = new Session();
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');

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
        $trans->setMethod('Invoice');
        $trans->onPrePersistChosenAt();
        $entityManager->persist($trans);
        $entityManager->flush();

        self:$this->createPdf($room, $trans, $entityManager);
        $this->addFlash('success', 'Uspiješno ste platili poduzećem');
        return $this->redirectToRoute('rooms');
    }

    /**
     * @param Room $room
     * @param Transaction $transaction
     * @param EntityManagerInterface $entityManager
     */
    public function createPdf(Room $room, Transaction $transaction, EntityManagerInterface $entityManager)
    {
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in twig file
        $html = $this->renderView('invoice/pdf.html.twig', [
            'title' => "Welcome to our PDF Test",
            'room' => $room,
            'invoice' => $transaction,
            'user' => $this->getUser()
        ]);

        $pdfName = date('Y') . $transaction->getId() . $this->getUser()->getId() . '.pdf';
        var_dump($this->getUser()->getId());
        $transaction->setTransactionId($pdfName);
        $entityManager->persist($transaction);
        $entityManager->flush();

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        $output = $dompdf->output();

        $publicDirectory = '../public/uploads/invoice';

        $pdfFilePath = $publicDirectory . $pdfName;

        file_put_contents($pdfFilePath, $output);
    }
}
