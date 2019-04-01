<?php

namespace App\Controller;

use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/create-category", name="admin/create-category")
     * @param                           Request $request
     * @param                           EntityManagerInterface $entityManager
     * @param                           CategoryRepository $categoryRepository
     * @return                          \Symfony\Component\HttpFoundation\Response
     */
    public function createCategory(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository
    ) {

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

        $category = $categoryRepository->findAll();

        return $this->render(
            'admin/categories.html.twig',
            [
                'categoryForm' => $categoryForm->createView(),
                'categories' => $category

            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/edit-category/{id}", name="admin/edit-category")
     * @param                              Request $request
     * @param                              EntityManagerInterface $entityManager
     * @param                              CategoryRepository $categoryRepository
     * @param                              $id
     * @return                             \Symfony\Component\HttpFoundation\Response
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
     * @Symfony\Component\Routing\Annotation\Route("/admin/hide-category/{id}", name="admin/hide-category")
     * @param                                EntityManagerInterface $entityManager
     * @param                                CategoryRepository $categoryRepository
     * @param                                $id
     * @return                               \Symfony\Component\HttpFoundation\Response
     */
    public function hideCategory(
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        $id
    ) {
        $category = $categoryRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $category->setHidden(1);
        $this->addFlash('success', 'Kategorija Uklonjena');
        $entityManager->flush();

        return $this->redirectToRoute('admin/create-category');
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/activate-category/{id}", name="admin/activate-category")
     * @param                                EntityManagerInterface $entityManager
     * @param                                CategoryRepository $categoryRepository
     * @param                                $id
     * @return                               \Symfony\Component\HttpFoundation\Response
     */
    public function activateCategory(
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        $id
    ) {
        $category = $categoryRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $category->setHidden(0);
        $this->addFlash('success', 'Kategorija aktivirana');
        $entityManager->flush();

        return $this->redirectToRoute('admin/create-category');
    }
}
