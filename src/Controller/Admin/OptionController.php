<?php

namespace App\Controller\Admin;

use App\Entity\Option;
use App\Form\OptionType;
use App\Repository\OptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/localProximity/option', name: 'admin_option_')]
#[IsGranted("ROLE_ADMIN")]
final class OptionController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route(name: 'index', methods: ['GET'])]
    public function index(OptionRepository $optionRepository): Response
    {
        return $this->render('admin/option/index.html.twig', [
            'options' => $optionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function form(?Option $option, Request $request, EntityManagerInterface $em): Response
    {
        if ($option === null) $option = new Option();
        $form = $this->createForm(OptionType::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$option->getId()) $em->persist($option);
            $em->flush();

            $nextAction = $form->get('saveAndAdd')->isClicked() ? 'admin_option_new' : 'admin_option_index';

            return $this->redirectToRoute($nextAction, [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/option/form.html.twig', [
            'option' => $option,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, Option $option): Response
    {
        if ($this->isCsrfTokenValid('delete'.$option->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($option);
            $this->em->flush();
        }

        $this->addFlash('success', "La suppression à bien été effectué avec succès");

        return $this->redirectToRoute('admin_option_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete', name: 'delete_all', methods: ['POST'])]
    public function deleteAll(Request $request, OptionRepository $options): Response
    {
        $ids = $request->getPayload()->get('_selected_rows');

        if (!empty($ids)) {
            $dataId = explode(',', $ids);

            foreach ($dataId as $id) {
                $option = $options->findOneBy(['id' => $id]);
                $this->em->remove($option);
            }

            $this->em->flush();
        }

        $this->addFlash('success', "La suppression à bien été effectué avec succès");

        return $this->redirectToRoute('admin_option_index', [], Response::HTTP_SEE_OTHER);
    }
}
