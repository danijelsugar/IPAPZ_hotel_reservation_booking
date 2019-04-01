<?php

namespace App\Controller;

use App\Form\ReviewFromType;
use App\Repository\ReservationRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends AbstractController
{
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
                $this->addFlash('warning', 'Something went wrong');
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
