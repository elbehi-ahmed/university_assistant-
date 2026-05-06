<?php

namespace App\Controller\Admin;

use App\Entity\AllowedEmail;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminWhitelistController extends AbstractController
{
    #[Route('/whitelist', name: 'app_admin_whitelist')]
    public function list(Request $request, EntityManagerInterface $em): Response
    {
        $admin = $this->getAdmin($em);
        if (!$admin) {
            throw $this->createNotFoundException('No admin account found.');
        }

        $searchQuery = $request->query->get('q', '');

        $qb = $em->getRepository(AllowedEmail::class)->createQueryBuilder('a');
        
        if ($searchQuery) {
            $qb->andWhere('a.email LIKE :q')
               ->setParameter('q', '%' . $searchQuery . '%');
        }

        $allowedEmails = $qb->getQuery()->getResult();

        // Build add form view (manual since we don't have Form component)
        return $this->render('admin/whitelist.html.twig', [
            'allowed_emails' => $allowedEmails,
        ]);
    }

    #[Route('/whitelist/add', name: 'app_admin_whitelist_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $email = $request->request->get('email');
        $note = $request->request->get('note', '');

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existing = $em->getRepository(AllowedEmail::class)->findOneBy(['email' => $email]);
            if (!$existing) {
                $allowed = new AllowedEmail();
                $allowed->setEmail($email);
                // Note field doesn't exist in your entity - add it or remove this
                // $allowed->setNote($note);
                $allowed->setRole('ROLE_STUDENT'); // default role
                $allowed->setIsRegistered(false);
                
                $em->persist($allowed);
                $em->flush();
            }
        }

        return $this->redirectToRoute('app_admin_whitelist');
    }

    #[Route('/whitelist/{id}/revoke', name: 'app_admin_whitelist_revoke', methods: ['POST'])]
    public function revoke(int $id, EntityManagerInterface $em): Response
    {
        $allowed = $em->getRepository(AllowedEmail::class)->find($id);
        if ($allowed) {
            $em->remove($allowed);
            $em->flush();
        }
        return $this->redirectToRoute('app_admin_whitelist');
    }

    private function getAdmin(EntityManagerInterface $em): ?User
    {
        $users = $em->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                return $user;
            }
        }
        return $users[0] ?? null;
    }
}