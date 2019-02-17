<?php


namespace App\Controller;

use App\Entity\SubCategory;
use App\Form\SubCategoryFormType;
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
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/create-category", name="create-category")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SubCategoryRepository $subCategoryRepository
     * @return Response
     */
    public function createCategory(Request $request, EntityManagerInterface $entityManager, SubCategoryRepository
    $subCategoryRepository)
    {
        $form = $this->createForm(SubCategoryFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {

            $subCategory = $form->getData();
            $entityManager->persist($subCategoryRepository);
            $entityManager->flush();
            $this->addFlash('succes', 'New subcategory created');
            return $this->redirectToRoute('create-category');
        }

        $subCategory = $subCategoryRepository->getAll();

        return $this->render('admin/categories.html.twig', [
           'form' => $form->createView(),
           'subCategories' => $subCategory
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