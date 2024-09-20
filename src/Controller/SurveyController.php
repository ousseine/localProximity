<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Survey;
use App\Form\AnswerType;
use App\Repository\SurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class SurveyController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SurveyRepository $surveys,
    ) {
    }

    #[Route(name: 'survey_index')]
    #[Route('/{page<\d+>}',name: 'survey_question')]
    public function index(?int $page, Request $request): Response
    {
        if (!$page) $page = 0;

        $surveys = $this->surveys->findAll();

        if (empty($surveys[$page]))
            return $this->redirectToRoute('survey_completed');

        $survey = $surveys[$page];

        $form = $this->createForm(AnswerType::class, null, ['survey' => $survey]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->answer($page, $survey, $form, $request->getSession(), $request);
        }

        return $this->render('survey/index.html.twig', [
            'form' => $form,
            'survey' => $survey,
            'page' => $page + 1,
            'pages' => count($this->surveys->findAll()),
        ]);
    }

    #[Route('/complete',name: 'survey_completed')]
    public function completed(Request $request): Response
    {
        $answers = $request->getSession()->get('answers');

        if ($answers === null) {
            return $this->redirectToRoute('survey_index');
        }

        foreach ($answers as $row) {
            /** @var Answer $answer */
            $answer = $row;

            $this->em->persist($answer);
            $this->em->flush();
        }

        // On supprime l'id de la session une fois le sondage terminé
        $request->getSession()->remove('session_id');
        $request->getSession()->remove('answers');

        return $this->render('survey/completed.html.twig');
    }

    // Traitement du formulaire des réponses
    private function answer($page, Survey $survey, $form, $session, $request): RedirectResponse
    {
        $answers = $request->getSession()->get('answers', []);

        foreach ($survey->getQuestions()->toArray() as $question) {
            $response = $form->get('response-'.$question->getId())->getData();

            if (!$response) {
                $this->addFlash('warning', "Veillez renseigner votre lieu de résidence sur la carte");
                return $this->redirectToRoute('survey_index');
            }

            $answer = new Answer();
            $answer->setResponse($response);
            $answer->setSessionId($this->autoSessionId($session));

            $this->em->persist($answer);

            $answers[] = $answer;
        }

        $request->getSession()->set('answers', $answers);

        return $this->redirectToRoute('survey_question', [
            'page' => $page + 1,
        ]);
    }

    // Génère l'identifiant unique
    private function autoSessionId($session): string
    {
        if (!$session->has('session_id')) {
            $sessionId = Uuid::v4()->toRfc4122();
            $session->set('session_id', $sessionId);
        } else {
            $sessionId = $session->get('session_id');
        }

        return $sessionId;
    }
}
