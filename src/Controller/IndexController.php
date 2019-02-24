<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Form\ReservationFormType;
use App\Form\RoomFormType;
use App\Form\SearchFormType;
use App\Repository\EmployeeRepository;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
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
     * @Route("/rooms", name="rooms")
     * @param RoomRepository $roomRepository
     * @return Response
     */
    public function room(RoomRepository $roomRepository)
    {


        $room = $roomRepository->findAll();
        return $this->render('home/reservation.html.twig', [
            'rooms' => $room
        ]);
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
            /** @var Room $room */
            $room = $form->getData();
            $entityManager->flush();

            return $this->redirectToRoute('rooms');
        }


        return $this->render('home/edit_room.html.twig', [
            'form' => $form->createView()
        ]);

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
        $reservation = $reservationRepository->findAll();
        $reservation = count($reservation);

        if ($reservation === 0) {
            $room = $roomRepository->findOneBy([
                'id' => $id
            ]);
            $entityManager->remove($room);
            $entityManager->flush();
        }




        return $this->redirectToRoute('rooms');
    }



}