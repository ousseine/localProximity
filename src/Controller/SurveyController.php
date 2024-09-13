<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Survey;
use App\Form\AnswerType;
use App\Repository\SurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SurveyController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SurveyRepository $surveys,
    ) {}

    #[Route(name: 'survey_index')]
    public function index(SurveyRepository $surveys, Request $request): Response
    {
        $survey = $surveys->findOneBy(['slug' => 'cliquez-sur-la-carte-pour-marquer-votre-lieu-de-residence']);

        // Ajouter les questions s'il n'y a pas
        if (empty($survey)) return $this->redirectToRoute('admin_survey_new');

        $form = $this->createForm(AnswerType::class, null, ['survey' => $survey]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($survey->getQuestions()->toArray() as $question) {
                $answer = new Answer();
                $answer->setSurvey($survey);
                $answer->setQuestion($question);
                $answer->setResponse($form->get('response-'.$question->getId())->getData());

                $this->em->persist($answer);
            }

            $this->em->flush();

            return $this->redirectToRoute('survey_question', [
                'page' => 1
            ]);
        }

        return $this->render('survey/index.html.twig', [
            'form' => $form,
            'page' => 1,
            'survey' => $survey,
            'total' => count($surveys->findAll())
        ]);
    }

    #[Route('/question/{page}',name: 'survey_question')]
    public function question(int $page, Request $request): Response
    {
        $surveys = $this->surveys->findAll();

        if (empty($surveys[$page]))
            return $this->redirectToRoute('survey_completed');

        $survey = $surveys[$page];
        $form = $this->createForm(AnswerType::class, null, ['survey' => $survey]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($survey->getQuestions()->toArray() as $question) {
                $answer = new Answer();
                $answer->setSurvey($survey);
                $answer->setQuestion($question);
                $answer->setResponse($form->get('response-'.$question->getId())->getData());
                $this->em->persist($answer);
            }

            $this->em->flush();

            return $this->redirectToRoute('survey_question', ['page' => $page + 1]);
        }

        return $this->render('survey/question.html.twig', [
            'survey' => $survey,
            'page' => $page + 1,
            'form' => $form,
            'total' => count($surveys),
        ]);
    }

    #[Route('/complete',name: 'survey_completed')]
    public function completed(): Response
    {
        return $this->render('survey/completed.html.twig');
    }

    private function answer(Survey $survey, $form): RedirectResponse
    {
        foreach ($survey->getQuestions()->toArray() as $question) {
            $answer = new Answer();
            $answer->setSurvey($survey);
            $answer->setQuestion($question);
            $answer->setResponse($form->get('response-'.$question->getId())->getData());

            $this->em->persist($answer);
        }

        $this->em->flush();

        return $this->redirectToRoute('survey_question', ['page' => 1]);
    }
}
