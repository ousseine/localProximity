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

#[Route('/admin/option')]
final class OptionController extends AbstractController
{
    #[Route(name: 'admin_option_index', methods: ['GET'])]
    public function index(OptionRepository $optionRepository): Response
    {
        return $this->render('admin/option/index.html.twig', [
            'options' => $optionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_option_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'admin_option_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id}', name: 'admin_option_delete', methods: ['DELETE'])]
    public function delete(Request $request, Option $option, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$option->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($option);
            $em->flush();
        }

        return $this->redirectToRoute('admin_option_index', [], Response::HTTP_SEE_OTHER);
    }
}
