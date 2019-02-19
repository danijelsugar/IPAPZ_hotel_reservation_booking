<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Form\ReservationFormType;
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
     * @Route("/reservations", name="reservations")
     * @param RoomRepository $roomRepository
     * @return Response
     */
    public function reservation(RoomRepository $roomRepository)
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

              $reservation = $form->getData();
              $reservation->setRoom($room);
              $entityManager->persist($reservation);
              $entityManager->flush();

              return $this->redirectToRoute('home/book.html.twig', [
                  'id' => $room->getId()
              ]);
          }


        return $this->render('home/book.html.twig', [
            'form' => $form->createView(),
            'room' => $room
        ]);
    }

}