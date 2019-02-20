<?php


namespace App\Controller;

use App\Entity\SubCategory;
use App\Entity\Category;
use App\Entity\Room;
use App\Form\CategoryFormType;
use App\Form\RoomFormType;
use App\Form\SubCategoryFormType;
use App\Repository\CategoryRepository;
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
            $entityManager->persist($subCategory);
            $entityManager->flush();

            return $this->redirectToRoute('edit-subcategory', [
                'id' => $subCategory->getId()
            ]);
        }


        return $this->render('admin/edit_subcategory.html.twig', [
           'form' => $form->createView()
        ]);


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
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('edit-category', [
                'id' => $category->getId()
            ]);
        }

        return $this->render('admin/edit_category.html.twig', [
            'form' => $form->createView()
        ]);

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