<?php

namespace App\Controller;

use App\Entity\Task;
use \DateTimeImmutable;
use App\Form\TaskFormType;
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
        $form = $this->createForm(TaskFormType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $now = new DateTimeImmutable();
            $task = $form->getData();
            $task->setIsDone(false);
            $task->setCreatedAt($now);
            $task->setUpdatedAt($now);
            $task->setAuthor($user = $this->getUser());
            $entityManager->persist($task);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a bien été ajoutée.');
            return $this->redirectToRoute('list_task');
        }

        return $this->render('task/form.html.twig', [
            'taskForm' => $form,
            'action' => 'create',
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

    #[Route('/update/{taskId}', name: 'update_task')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function update(
        Request $request,
        ManagerRegistry $doctrine,
        int $taskId = 0
    ): Response {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($taskId);
        $form = $this->createForm(TaskFormType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $now = new DateTimeImmutable();
            $task->setUpdatedAt($now);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a bien été mise à jour.');
            return $this->redirectToRoute('list_task');
        }

        return $this->render('task/form.html.twig', [
            'taskForm' => $form,
            'action' => 'update',
        ]);
    }

    #[Route('/status/{taskId}/{taskStatus}', name: 'status_task')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function updateStatus(
        ManagerRegistry $doctrine,
        int $taskId,
        int $taskStatus
    ): Response {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($taskId);
        $taskStatus = ($taskStatus === 1 ? true : false);
        $task->setIsDone($taskStatus);
        $now = new DateTimeImmutable();
        $task->setUpdatedAt($now);
        $entityManager->flush();
        if ($taskStatus === true) {
            $this->addFlash('success', 'La tâche ' . $task->getTitle() . ' a bien été marquée comme faite.');
        } else {
            $this->addFlash('success', 'La tâche ' . $task->getTitle() . ' a bien été marquée comme à faire.');
        }
        return $this->redirectToRoute('list_task');
    }

    #[Route('/delete/{taskId}', name: 'delete_task')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function delete(
        Request $request,
        ManagerRegistry $doctrine,
        int $taskId
    ): Response {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($taskId);
        $entityManager->remove($task);
        $entityManager->flush();
        $this->addFlash('success', 'La tâche a bien été supprimée.');
        return $this->redirectToRoute('list_task');
    }
}
