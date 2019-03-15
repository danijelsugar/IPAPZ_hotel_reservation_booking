<?php


namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Form\ReservationFormType;
use App\Form\RoomFormType;
use App\Form\SearchFormType;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param ReservationRepository $reservationRepository
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
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param $room
     * @return Response
     * @throws \Exception
     */
    public function booking(EntityManagerInterface $entityManager, Request $request, $room)
    {

        $user = $this->getUser();
        $booking = new Reservation();
        $booking->setDatefrom(new \DateTime());
        $booking->setDateto(new \DateTime());
        $form = $this->createForm(ReservationFormType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Reservation $reservation */
            $choice = $form->get('personNum')->getData();
            $reservation = $form->getData();
            $reservation->setUser($user);
            $entityManager->persist($reservation);
            $entityManager->flush();
            $resId = $reservation->getId();
            return $this->redirectToRoute('rooms',[
                'choice' => $choice,
                'reservation' => $resId
            ]);

        }

        return $this->render('home/booking.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/rooms/{choice}/{reservation}", name="rooms")
     * @param RoomRepository $roomRepository
     * @param $choice
     * @param $reservation
     * @return Response
     */
    public function room(RoomRepository $roomRepository, $choice, $reservation)
    {

        $resId = $reservation;
        $room = $roomRepository->findBy([
            'capacity' => $choice
        ]);
        return $this->render('home/reservation.html.twig', [
            'rooms' => $room,
            'reservation' => $resId
        ]);
    }

    /**
     * @Route("finish_reservation/{room}/{reservation}", name="finish_reservation")
     * @param ReservationRepository $reservationRepository
     * @param EntityManagerInterface $entityManager
     * @param $room
     * @param $reservation
     * @return Response
     */
    public function finishReservation(ReservationRepository $reservationRepository, RoomRepository $roomRepository, EntityManagerInterface $entityManager, $room, $reservation)
    {
        $res = $reservationRepository->findOneBy([
           'id' => $reservation
        ]);

        $room = $roomRepository->findOneBy([
            'id' => $room
        ]);
        $res->setRoom($room);
        $this->addFlash('success', 'Soba rezervirana');
        $entityManager->flush();


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
     * @Route("/book/{id}", name="book")
     * @param Room $room
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Exception
     */
    public function book(Room $room, Request $request, EntityManagerInterface $entityManager )
    {

        $reserved = new Reservation();
        $reserved->setDatefrom(new \DateTime());
        $reserved->setDateto(new \DateTime());
        $form = $this->createForm(ReservationFormType::class, $reserved);
        $form->handleRequest($request);

          if ($form->isSubmitted() && $form->isValid()) {
              /** @var Reservation $reservation */
              $reservation = $form->getData();
              $reservation->setRoom($room);
              $entityManager->persist($reservation);
              $this->addFlash('success', 'Soba rezervirana');
              $entityManager->flush();

              return $this->redirectToRoute('rooms');
          }


        return $this->render('home/book.html.twig', [
            'form' => $form->createView(),
            'room' => $room
        ]);
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