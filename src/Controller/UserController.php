<?php

namespace App\Controller;

use \DateTimeImmutable;
use App\Entity\User;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Form\RegistrationFormType;
use App\Form\UpdateUserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response
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
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Le compte utilisateur est créé.');
            return $this->redirectToRoute('home');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/user/update', name: 'update_user')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function update(
        Request $request, ManagerRegistry $doctrine,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response
    {
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();
        $formOptions = ['include_password' => true];
        $form = $this->createForm(UpdateUserFormType::class, $user, $formOptions);

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
            $this->addFlash('success', 'Le compte utilisateur est modifié.');
            return $this->redirectToRoute('home');
        }

        return $this->render('user/update.html.twig', [
            'updateUserForm' => $form,
            'formOptions' => $formOptions,
            'userToUpdate' => $user,
        ]);
    }

    #[Route('/user/admin_update/{userId}', name: 'admin_update_user', defaults: ['userId' => 0])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminUpdate(
        Request $request, ManagerRegistry $doctrine,
        UserPasswordHasherInterface $userPasswordHasher, int $userId = 0
    ): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($userId);
        $formOptions = ['include_password' => false];
        $form = $this->createForm(UpdateUserFormType::class, $user, $formOptions);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le compte utilisateur est modifié.');
            return $this->redirectToRoute('home');
        }

        return $this->render('user/update.html.twig', [
            'updateUserForm' => $form,
            'formOptions' => $formOptions,
            'userToUpdate' => $user,
        ]);
    }

    #[Route('/user/delete/{userId}', name: 'delete_user')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function delete(
        Request $request, ManagerRegistry $doctrine,
        Session $session, int $userId
    ): Response
    {
        $entityManager = $doctrine->getManager();
        $userLogged = $this->getUser();
        // we attribute user's tasks to user anonyme
        $userAnonyme = $entityManager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
        $taskRepository = new TaskRepository($doctrine);
        $tasks = $taskRepository->findAllTasksByAuthor($userId);
        foreach ($tasks as $task) {
            $task->setAuthor($userAnonyme);
            $now = new DateTimeImmutable();
            $task->setUpdatedAt($now);
            $entityManager->flush();
        }
        if ($userLogged->getId() === $userId) {
            $session = new Session();
            $session->invalidate();
            $entityManager->remove($userLogged);
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        } elseif ($this->isGranted('ROLE_ADMIN')) {
            $userToDelete = $entityManager->getRepository(User::class)->find($userId);
            $entityManager->remove($userToDelete);
            $entityManager->flush();
            $this->addFlash('success', 'Le compte utilisateur est supprimé.');
            return $this->redirectToRoute('list_user');
        }
        $session = new Session();
        $session->invalidate();    
        return $this->redirectToRoute('app_login');
    }

    #[Route('/user/list', name: 'list_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function list(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->render('user/users.list.html.twig', [
            'users' => $users,
        ]);
    }
}