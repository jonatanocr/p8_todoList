<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UpdateUserType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, 
    UserPasswordHasherInterface $userPasswordHasher, 
    EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/user/update', name: 'update_user')]
    public function update(Request $request, ManagerRegistry $doctrine, 
    UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $this->getUser();
        $form = $this->createForm(UpdateUserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('user/update.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/delete', name: 'delete_user')]
    public function delete(Request $request, ManagerRegistry $doctrine, Session $session): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $this->getUser();

        $session = new Session();
        $session->invalidate();
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute('app_login');

    }
}
