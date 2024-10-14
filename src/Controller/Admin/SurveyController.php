<?php

namespace App\Controller\Admin;

use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\SurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/localProximity/survey', name: 'admin_survey_')]
#[IsGranted("ROLE_ADMIN")]
final class SurveyController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SurveyRepository $surveys,
    ) {
    }

    #[Route(name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/survey/index.html.twig', [
            'surveys' => $this->surveys->findByPriorityAsc(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function form(?Survey $survey, Request $request): Response
    {
        if (!$survey) $survey = new Survey();
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$survey->getId()) $this->em->persist($survey);
            $this->em->flush();

            $nextAction = $form->get('saveAndAdd')->isClicked() ? 'admin_survey_new' : 'admin_survey_index';

            return $this->redirectToRoute($nextAction, [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/survey/form.html.twig', [
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/update-priority', name: 'update.priority', methods: ['GET', 'POST'])]
    public function updatePriority(Request $request): Response
    {
        $ids = json_decode($request->getContent(), true);

        $surveys = $this->surveys->findBy(['id' => $ids]);

        $surveyIds = [];
        foreach ($surveys as $survey) {
            $surveyIds[$survey->getId()] = $survey;
        }

        foreach ($ids as $priority => $id) {
            if (isset($surveyIds[$id])) {
                $surveyIds[$id]->setPriority($priority + 1);
            }
            $this->em->flush();
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Survey $survey): Response
    {
        return $this->render('admin/survey/show.html.twig', [
            'survey' => $survey,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, Survey $survey): Response
    {
        if ($this->isCsrfTokenValid('delete'.$survey->getId(), $request->getPayload()->getString('_token'))) {

            // On n'efface pas les réponses, mais plutôt l'id des questions
            foreach ($survey->getAnswers() as $answer) {
                $answer->setSurvey(null);
                $this->em->persist($survey);
            }

            $this->em->remove($survey);
            $this->em->flush();
        }

        $this->addFlash('success', "La suppression à bien été supprimé avec succès.");

        return $this->redirectToRoute('admin_survey_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete', name: 'delete_all', methods: ['POST'])]
    public function deleteAll(Request $request): RedirectResponse
    {
        $rowsId = $request->getPayload()->get('_selected_rows');

        if (!empty($rowsId)) {
            $dataId = explode(',', $rowsId);

            foreach ($dataId as $id) {
                $survey = $this->surveys->findOneBy(['id' => $id]);
                $this->em->remove($survey);
            }

            $this->em->flush();
        }

        $this->addFlash('success', "La suppression à bien été supprimé avec succès.");

        return $this->redirectToRoute('admin_survey_index');
    }
}
