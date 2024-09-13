<?php

namespace App\Controller\Admin;

use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\SurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/survey')]
final class SurveyController extends AbstractController
{
    #[Route(name: 'admin_survey_index', methods: ['GET'])]
    public function index(SurveyRepository $surveyRepository): Response
    {
        return $this->render('admin/survey/index.html.twig', [
            'surveys' => $surveyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_survey_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'admin_survey_edit', methods: ['GET', 'POST'])]
    public function form(?Survey $survey, Request $request, EntityManagerInterface $em): Response
    {
        if (!$survey) $survey = new Survey();
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$survey->getId()) $em->persist($survey);
            $em->flush();

            $nextAction = $form->get('saveAndAdd')->isClicked() ? 'admin_survey_new' : 'admin_survey_index';

            return $this->redirectToRoute($nextAction, [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/survey/form.html.twig', [
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_survey_show', methods: ['GET'])]
    public function show(Survey $survey): Response
    {
        return $this->render('admin/survey/show.html.twig', [
            'survey' => $survey,
        ]);
    }

    #[Route('/{id}', name: 'admin_survey_delete', methods: ['DELETE'])]
    public function delete(Request $request, Survey $survey, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$survey->getId(), $request->getPayload()->getString('_token'))) {

            // On n'efface pas les réponses, mais plutôt l'id des questions
            foreach ($survey->getAnswers() as $answer) {
                $answer->setSurvey(null);
                $em->persist($survey);
            }

            $em->remove($survey);

            $em->flush();
        }

        return $this->redirectToRoute('admin_survey_index', [], Response::HTTP_SEE_OTHER);
    }
}
