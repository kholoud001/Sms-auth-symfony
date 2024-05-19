<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\TwilioService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/new', name: 'user_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, TwilioService $twilioService): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the password (you should set up an encoder service)
            //$encodedPassword = password_hash($user->getPassword(), PASSWORD_BCRYPT);
            $encodedPassword=$user->getPassword();
            $user->setPassword($encodedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            // Send SMS with username and password
            $message = "Your username: {$user->getPhoneNumber()}, Your password: {$user->getPassword()}";
            $twilioService->sendSms($user->getPhoneNumber(), $message);

            return $this->redirectToRoute('user_success');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/success', name: 'user_success')]
    public function success(): Response
    {
        return $this->render('user/success.html.twig');
    }
}
