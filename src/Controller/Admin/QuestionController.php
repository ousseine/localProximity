<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\Survey;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/localProximity/question')]
#[IsGranted("ROLE_ADMIN")]
final class QuestionController extends AbstractController
{
    #[Route('/{id}/new', name: 'admin_question_new', methods: ['GET', 'POST'])]
    public function new(Survey $survey, Request $request, EntityManagerInterface $em): Response
    {
        $question = new Question();
        $question->setSurvey($survey);

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();

            return $this->redirectToRoute('admin_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/question/new.html.twig', [
            'question' => $question,
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/{question_id}-{survey_id}/edit', name: 'admin_question_edit', methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity(mapping: ['question_id' => 'id'])] Question $question,
        #[MapEntity(mapping: ['survey_id' => 'id'])] Survey $survey,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/question/edit.html.twig', [
            'question' => $question,
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_question_delete', methods: ['DELETE'])]
    public function delete(Request $request, Question $question, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($question);
            $em->flush();
        }

        return $this->redirectToRoute('admin_survey_index', [], Response::HTTP_SEE_OTHER);
    }
}
