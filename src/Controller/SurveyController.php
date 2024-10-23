<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Email;
use App\Entity\Survey;
use App\Form\AnswerType;
use App\Form\EmailFormType;
use App\Repository\AboutRepository;
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
        private readonly AboutRepository $abouts,
    ) {
    }

    #[Route(name: 'survey_index')]
    #[Route('/{page<\d+>}',name: 'survey_question')]
    public function index(?int $page, Request $request): Response
    {
        $about = $this->abouts->findOneBy([
            'name' => 'sondage',
        ]);

        if (!$page) $page = 0;

        $surveys = $this->surveys->findByPriorityAsc();

        // Si aucune question n'est enregistré
        if (empty($surveys))
            return $this->redirectToRoute('survey_error');

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
            'title' => $about ? $about->getTitle() : "Sondage",
            'description' => $about ? $about->getDescription() : "Description du sondage",
        ]);
    }

    #[Route('/survey-completed', name: 'survey_completed', methods: ['GET', 'POST'])]
    public function completed(Request $request, EntityManagerInterface $em): Response
    {
        $answers = $request->getSession()->get('answers');

        if ($answers === null) {
            return $this->redirectToRoute('survey_index');
        }

        $this->sendSondage($answers, $request);

        $email = new Email();
        $form = $this->createForm(EmailFormType::class, $email);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($email);
            $em->flush();

            $this->addFlash('success', "Votre message à bien été envoyé. Nous vous tiendrons au courant de la suite du sondage");

            return $this->redirectToRoute('survey_email');
        }

        return $this->render('survey/completed.html.twig', [
            'form' => $form,
            'email' => $form,
        ]);
    }

    #[Route('/erreur',name: 'survey_error')]
    public function error(): Response
    {
        return $this->render('survey/error.html.twig');
    }

    // Traitement du formulaire des réponses
    private function answer($page, Survey $survey, $form, $session, $request): RedirectResponse
    {
        $answers = $request->getSession()->get('answers', []);

        foreach ($survey->getQuestions()->toArray() as $question) {
            $response = $form->get('response-'.$question->getId())->getData();

            $path = $request->attributes->get('_route');
            if ($path == 'survey_index' && !$response) {
                $this->addFlash('danger', "Veillez renseigner votre lieu de résidence sur la carte");
                return $this->redirectToRoute('survey_index');
            }

            if (!$response) return $this->redirectToRoute('survey_question', ['page' => $page]);

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

    private function sendSondage($answers, Request $request): void
    {
        foreach ($answers as $row) {
            /** @var Answer $answer */
            $answer = $row;

            $this->em->persist($answer);
            $this->em->flush();
        }

        // On supprime l'id de la session une fois le sondage terminé
        $request->getSession()->remove('session_id');
        $request->getSession()->remove('answers');
    }
}
