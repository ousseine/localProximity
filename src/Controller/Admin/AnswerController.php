<?php

namespace App\Controller\Admin;

use App\Repository\AnswerRepository;
use App\Repository\SurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/localProximity/answer', name: 'admin_answer_')]
#[IsGranted("ROLE_ADMIN")]
class AnswerController extends AbstractController
{
    public function __construct(
        private readonly AnswerRepository $answers,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route(name: 'index', methods: ['GET'])]
    public function index(SurveyRepository $surveys): Response
    {
        $data = $this->getData($this->answers);

        return $this->render('admin/answer/index.html.twig', [
            'data' => $data,
            'total' => count($data),
            'surveys' => $surveys->findAll(),
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(Request $request)
    {
        $data = $this->getData($this->answers);

        // Récupérer chaque ligne
        $ids = $request->getPayload()->get('_ids');

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

    #[Route('/csv', name: 'csv', methods: ['GET', 'POST'])]
    public function csv(AnswerRepository $answers): StreamedResponse
    {
        $data = $this->getData($answers);

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
        $response->headers->set('Content-Disposition', 'attachment; filename="survey_data.csv"');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
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

    private function getCsvHeader(): array
    {
        // TODO :: Csv dynamique
        return [
            'Index',
            'Latitude',
            'Longitude',
            'Service de commerces',
            'Services de soins',
            'Services de formation',
            'Services de divertissement',
            'Services de travail',
            'Services d\'habiter',
            'Déplacement pour du commerce',
            'Déplacement pour du soins',
            'Déplacement pour des formations',
            'Déplacement pour du divertissement',
            'Déplacement pour travailler',
            'Déplacement pour habiter',
            'Remarques ou précision',
            'Civilité',
            'Type de logement',
            'Location',
            'Composition du foyer',
            'Nb voitures',
            'Age',
            'Status pro',
            'En savoir plus',
            'Commentaire',
        ];
    }
}
