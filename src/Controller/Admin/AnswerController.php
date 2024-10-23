<?php

namespace App\Controller\Admin;

use App\Repository\AnswerRepository;
use App\Repository\SurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route('/localProximity/answer', name: 'admin_answer_')]
class AnswerController extends AbstractController
{
    public function __construct(
        private readonly AnswerRepository $answers,
        private readonly EntityManagerInterface $em,
        private readonly SurveyRepository $surveys,
    ) {}

    #[Route(name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $data = $this->getData($this->answers);

        return $this->render('admin/answer/index.html.twig', [
            'data' => $data,
            'total' => count($data),
            'surveys' => $this->surveys->findAll(),
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request): RedirectResponse
    {
        $data = $this->getData($this->answers);

        // Récupérer chaque ligne
        $ids = $request->getPayload()->get('_selected_rows');

        if (!empty($ids)) {
            $dataId = explode(',', $ids);

            foreach ($dataId as $id) {
                foreach ($data[$id] as $row) {
                    $this->em->remove($row);
                }
            }

            $this->em->flush();
        }

        return $this->redirectToRoute('admin_answer_index');
    }

    #[Route('/download', name: 'download', methods: ['GET'])]
    public function downloadCsv(): RedirectResponse|StreamedResponse
    {
        $data = $this->getData($this->answers);

        if ($data) return $this->getCsv($data);

        return $this->redirectToRoute('admin_answer_index');
    }

    private function getData(AnswerRepository $answers): array
    {
        $index = 1;
        $data = [];
        foreach ($this->getSessionIds($answers->findAll()) as $sessionId) {
            $data[$index] = $answers->findAllUsers($sessionId);
            $index++;
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

    private function getCsv(array $data): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $this->getCsvHeader());

            $index = 1; // Démarrer l'index
            foreach ($data as $rows) {
                $csvRow = [$index];

                foreach ($rows as $row) {
                    $csvRow[] = $row->getResponse();
                }

                // Écrire la ligne formée dans le fichier CSV
                fputcsv($handle, $csvRow);

                // Incrémenter l'index pour la prochaine ligne
                $index++;
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="data.csv"');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    private function getCsvHeader(): array
    {
        $questionsHeader = ['index'];

        foreach ($this->surveys->findAll() as $answer) {
            foreach ($answer->getQuestions() as $question) {
                $questionsHeader[] = $question->getName();
            }
        }

        return $questionsHeader;
    }
}
