<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Email;
use App\Repository\EmailRepository;
use App\Repository\OptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route('/localProximity/email', name: 'admin_email_')]
class EmailController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route(name: 'index', methods: ['GET'])]
    public function index(EmailRepository $emails): Response
    {
        return $this->render('admin/email/index.html.twig', [
            'emails' => $emails->findAll()
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, Email $email): Response
    {
        if ($this->isCsrfTokenValid('delete'.$email->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($email);
            $this->em->flush();
        }

        $this->addFlash('success', "La suppression à bien été effectué avec succès");

        return $this->redirectToRoute('admin_email_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete', name: 'delete_all', methods: ['POST'])]
    public function deleteAll(Request $request, EmailRepository $emails): Response
    {
        $ids = $request->getPayload()->get('_selected_rows');

        if (!empty($ids)) {
            $dataId = explode(',', $ids);

            foreach ($dataId as $id) {
                $email = $emails->findOneBy(['id' => $id]);
                $this->em->remove($email);
            }

            $this->em->flush();
        }

        $this->addFlash('success', "La suppression à bien été effectué avec succès");

        return $this->redirectToRoute('admin_email_index', [], Response::HTTP_SEE_OTHER);
    }
}
