<?php

namespace App\Controller;

use App\Form\RoomFormType;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class RoomController extends AbstractController
{
    /**
     * @Symfony\Component\Routing\Annotation\Route("/rooms", name="rooms")
     * @param           RoomRepository $roomRepository
     * @param           ReservationRepository $reservationRepository
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function room(RoomRepository $roomRepository, ReservationRepository $reservationRepository)
    {

        $session = new Session();

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
            if ($reservation == 0) {
                $roomsArray[] = $roomId;
            }
        }

        if (empty($roomsArray)) {
            $alert = 'Prikazuje se par uskoro dostupnih soba';
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
            $alert= '';
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
                'alert' => $alert,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/all-rooms", name="admin/all-rooms")
     * @param RoomRepository $roomRepository
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function allRooms(RoomRepository $roomRepository)
    {
        $rooms = $roomRepository->findAll();

        return $this->render(
            'admin/all_rooms.html.twig',
            [
                'rooms' => $rooms
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/create-room", name="admin/create-room")
     * @param                       Request $request
     * @param                       EntityManagerInterface $entityManager
     * @param RoomRepository $roomRepository
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function createRoom(Request $request, EntityManagerInterface $entityManager, RoomRepository $roomRepository)
    {
        $form = $this->createForm(RoomFormType::class);
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
            $entityManager->persist($room);
            $this->addFlash('success', 'Kreirana nova soba');
            $entityManager->flush();

            return $this->redirectToRoute('admin/all-rooms');
        }

        $rooms = $roomRepository->findAll();

        return $this->render(
            'admin/rooms.html.twig',
            [
                'form' => $form->createView(),
                'rooms' => $rooms
            ]
        );
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
            $this->addFlash('success', 'Soba promijenjena');
            $entityManager->flush();

            return $this->redirectToRoute('admin/all-rooms');
        }


        return $this->render(
            'admin/edit_room.html.twig',
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
     * @Symfony\Component\Routing\Annotation\Route("/admin/disable-room/{id}", name="admin/disable-room")
     * @param                            EntityManagerInterface $entityManager
     * @param                            RoomRepository $roomRepository
     * @param                            $id
     * @return                           \Symfony\Component\HttpFoundation\Response
     */
    public function disableRoom(
        EntityManagerInterface $entityManager,
        RoomRepository $roomRepository,
        $id
    ) {

        $room = $roomRepository->findOneBy(
            [
                'id' => $id
            ]
        );
        $room->setStatus(0);
        $entityManager->flush();


        return $this->redirectToRoute('admin/all-rooms');
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/enable-room/{id}", name="admin/enable-room")
     * @param                            EntityManagerInterface $entityManager
     * @param                            RoomRepository $roomRepository
     * @param                            $id
     * @return                           \Symfony\Component\HttpFoundation\Response
     */
    public function enableRoom(
        EntityManagerInterface $entityManager,
        RoomRepository $roomRepository,
        $id
    ) {

        $room = $roomRepository->findOneBy(
            [
                'id' => $id
            ]
        );
        $room->setStatus(1);
        $entityManager->flush();


        return $this->redirectToRoute('admin/all-rooms');
    }
}
