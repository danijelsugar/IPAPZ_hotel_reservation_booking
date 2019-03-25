<?php


namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Form\ReviewFromType;
use App\Form\RoomFormType;
use App\Repository\ReservationRepository;
use App\Repository\ReviewRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class IndexController extends AbstractController
{
    /**
     * @Symfony\Component\Routing\Annotation\Route("/", name="home")
     * @param      ReservationRepository $reservationRepository
     * @param      RoomRepository $roomRepository
     * @return     \Symfony\Component\HttpFoundation\Response
     */
    public function index(ReservationRepository $reservationRepository, RoomRepository $roomRepository)
    {

        $room = $roomRepository->findAll();
        $reservation = $reservationRepository->findAll();

        return $this->render(
            'home/index.html.twig',
            [
                'reservations' => $reservation,
                'rooms' => $room
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
     * @Symfony\Component\Routing\Annotation\Route("/rooms", name="rooms")
     * @param           RoomRepository $roomRepository
     * @param           ReservationRepository $reservationRepository
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function room(RoomRepository $roomRepository, ReservationRepository $reservationRepository)
    {

        $session = new Session();
        if (isset($_GET['message'])) {
            $message = $_GET['message'];
        } else {
            $message = '';
        }

        $room = $roomRepository->findBy(
            [
                'capacity' => $session->get('people')
            ]
        );
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');
        $roomsArray = [];
        foreach ($room as $r) {
            $roomId = $r->getId();
            $reservation = $reservationRepository->reservationNum($dateFrom, $dateTo, $roomId);
            if ($reservation === 0) {
                $roomsArray[] = $roomId;
            }
        }

        if (empty($roomsArray)) {
            $message = 'Nema dostupnih soba u tome terminu. Na kalendaru možete viditi kada je pojedina soba dostupna';
            $dateFromMinus =  clone $dateFrom;
            $dateFromMinus = $dateFromMinus->modify('-5 days');
            $dateFromPlus = clone $dateFrom;
            $dateFromPlus = $dateFromPlus->modify('+5 days');
            $dateToMinus = clone $dateTo;
            $dateToMinus = $dateToMinus->modify('-5 days');
            $dateToPlus = clone $dateTo;
            $dateToPlus = $dateToPlus->modify('+5 days');
            foreach ($room as $r) {
                $roomId = $r->getId();
                $reservation = $reservationRepository->getClosest(
                    $dateFromMinus,
                    $dateFromPlus,
                    $dateToMinus,
                    $dateToPlus,
                    $roomId
                );
                if ($reservation != 0) {
                    $roomsArray[] = $roomId;
                }
            }

            $rooms = $roomRepository->findBy(
                [
                    'id' => $roomsArray
                ]
            );
        } else {
            $rooms = $roomRepository->findBy(
                [
                    'id' => $roomsArray
                ]
            );
        }

        return $this->render(
            'home/reservation.html.twig',
            [
                'rooms' => $rooms,
                'message' => $message,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("finish_reservation/{room}", name="finish_reservation")
     * @param                              RoomRepository $roomRepository
     * @param                              EntityManagerInterface $entityManager
     * @param                              ReservationRepository $reservationRepository
     * @param                              $room
     * @return                             \Symfony\Component\HttpFoundation\Response
     */
    public function finishReservation(
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        $room
    ) {

        $session = new Session();
        $user = $this->getUser();
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');
        $room = $roomRepository->findOneBy(
            [
                'id' => $room
            ]
        );
        $res = $reservationRepository->reservationNum($dateFrom, $dateTo, $room);
        if ($res === 0) {
            /**
             * @var \App\Entity\Reservation $reservation
             */
            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setUser($user);
            $reservation->setDatefrom($dateFrom);
            $reservation->setDateto($dateTo);
            $entityManager->persist($reservation);
            $this->addFlash('success', 'Soba rezervirana');
            $entityManager->flush();
        } else {
            return $this->redirectToRoute(
                'rooms',
                [
                    'message' => 'Soba nije dostupna u tome terminu molimo vas 
                    odaberite drugi termin. U kalendaru možete pogledati dostupne termine'
                ]
            );
        }


        return $this->redirectToRoute('home');
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

        if (isset($_GET['message'])) {
            $message = $_GET['message'];
        } else {
            $message = '';
        }

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
            } else {
                return $this->redirectToRoute(
                    'edit-reservation',
                    [
                        'id' => $id,
                        'message' => 'Soba nije dostupna u tome terminu molimo vas odaberite drugi termin'
                    ]
                );
            }
        }

        return $this->render(
            'home/edit_reservation.html.twig',
            [
                'form' => $form->createView(),
                'message' => $message
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
     * @Symfony\Component\Routing\Annotation\Route("/admin/edit-room/{id}", name="admin/edit-room")
     * @param                          Request $request
     * @param                          EntityManagerInterface $entityManager
     * @param                          RoomRepository $roomRepository
     * @param                          $id
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function editRoom(
        Request $request,
        EntityManagerInterface $entityManager,
        RoomRepository $roomRepository,
        $id
    ) {

        $room = $roomRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $form = $this->createForm(RoomFormType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Entity\Room $room
             */
            $room = $form->getData();
            $file = $room->getImage();
            $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('image_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $room->setImage($fileName);

            $entityManager->flush();

            return $this->redirectToRoute('rooms');
        }


        return $this->render(
            'home/edit_room.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/delete-room/{id}", name="admin/delete-room")
     * @param                            EntityManagerInterface $entityManager
     * @param                            RoomRepository $roomRepository
     * @param                            ReservationRepository $reservationRepository
     * @param                            $id
     * @return                           \Symfony\Component\HttpFoundation\Response
     */
    public function deleteRoom(
        EntityManagerInterface $entityManager,
        RoomRepository $roomRepository,
        ReservationRepository $reservationRepository,
        $id
    ) {
        $reservations = $reservationRepository->countReservations($id);

        if ($reservations == 0) {
            $room = $roomRepository->findOneBy(
                [
                    'id' => $id
                ]
            );
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('rooms');
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
     * @Symfony\Component\Routing\Annotation\Route("leave-review/{id}", name="leave-review")
     * @param                      Request $request
     * @param                      EntityManagerInterface $entityManager
     * @param                      $id
     * @param                      ReservationRepository $reservationRepository
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function leaveReview(
        Request $request,
        EntityManagerInterface $entityManager,
        $id,
        ReservationRepository $reservationRepository
    ) {

        $form = $this->createForm(ReviewFromType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $room = $reservationRepository->find($id);
            $room = $room->getRoom();
            /**
             * @var \App\Entity\Review $review
             */
            $user = $this->getUser();
            $review = $form->getData();
            $review->setUser($user);
            $review->setRoom($room);
            try {
                $entityManager->persist($review);
                $this->addFlash('success', 'Recenzija uspiješno kreirana');
                $entityManager->flush();
            } catch (ORMException | ORMInvalidArgumentException $exception) {
                $this->addFlash('success', 'Something went wrong');
            }


            return $this->redirectToRoute('user-reservations');
        }


        return $this->render(
            'home/new_review.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("room-reviews/{room}", name="room-reviews")
     * @param                        ReviewRepository $reviewRepository
     * @param                        $room
     * @return                       \Symfony\Component\HttpFoundation\Response
     */
    public function roomReviews(ReviewRepository $reviewRepository, $room)
    {

        $reviews = $reviewRepository->findBy(
            [
                'room' => $room
            ]
        );

        return $this->render(
            'home/room_reviews.html.twig',
            [
                'reviews' => $reviews
            ]
        );
    }
}
