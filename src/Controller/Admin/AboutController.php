<?php

namespace App\Controller\Admin;

use App\Entity\About;
use App\Form\AboutType;
use App\Repository\AboutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route('/localProximity/about')]
final class AboutController extends AbstractController
{
    #[Route(name: 'admin_about_index', methods: ['GET'])]
    public function index(AboutRepository $aboutRepository): Response
    {
        return $this->render('admin/about/index.html.twig', [
            'abouts' => $aboutRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_about_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'admin_about_edit', methods: ['GET', 'POST'])]
    public function form(?About $about, Request $request, EntityManagerInterface $entityManager): Response
    {
        if(!$about) $about = new About();
        $form = $this->createForm(AboutType::class, $about);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$about->getId()) {
                $entityManager->persist($about);
            }

            $entityManager->flush();

            $message = $request->getPayload()->get('_route') === "admin_about_new" ? "About ajouter avec succès." : "About modifier avec succès";

            $this->addFlash('success', $message);

            return $this->redirectToRoute('admin_about_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/about/form.html.twig', [
            'about' => $about,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_about_delete', methods: ['DELETE'])]
    public function delete(Request $request, About $about, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$about->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($about);
            $entityManager->flush();
        }

        $this->addFlash('success', "La suppression à bien été fait avec succès");

        return $this->redirectToRoute('admin_about_index', [], Response::HTTP_SEE_OTHER);
    }
}
