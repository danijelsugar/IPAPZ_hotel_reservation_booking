<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\OrderByFormType;
use App\Form\ReservationFormType;
use App\Repository\ReservationRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ReservationController extends AbstractController
{

    /**
     * @Symfony\Component\Routing\Annotation\Route("/", name="home")
     * @param      ReservationRepository $reservationRepository
     * @return     \Symfony\Component\HttpFoundation\Response
     */
    public function index(ReservationRepository $reservationRepository)
    {

        $reservation = $reservationRepository->findAll();

        return $this->render(
            'home/index.html.twig',
            [
                'reservations' => $reservation
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/booking/{room}", defaults={"room"= null}, name="booking")
     * @param                    Request $request
     * @return                   \Symfony\Component\HttpFoundation\Response
     * @throws                   \Exception
     */
    public function booking(Request $request)
    {

        $session = new Session();

        $booking = new Reservation();
        $booking->setDatefrom(new \DateTime());
        $booking->setDateto(new \DateTime());
        $form = $this->createForm(ReservationFormType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $choice = $form->get('personNum')->getData();
            $dateFrom = $form->get('datefrom')->getData();
            $dateTo = $form->get('dateto')->getData();
            $session->set('people', $choice);
            $session->set('datefrom', $dateFrom);
            $session->set('dateto', $dateTo);
            if ($session->get('dateto') <= $session->get('datefrom')) {
                $this->addFlash('warning', 'Završni datum mora biti veći od poćetnog');
                return $this->redirectToRoute('booking');
            }

            return $this->redirectToRoute('rooms');
        }

        return $this->render(
            'home/booking.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("edit-reservation/{id}", name="edit-reservation")
     * @param                          Request $request
     * @param                          EntityManagerInterface $entityManager
     * @param                          ReservationRepository $reservationRepository
     * @param                          $id
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function editReservation(
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        $id
    ) {

        $reservation = $reservationRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $form = $this->createForm(ReservationFormType::class, $reservation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Entity\Reservation $reservation
             */
            $reservation = $form->getData();
            $dateFrom = $reservation->getDatefrom();
            $dateTo = $reservation->getDateto();
            $room = $reservation->getRoom();
            $res = $reservationRepository->editReservationNum($dateFrom, $dateTo, $room, $id);

            if ($res === 0) {
                $this->addFlash('success', 'Rezervacija promijenjena');
                $entityManager->flush();
                return $this->redirectToRoute(
                    'user-reservations'
                );
            } else {
                $this->addFlash('warning', 'Soba nije dostupna u tome terminu molimo vas odaberite drugi termin');
                return $this->redirectToRoute(
                    'user-reservations'
                );
            }
        }

        return $this->render(
            'home/edit_reservation.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("room_reservations", name="room_reservations")
     * @param                      ReservationRepository $reservationRepository
     * @param                      Request $request
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function roomReservations(ReservationRepository $reservationRepository, Request $request)
    {
        $roomId = $request->request->get('id');
        $reservations = $reservationRepository->findAllArray($roomId);
        return new JsonResponse($reservations);
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("user-reservations", name="user-reservations")
     * @param                      ReservationRepository $reservationRepository
     * @return                     \Symfony\Component\HttpFoundation\Response
     * @throws                     \Exception
     */
    public function userReservations(ReservationRepository $reservationRepository)
    {

        $user = $user = $this->getUser();
        $current = new \DateTime('today');

        $reservation = $reservationRepository->findBy(
            [
                'user' => $user
            ]
        );
        return $this->render(
            'home/user_reservations.html.twig',
            [
                'reservations' => $reservation,
                'current' => $current
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/reservations", name="admin/reservations")
     * @param                        ReservationRepository $reservationRepository
     * @param                        Request $request
     * @return                       \Symfony\Component\HttpFoundation\Response
     */
    public function reservations(ReservationRepository $reservationRepository, Request $request)
    {
        $orderForm = $this->createForm(OrderByFormType::class);
        $orderForm->handleRequest($request);

        if ($orderForm->isSubmitted() && $orderForm->isValid()) {
            $choice = $orderForm->getData();
            switch ($choice['orderby']) {
                case 1:
                    $condition = 'r.datefrom';
                    break;
                case 2:
                    $condition = 'u.email';
                    break;
                case 3:
                    $condition = 'c.name';
                    break;
            }
        } else {
            $condition = 'r.datefrom';
        }

        $reservation = $reservationRepository->orderReservations($condition);

        return $this->render(
            'admin/pending.html.twig',
            [
                'reservations' => $reservation,
                'orderForm' => $orderForm->createView()
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/accepted", name="admin/accepted")
     * @param                    ReservationRepository $reservationRepository
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function acceptedReservations(ReservationRepository $reservationRepository)
    {


        $reservation = $reservationRepository->findAll();

        return $this->render(
            'admin/accepted.html.twig',
            [
                'reservations' => $reservation,
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/declined", name="admin/declined")
     * @param                    ReservationRepository $reservationRepository
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function declinedReservations(ReservationRepository $reservationRepository)
    {
        $reservation = $reservationRepository->findBy(
            [
                'declined' => 1
            ]
        );

        return $this->render(
            'admin/declined.html.twig',
            [
                'reservations' => $reservation
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/accept/{id}/{roomid}", name="admin/accept")
     * @param                                EntityManagerInterface $entityManager
     * @param                                ReservationRepository $reservationRepository
     * @param                                $id
     * @param                                $roomid
     * @return                               \Symfony\Component\HttpFoundation\Response
     */
    public function acceptReservation(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        $id,
        $roomid
    ) {
        $reservation = $reservationRepository->findOneBy(
            [
                'id' => $id
            ]
        );
        $dateFrom = $reservation->getDateFrom();
        $dateTo = $reservation->getDateTo();

        $reservationNum = $reservationRepository->reservationNum($dateFrom, $dateTo, $roomid);
        if ($reservationNum !== 0) {
            $this->addFlash('success', 'Rezervacija ne može biti prihvaćena termin je zauzet');
            return $this->redirectToRoute(
                'admin/reservations'
            );
        }

        /**
         * @var \App\Entity\Reservation $reservation
         */
        $reservation->setStatus(1);
        $reservation->setDeclined(0);
        $this->addFlash('success', 'Rezervacija prihvaćena');
        $entityManager->flush();


        return $this->redirectToRoute(
            'admin/reservations'
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/cancel/{id}", name="admin/cancel")
     * @param                                EntityManagerInterface $entityManager
     * @param                                ReservationRepository $reservationRepository
     * @param                                $id
     * @return                               \Symfony\Component\HttpFoundation\Response
     */
    public function cancelReservation(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        $id
    ) {


        /**
         * Deleting reservation with given id
         */
        $reservation = $reservationRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        /**
         * @var \App\Entity\Reservation $reservation
         */
        $reservation->setStatus(0);
        $reservation->setDeclined(1);
        $this->addFlash('success', 'Rezervacija otkazana');
        $entityManager->flush();

        return $this->redirectToRoute(
            'admin/accepted',
            [
                'reservations' => $reservation
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/decline/{id}", name="admin/decline")
     * @param                                 EntityManagerInterface $entityManager
     * @param                                 ReservationRepository $reservationRepository
     * @param                                 $id
     * @return                                \Symfony\Component\HttpFoundation\Response
     */
    public function declinelReservation(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        $id
    ) {

        $reservation = $reservationRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        /**
         * @var \App\Entity\Reservation $reservation
         */
        $reservation->setDeclined(1);
        $this->addFlash('success', 'Reservation declined');
        $entityManager->flush();

        return $this->redirectToRoute(
            'admin/reservations',
            [
                'reservations' => $reservation
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/download/{reservation}", name="admin/pdf-download")
     * @param $reservation
     * @param TransactionRepository $transactionRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadPdf($reservation, TransactionRepository $transactionRepository)
    {
        /**
         * @var \App\Entity\Transaction $transaction
         */
        $transaction = $transactionRepository->findOneBy(
            [
                'reservation' => $reservation
            ]
        );

        $fileName = $transaction->getFileName();
        $filePath = $this->getParameter('kernel.project_dir'). '/public/uploads/invoice/' . $fileName;

        $response = new Response();
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set(
            'Content-Disposotopn',
            'attachment; filename="%s"',
            $fileName
        );
        $response->setContent(file_get_contents($filePath));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
