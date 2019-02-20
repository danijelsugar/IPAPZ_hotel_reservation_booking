<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Form\ReservationFormType;
use App\Form\RoomFormType;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param RoomRepository $roomRepository
     * @return Response
     */
    public function index(RoomRepository $roomRepository)
    {



        return $this->render('home/index.html.twig');
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
     */
    public function book(Room $room, Request $request, EntityManagerInterface $entityManager)
    {

        $form = $this->createForm(ReservationFormType::class);
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
     * @Route("edit-room/{id}", name="edit-room")
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

            return $this->redirectToRoute('reservations');
        }


        return $this->render('home/edit_room.html.twig', [
            'form' => $form->createView()
        ]);

    }

}