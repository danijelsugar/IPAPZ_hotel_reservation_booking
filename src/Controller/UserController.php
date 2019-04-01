<?php

namespace App\Controller;

use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
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
        $this->addFlash('success', 'Zaposlenik obrisan');
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
     * @Symfony\Component\Routing\Annotation\Route("/register", name="register")
     * @param              EntityManagerInterface $entityManager
     * @param              Request $request
     * @param              UserPasswordEncoderInterface $encoder
     * @return             \Symfony\Component\HttpFoundation\Response
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
