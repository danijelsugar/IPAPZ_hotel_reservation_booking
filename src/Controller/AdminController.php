<?php


namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\SubCategory;
use App\Entity\Category;
use App\Entity\Room;
use App\Form\CategoryFormType;
use App\Form\RoomFormType;
use App\Form\SubCategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class AdminController
 * @package App\Controller
 *
 * Security annotation on login will throw 403 and on register route we use redirect to route. Both examples are correct.
 */
class AdminController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/create-category", name="create-category")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @param SubCategoryRepository $subCategoryRepository
     * @return Response
     */
    public function createCategory(Request $request, EntityManagerInterface $entityManager, SubCategoryRepository
    $subCategoryRepository, CategoryRepository $categoryRepository)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {


            $form = $this->createForm(SubCategoryFormType::class);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                /** @var SubCategory $subCategory */
                $subCategory = $form->getData();
                $entityManager->persist($subCategory);
                $entityManager->flush();

                return $this->redirectToRoute('create-category');
            }

            $categoryForm = $this->createForm(CategoryFormType::class);
            $categoryForm->handleRequest($request);
            if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {

                /** @var Category $category */
                $category = $categoryForm->getData();
                $entityManager->persist($category);
                $entityManager->flush();

                return $this->redirectToRoute('create-category');
            }


            $subCategory = $subCategoryRepository->getAll();
            $category = $categoryRepository->getAll();

            return $this->render('admin/categories.html.twig', [
                'form' => $form->createView(),
                'subCategories' => $subCategory,
                'categoryForm' => $categoryForm->createView(),
                'categories' => $category

            ]);
        } else {
            return $this->render('404.html.twig');
        }
    }

    /**
     * @Route("/edit-subcategory/{id}", name="edit-subcategory")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SubCategoryRepository $subCategoryRepository
     * @param $id
     * @return Response
     */
    public function editSubcategory(Request $request, EntityManagerInterface $entityManager, SubCategoryRepository
    $subCategoryRepository, $id)
    {

        $subCategory = $subCategoryRepository->findOneBy([
           'id' => $id
        ]);

        $form = $this->createForm(SubCategoryFormType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SubCategory $subCategory */
            $subCategory = $form->getData();
            $entityManager->flush();

            return $this->redirectToRoute('create-category', [
                'id' => $subCategory->getId()
            ]);
        }


        return $this->render('admin/edit_subcategory.html.twig', [
           'form' => $form->createView()
        ]);


    }

    /**
     * @Route("/delete-subcategory/{id}", name="delete-subcategory")
     * @param EntityManagerInterface $entityManager
     * @param SubCategoryRepository $subCategoryRepository
     * @param $id
     * @return Response
     */
    public function deleteSubcategory(EntityManagerInterface $entityManager, SubCategoryRepository
    $subCategoryRepository, $id)
    {
        $subCategory = $subCategoryRepository->findOneBy([
            'id' => $id
        ]);

        $entityManager->remove($subCategory);
        $entityManager->flush();

        return $this->redirectToRoute('create-category');

    }

    /**
     * @Route("/edit-category/{id}", name="edit-category")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @param $id
     * @return Response
     */
    public function editCategory(Request $request, EntityManagerInterface $entityManager, CategoryRepository
    $categoryRepository, $id)
    {
        $category = $categoryRepository->findOneBy([
            'id' => $id
        ]);

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Category $category */
            $category = $form->getData();
            $entityManager->flush();

            return $this->redirectToRoute('create-category', [
                'id' => $category->getId()
            ]);
        }

        return $this->render('admin/edit_category.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/delete-category/{id}", name="delete-category")
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @param $id
     * @return Response
     */
    public function deleteCategory(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, $id)
    {
        $category = $categoryRepository->findOneBy([
            'id' => $id
        ]);

        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('create-category');
    }

    /**
     * @Route("/create-room", name="create-room")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function createRoom(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(RoomFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Room $room */
            $room = $form->getData();
            $entityManager->persist($room);
            $entityManager->flush();

            return $this->redirectToRoute('create-room');
        }

        return $this->render('admin/rooms.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reservations", name="reservations")
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    public function reservations(ReservationRepository $reservationRepository)
    {
        $reservation = $reservationRepository->findAll();

        return $this->render('admin/admin.html.twig', [
           'reservations' => $reservation
        ]);
    }

    /**
     * @Route("/accepted", name="accepted")
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    public function acceptedReservations(ReservationRepository $reservationRepository)
    {
        $reservation = $reservationRepository->findAll();

        return $this->render('admin/accepted.html.twig', [
            'reservations' => $reservation
        ]);
    }

    /**
     * @Route("accept/{id}/{roomid}", name="accept")
     * @param EntityManagerInterface $entityManager
     * @param ReservationRepository $reservationRepository
     * @param RoomRepository $roomRepository
     * @param $id
     * @param $roomid
     * @return Response
     */
    public function acceptReservation(EntityManagerInterface $entityManager, ReservationRepository
    $reservationRepository, RoomRepository $roomRepository, $id, $roomid)
    {
        $reservation = $reservationRepository->findOneBy([
            'id' => $id
        ]);

        /** @var Reservation $reservation */
        $reservation->setStatus(1);
        $entityManager->flush();

        $room = $roomRepository->findOneBy([
           'id' => $roomid
        ]);

        /** @var Room $room */
        $amount = $room->getAmount();
        $after = --$amount;
        $room->setAmount($after);
        $entityManager->flush();

        return $this->redirectToRoute('reservations', [
            'reservations' => $reservation
        ]);


    }

    /**
     * @Route("decline/{id}/{roomid}", name="decline")
     * @param EntityManagerInterface $entityManager
     * @param ReservationRepository $reservationRepository
     * @param RoomRepository $roomRepository
     * @param $id
     * @param $roomid
     * @return Response
     */
    public function declineReservation(EntityManagerInterface $entityManager, ReservationRepository
    $reservationRepository, RoomRepository $roomRepository, $id, $roomid)
    {
        $reservation = $reservationRepository->findOneBy([
            'id' => $id
        ]);


        /** @var Reservation $reservation */
        $reservation->setStatus(0);
        $entityManager->flush();

        $room = $roomRepository->findOneBy([
            'id' => $roomid
        ]);

        /** @var Room $room */
        $amount = $room->getAmount();
        $after = ++$amount;
        $room->setAmount($after);

        $entityManager->flush();

        return $this->redirectToRoute('accepted', [
            'reservations' => $reservation
        ]);


    }

    /**
     * @Route("/login", name="app_login")
     * @Security("not is_granted('ROLE_USER')")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {

    }
}