<?php


namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Form\ReservationFormType;
use App\Form\RoomFormType;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param ReservationRepository $reservationRepository
     * @param RoomRepository $roomRepository
     * @return Response
     */
    public function index(ReservationRepository $reservationRepository, RoomRepository $roomRepository)
    {

        $room = $roomRepository->findAll();
        $reservation = $reservationRepository->findAll();

        return $this->render('home/index.html.twig', [
            'reservations' => $reservation,
            'rooms' => $room
        ]);
    }

    /**
     * @Route("/booking/{room}", defaults={"room"= null}, name="booking")
     * @param Request $request
     * @return Response
     * @throws \Exception
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

        return $this->render('home/booking.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/rooms", name="rooms")
     * @param RoomRepository $roomRepository
     * @return Response
     */
    public function room(RoomRepository $roomRepository)
    {

        $session = new Session();
        if (isset($_GET['message'])) {
            $message = $_GET['message'];
        } else {
            $message = '';
        }
        $room = $roomRepository->findBy([
            'capacity' => $session->get('people')
        ]);
        return $this->render('home/reservation.html.twig', [
            'rooms' => $room,
            'message' => $message
        ]);
    }

    /**
     * @Route("finish_reservation/{room}", name="finish_reservation")
     * @param RoomRepository $roomRepository
     * @param EntityManagerInterface $entityManager
     * @param ReservationRepository $reservationRepository
     * @param $room
     * @return Response
     */
    public function finishReservation(RoomRepository $roomRepository, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository, $room)
    {

        $session = new Session();
        $user = $this->getUser();
        $dateFrom = $session->get('datefrom');
        $dateTo = $session->get('dateto');
        $room = $roomRepository->findOneBy([
            'id' => $room
        ]);
        $res = $reservationRepository->reservationNum($dateFrom,$dateTo,$room);
        if ($res === 0) {
            /** @var Reservation $reservation */
            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setUser($user);
            $reservation->setDatefrom($dateFrom);
            $reservation->setDateto($dateTo);
            $entityManager->persist($reservation);
            $this->addFlash('success', 'Soba rezervirana');
            $entityManager->flush();
        } else {
            return $this->redirectToRoute('rooms', [
                'message' => 'Soba nije dostupna u tome terminu molimo vas odaberite drugi termin'
            ]);
        }



        return $this->redirectToRoute('home');
    }

    /**
     * @Route("room_reservations", name="room_reservations")
     * @param ReservationRepository $reservationRepository
     * @param Request $request
     * @return Response
     */
    public function roomReservations(ReservationRepository $reservationRepository, Request $request)
    {
        $roomId = $request->request->get('id');
        $reservations = $reservationRepository->findAllArray($roomId);
        return new JsonResponse($reservations);
    }

    /**
     * @Route("/admin/edit-room/{id}", name="admin/edit-room")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param RoomRepository $roomRepository
     * @param $id
     * @return Response
     */
    public function editRoom(Request $request, EntityManagerInterface $entityManager, RoomRepository $roomRepository, $id)
    {

        $room = $roomRepository->findOneBy([
            'id' => $id
        ]);

        $form = $this->createForm(RoomFormType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Room $room
             */
            $room = $form->getData();
            $file = $room->getImage();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
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


        return $this->render('home/edit_room.html.twig', [
            'form' => $form->createView()
        ]);

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
     * @Route("/admin/delete-room/{id}", name="admin/delete-room")
     * @param EntityManagerInterface $entityManager
     * @param RoomRepository $roomRepository
     * @param ReservationRepository $reservationRepository
     * @param $id
     * @return Response
     */
    public function deleteRoom(EntityManagerInterface $entityManager, RoomRepository $roomRepository,
    ReservationRepository $reservationRepository, $id)
    {
        $reservations = $reservationRepository->countReservations($id);

        if ($reservations == 0) {
            $room = $roomRepository->findOneBy([
                'id' => $id
            ]);
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('rooms');
    }
}