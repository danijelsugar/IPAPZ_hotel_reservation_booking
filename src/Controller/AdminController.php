<?php


namespace App\Controller;

use App\Form\CategoryFormType;
use App\Form\UserFormType;
use App\Form\OrderByFormType;
use App\Form\RoomFormType;
use App\Form\SubCategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class AdminController
 *
 * @package App\Controller
 *
 * Security annotation on login will throw 403 and on register route
 * we use redirect to route. Both examples are correct.
 */
class AdminController extends AbstractController
{


    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/create-category", name="admin/create-category")
     * @param                           Request $request
     * @param                           EntityManagerInterface $entityManager
     * @param                           CategoryRepository $categoryRepository
     * @param                           SubCategoryRepository $subCategoryRepository
     * @return                          Response
     */
    public function createCategory(
        Request $request,
        EntityManagerInterface $entityManager,
        SubCategoryRepository $subCategoryRepository,
        CategoryRepository $categoryRepository
    ) {

        $form = $this->createForm(SubCategoryFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var \App\Entity\SubCategory $subCategory
             */
            $subCategory = $form->getData();
            $entityManager->persist($subCategory);
            $this->addFlash('success', 'Podkategorija kreirana');
            $entityManager->flush();

            return $this->redirectToRoute('admin/create-category');
        }

        $categoryForm = $this->createForm(CategoryFormType::class);
        $categoryForm->handleRequest($request);
        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {

            /**
             * @var \App\Entity\Category $category
             */
            $category = $categoryForm->getData();
            $entityManager->persist($category);
            $this->addFlash('success', 'Kategorija kreirana');
            $entityManager->flush();

            return $this->redirectToRoute('admin/create-category');
        }


        $subCategory = $subCategoryRepository->findAll();
        $category = $categoryRepository->findAll();

        return $this->render(
            'admin/categories.html.twig',
            [
                'form' => $form->createView(),
                'subCategories' => $subCategory,
                'categoryForm' => $categoryForm->createView(),
                'categories' => $category

            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/edit-subcategory/{id}", name="admin/edit-subcategory")
     * @param                                 Request $request
     * @param                                 EntityManagerInterface $entityManager
     * @param                                 SubCategoryRepository $subCategoryRepository
     * @param                                 $id
     * @return                                Response
     */
    public function editSubcategory(
        Request $request,
        EntityManagerInterface $entityManager,
        SubCategoryRepository $subCategoryRepository,
        $id
    ) {

        $subCategory = $subCategoryRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $form = $this->createForm(SubCategoryFormType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Entity\SubCategory $subCategory
             */
            $subCategory = $form->getData();
            $entityManager->flush();

            return $this->redirectToRoute(
                'admin/create-category',
                [
                    'id' => $subCategory->getId()
                ]
            );
        }


        return $this->render(
            'admin/edit_subcategory.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/delete-subcategory/{id}", name="admin/delete-subcategory")
     * @param                                   EntityManagerInterface $entityManager
     * @param                                   SubCategoryRepository $subCategoryRepository
     * @param                                   $id
     * @return                                  Response
     */
    public function deleteSubcategory(
        EntityManagerInterface $entityManager,
        SubCategoryRepository $subCategoryRepository,
        $id
    ) {
        $subCategory = $subCategoryRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $entityManager->remove($subCategory);
        $this->addFlash('success', 'Potkategorija obrisana');
        $entityManager->flush();

        return $this->redirectToRoute('admin/create-category');
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/edit-category/{id}", name="admin/edit-category")
     * @param                              Request $request
     * @param                              EntityManagerInterface $entityManager
     * @param                              CategoryRepository $categoryRepository
     * @param                              $id
     * @return                             Response
     */
    public function editCategory(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        $id
    ) {
        $category = $categoryRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Entity\Category $category
             */
            $category = $form->getData();
            $entityManager->flush();

            return $this->redirectToRoute(
                'admin/create-category',
                [
                    'id' => $category->getId()
                ]
            );
        }

        return $this->render(
            'admin/edit_category.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/delete-category/{id}", name="admin/delete-category")
     * @param                                EntityManagerInterface $entityManager
     * @param                                CategoryRepository $categoryRepository
     * @param                                $id
     * @return                               Response
     */
    public function deleteCategory(
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        $id
    ) {
        $category = $categoryRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $entityManager->remove($category);
        $this->addFlash('success', 'Kategorija obrisana');
        $entityManager->flush();

        return $this->redirectToRoute('admin/create-category');
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
     * @Symfony\Component\Routing\Annotation\Route("/admin/reservations", name="admin/reservations")
     * @param                        ReservationRepository $reservationRepository
     * @param                        Request $request
     * @return                       Response
     */
    public function reservations(ReservationRepository $reservationRepository, Request $request)
    {
        $orderForm = $this->createForm(OrderByFormType::class);
        $orderForm->handleRequest($request);

        if ($orderForm->isSubmitted() && $orderForm->isValid()) {
            $choice = $orderForm->getData();
            switch ($choice['orderby']) {
                case 1:
                    $condition = 'r.datefrom';
                    break;
                case 2:
                    $condition = 'u.email';
                    break;
                case 3:
                    $condition = 'c.name';
                    break;
            }
        } else {
            $condition = 'r.datefrom';
        }

        $reservation = $reservationRepository->orderReservations($condition);

        return $this->render(
            'admin/pending.html.twig',
            [
                'reservations' => $reservation,
                'orderForm' => $orderForm->createView()
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/accepted", name="admin/accepted")
     * @param                    ReservationRepository $reservationRepository
     * @return                   Response
     */
    public function acceptedReservations(ReservationRepository $reservationRepository)
    {


        $reservation = $reservationRepository->findAll();

        return $this->render(
            'admin/accepted.html.twig',
            [
                'reservations' => $reservation,
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/declined", name="admin/declined")
     * @param                    ReservationRepository $reservationRepository
     * @return                   Response
     */
    public function declinedReservations(ReservationRepository $reservationRepository)
    {
        $reservation = $reservationRepository->findBy(
            [
                'declined' => 1
            ]
        );

        return $this->render(
            'admin/declined.html.twig',
            [
                'reservations' => $reservation
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/accept/{id}/{roomid}", name="admin/accept")
     * @param                                EntityManagerInterface $entityManager
     * @param                                ReservationRepository $reservationRepository
     * @param                                RoomRepository $roomRepository
     * @param                                $id
     * @param                                $roomid
     * @return                               Response
     */
    public function acceptReservation(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        RoomRepository $roomRepository,
        $id,
        $roomid
    ) {
        $reservation = $reservationRepository->findOneBy(
            [
                'id' => $id
            ]
        );
        $dateFrom = $reservation->getDateFrom();
        $dateTo = $reservation->getDateTo();

        $reservationNum = $reservationRepository->reservationNum($dateFrom, $dateTo, $roomid);
        if ($reservationNum !== 0) {
            $this->addFlash('success', 'Rezervacija ne može biti prihvaćena termin je zauzet');
            return $this->redirectToRoute(
                'admin/reservations'
            );
        }

        /**
         * @var \App\Entity\Reservation $reservation
         */
        $reservation->setStatus(1);
        $reservation->setDeclined(0);
        $this->addFlash('success', 'Rezervacija prihvaćena');
        $entityManager->flush();


        return $this->redirectToRoute(
            'admin/reservations'
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/cancel/{id}/{roomid}", name="admin/cancel")
     * @param                                EntityManagerInterface $entityManager
     * @param                                ReservationRepository $reservationRepository
     * @param                                $id
     * @return                               Response
     */
    public function cancelReservation(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        $id
    ) {


        /**
         * Deleting reservation with given id
         */
        $reservation = $reservationRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        /**
         * @var \App\Entity\Reservation $reservation
         */
        $reservation->setStatus(0);
        $reservation->setDeclined(1);
        $this->addFlash('success', 'Rezervacija otkazana');
        $entityManager->flush();

        return $this->redirectToRoute(
            'admin/accepted',
            [
                'reservations' => $reservation
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/decline/{id}/{roomid}", name="admin/decline")
     * @param                                 EntityManagerInterface $entityManager
     * @param                                 ReservationRepository $reservationRepository
     * @param                                 $id
     * @return                                Response
     */
    public function declinelReservation(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        $id
    ) {

        $reservation = $reservationRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        /**
         * @var \App\Entity\Reservation $reservation
         */
        $reservation->setDeclined(1);
        $this->addFlash('success', 'Reservation declined');
        $entityManager->flush();

        return $this->redirectToRoute(
            'admin/reservations',
            [
                'reservations' => $reservation
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("admin/employees", name="admin/employees")
     * @param                    EntityManagerInterface $entityManager
     * @param                    Request $request
     * @param                    UserPasswordEncoderInterface $encoder
     * @param                    UserRepository $userRepository
     * @return                   Response
     */
    public function newEmployee(
        EntityManagerInterface $entityManager,
        Request $request,
        UserPasswordEncoderInterface $encoder,
        UserRepository $userRepository
    ) {


        $form = $this->createForm(UserFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Entity\User $user
             */
            $user = $form->getData();
            $user->setRoles(array('ROLE_EMPLOYEE'));
            $user->setPassword(
                $encoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $this->addFlash('success', 'Uspiješno kreiran novi zaposlenik');
            $entityManager->flush();


            return $this->redirectToRoute('admin/employees');
        }

        $employees = $userRepository->findByRole();

        return $this->render(
            'admin/employees.html.twig',
            [
                'form' => $form->createView(),
                'employees' => $employees
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/delete-employee/{id}", name="admin/delete-employee")
     * @param                                EntityManagerInterface $entityManager
     * @param                                UserRepository $employeeRepository
     * @param                                $id
     * @return                               Response
     */
    public function deleteEmployee(
        EntityManagerInterface $entityManager,
        UserRepository $employeeRepository,
        $id
    ) {
        $employee = $employeeRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $entityManager->remove($employee);
        $this->addFlash('success', 'Korisnik obrisan');
        $entityManager->flush();

        return $this->redirectToRoute('admin/employees');
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/edit-employee/{id}", name="admin/edit-employee")
     * @param                              Request $request
     * @param                              EntityManagerInterface $entityManager
     * @param                              UserRepository $employeeRepository
     * @param                              UserPasswordEncoderInterface $encoder
     * @param                              $id
     * @return                             Response
     */
    public function editEmployee(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $employeeRepository,
        UserPasswordEncoderInterface $encoder,
        $id
    ) {
        $employee = $employeeRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $form = $this->createForm(UserFormType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Entity\User $employee
             */
            $employee = $form->getData();
            $employee->setRoles(array('ROLE_EMPLOYEE'));
            $employee->setPassword(
                $encoder->encodePassword(
                    $employee,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($employee);
            $entityManager->flush();


            return $this->redirectToRoute('admin/employees');
        }


        return $this->render(
            'admin/edit_employee.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/download/{reservation}", name="admin/pdf-download")
     * @param $reservation
     * @param TransactionRepository $transactionRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadPdf($reservation, TransactionRepository $transactionRepository)
    {
        /**
         * @var \App\Entity\Transaction $transaction
         */
        $transaction = $transactionRepository->findOneBy(
            [
                'reservation' => $reservation
            ]
        );

        $fileName = $transaction->getFileName();
        $filePath = $this->getParameter('kernel.project_dir'). '/public/uploads/invoice/' . $fileName;

        $response = new Response();
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set(
            'Content-Disposotopn',
            'attachment; filename="%s"',
            $fileName
        );
        $response->setContent(file_get_contents($filePath));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/payment-methods", name="admin/payment-methods")
     * @param PaymentMethodRepository $paymentMethodRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentMethods(PaymentMethodRepository $paymentMethodRepository)
    {

        $methods = $paymentMethodRepository->findAll();

        return $this->render(
            'admin/payment_methods.html.twig',
            [
                'methods' => $methods
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/disable-payment-method/{id}", name="admin/disable-payment-method")
     * @param $id
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disablePaymentMethod(
        $id,
        PaymentMethodRepository $paymentMethodRepository,
        EntityManagerInterface $entityManager
    ) {
        $method = $paymentMethodRepository->findOneBy(
            [
                'id' => $id
            ]
        );
        $method->setEnabled(0);
        $entityManager->flush();

        return $this->redirectToRoute(
            'admin/payment-methods'
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/enable-payment-method/{id}", name="admin/enable-payment-method")
     * @param $id
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enablePaymentMethod(
        $id,
        PaymentMethodRepository $paymentMethodRepository,
        EntityManagerInterface $entityManager
    ) {
        $method = $paymentMethodRepository->findOneBy(
            [
                'id' => $id
            ]
        );
        $method->setEnabled(1);
        $entityManager->flush();

        return $this->redirectToRoute(
            'admin/payment-methods'
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/register", name="register")
     * @param              EntityManagerInterface $entityManager
     * @param              Request $request
     * @param              UserPasswordEncoderInterface $encoder
     * @return             Response
     */
    public function register(
        EntityManagerInterface $entityManager,
        Request $request,
        UserPasswordEncoderInterface $encoder
    ) {

        $form = $this->createForm(UserFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Entity\User $employee
             */
            $employee = $form->getData();
            $employee->setRoles(array('ROLE_USER'));
            $employee->setPassword(
                $encoder->encodePassword(
                    $employee,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($employee);
            $this->addFlash('success', 'Uspiješno ste se registrirali');
            $entityManager->flush();

            return $this->redirectToRoute('register');
        }


        return $this->render(
            'home/register.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/login", name="app_login")
     * @Sensio\Bundle\FrameworkExtraBundle\Configuration\Security("not   is_granted('ROLE_USER')")
     * @param           AuthenticationUtils $authenticationUtils
     * @return          Response
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
     * @Symfony\Component\Routing\Annotation\Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }
}
