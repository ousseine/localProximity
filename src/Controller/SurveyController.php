<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Email;
use App\Entity\Question;
use App\Entity\Survey;
use App\Form\AnswerType;
use App\Form\EmailFormType;
use App\Repository\AboutRepository;
use App\Repository\SurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
    public function index(?int $page, Request $request): RedirectResponse|Response
    {
        $about = $this->abouts->findOneBy(['name' => 'sondage']);
        if (!$page) $page = 0;
        $surveys = $this->surveys->findByPriorityAsc();

        // Si aucune question n'est enregistré
        if (empty($surveys)) return $this->redirectToRoute('survey_error');
        if (empty($surveys[$page])) return $this->redirectToRoute('survey_sended');

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

    #[Route('/survey-sended', name: 'survey_sended', methods: ['GET', 'POST'])]
    public function sended(Request $request): RedirectResponse
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

        $request->getSession()->remove('answers');

        return $this->redirectToRoute('survey_completed');
    }

    #[Route('/survey-completed', name: 'survey_completed', methods: ['GET', 'POST'])]
    public function completed(Request $request, EntityManagerInterface $em): Response
    {
        $sessionId = $request->getSession()->get('session_id');
        if (!$sessionId) return $this->redirectToRoute('survey_index');

        $email = new Email();
        $form = $this->createForm(EmailFormType::class, $email);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($email);
            $em->flush();

            // On supprime l'id de la session une fois le sondage terminé
            $request->getSession()->remove('session_id');
            return $this->redirectToRoute('survey_finish');
        }

        return $this->render('survey/completed.html.twig', [
            'form' => $form,
            'email' => $form,
        ]);
    }

    #[Route('/survey-finish', name: 'survey_finish', methods: ['GET'])]
    public function finish(): Response
    {
        return $this->render('survey/finish.html.twig');
    }

    #[Route('/erreur',name: 'survey_error')]
    public function error(): Response
    {
        return $this->render('survey/error.html.twig');
    }

    #[Route('/survey-email-return',name: 'survey_email_return')]
    public function emailReturn(Request $request): RedirectResponse
    {
        $request->getSession()->remove('session_id');
        return $this->redirectToRoute('survey_index');
    }

    /** * Traitement du formulaire des réponses */
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

            // TODO :: Si on a déjà répondu à la question, on met a jour la réponse au lieu d'ajouter une autre réponse
            // TODO :: inversion des questions ??? cascade dans question survey et answer question

//            $existingAnswer = null;
//            foreach ($answers as $row) {
//                if($row->getQuestion()->getId() === $question->getId()) {
//                    $existingAnswer = $row;
//                    break;
//                }
//            }
//
//            if ($existingAnswer) {
//                $existingAnswer->setResponse($response);
//            } else {
//                $answer = new Answer();
//                $answer->setResponse($response);
//                $answer->setSessionId($this->autoSessionId($session));
//
//                $this->em->persist($answer);
//                $answers[] = $answer;
//            }

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
