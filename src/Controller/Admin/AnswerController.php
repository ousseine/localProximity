<?php

namespace App\Controller\Admin;

use App\Repository\AnswerRepository;
use App\Repository\SurveyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/localProximity/answer', name: 'admin_answer_')]
#[IsGranted("ROLE_ADMIN")]
class AnswerController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(AnswerRepository $answers, SurveyRepository $surveys): Response
    {
        $data = $this->getData($answers);

        return $this->render('admin/answer/index.html.twig', [
            'data' => $data,
            'total' => count($data),
            'surveys' => $surveys->findAll(),
        ]);
    }

    public function csv()
    {

    }

    private function getData(AnswerRepository $answers): array
    {
        $data = [];
        foreach ($this->getSessionIds($answers->findAll()) as $sessionId) {
            $data[] = $answers->findAllUsers($sessionId);
        }

        return $data;
    }

    private function getSessionIds($answers): array
    {
        $sessionIds = [];
        foreach ($answers as $answer) {
            // On ne veut pas des sessionId null :
            if ($answer->getSessionId() !== null) {
                // ni des doublons : array_unique
                $sessionIds[] = $answer->getSessionId();
            }
        }

        return array_unique($sessionIds);
    }
}
