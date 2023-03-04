<?php

namespace App\Controller;

use App\Entity\Task;
use \DateTimeImmutable;
use App\Form\CreateTaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/task')]
class TaskController extends AbstractController
{

    #[Route('/create', name: 'create_task')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $task = new Task();
        $entityManager = $doctrine->getManager();
        $form = $this->createForm(CreateTaskFormType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $now = new DateTimeImmutable();
            $task = $form->getData();
            $task->setCreatedAt($now);
            $task->setUpdatedAt($now);
            $task->setAuthor($user = $this->getUser());
            $entityManager->persist($task);
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('task/create.html.twig', [
            'createTaskForm' => $form,
        ]);
    }

    #[Route('/list', name: 'list_task')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function list(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $tasks = $entityManager->getRepository(Task::class)->findAll();
        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
        ]);
    }

}