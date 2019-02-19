<?php


namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $room = $roomRepository->getAll();

        return $this->render('home/index.html.twig', [
            'rooms' => $room
        ]);
    }

    /**
     * @Route("/reservations", name="reservations")
     * @param RoomRepository $roomRepository
     * @return Response
     */
    public function reservation(RoomRepository $roomRepository)
    {
        $room = $roomRepository->getAll();

        return $this->render('home/reservation.html.twig', [
            'rooms' => $room
        ]);
    }

    /**
     * @Route("/book/{id}", name="book")
     * @param ReservationRepository $reservationRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @return Response
     */
    public function book($id, EntityManagerInterface $entityManager, Request $request, RoomRepository $roomRepository)
    {

        $form = $this->createForm(ReservationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Reservation $reservation */
            $reservation = $form->getData();
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('home/book.html.twig', [
                'id' => $id
            ]);
        }

        $room = $roomRepository->find($id);

        return $this->render('home/book.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'room' => $room
        ]);
    }

}