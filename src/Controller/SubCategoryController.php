<?php

namespace App\Controller;

use App\Form\SubCategoryFormType;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SubCategoryController extends AbstractController
{
    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/create-subcategory", name="admin/create-subcategory")
     * @param                           Request $request
     * @param                           EntityManagerInterface $entityManager
     * @param                           SubCategoryRepository $subCategoryRepository
     * @return                          \Symfony\Component\HttpFoundation\Response
     */
    public function createSubcategory(
        Request $request,
        EntityManagerInterface $entityManager,
        SubCategoryRepository $subCategoryRepository
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

            return $this->redirectToRoute('admin/create-subcategory');
        }

        $subCategory = $subCategoryRepository->findAll();

        return $this->render(
            'admin/subcategories.html.twig',
            [
                'form' => $form->createView(),
                'subCategories' => $subCategory
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/edit-subcategory/{id}", name="admin/edit-subcategory")
     * @param                                 Request $request
     * @param                                 EntityManagerInterface $entityManager
     * @param                                 SubCategoryRepository $subCategoryRepository
     * @param                                 $id
     * @return                                \Symfony\Component\HttpFoundation\Response
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
                'admin/create-subcategory',
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
     * @Symfony\Component\Routing\Annotation\Route("/admin/hide-subcategory/{id}", name="admin/hide-subcategory")
     * @param                                   EntityManagerInterface $entityManager
     * @param                                   SubCategoryRepository $subCategoryRepository
     * @param                                   $id
     * @return                                  \Symfony\Component\HttpFoundation\Response
     */
    public function hideSubcategory(
        EntityManagerInterface $entityManager,
        SubCategoryRepository $subCategoryRepository,
        $id
    ) {
        $subCategory = $subCategoryRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $subCategory->setHidden(1);
        $this->addFlash('success', 'Potkategorija uklonjena');
        $entityManager->flush();

        return $this->redirectToRoute('admin/create-subcategory');
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/admin/activate-subcategory/{id}", name="admin/activate-subcategory")
     * @param                                   EntityManagerInterface $entityManager
     * @param                                   SubCategoryRepository $subCategoryRepository
     * @param                                   $id
     * @return                                  \Symfony\Component\HttpFoundation\Response
     */
    public function activateSubcategory(
        EntityManagerInterface $entityManager,
        SubCategoryRepository $subCategoryRepository,
        $id
    ) {
        $subCategory = $subCategoryRepository->findOneBy(
            [
                'id' => $id
            ]
        );

        $subCategory->setHidden(0);
        $this->addFlash('success', 'Potkategorija aktivirana');
        $entityManager->flush();

        return $this->redirectToRoute('admin/create-subcategory');
    }
}
