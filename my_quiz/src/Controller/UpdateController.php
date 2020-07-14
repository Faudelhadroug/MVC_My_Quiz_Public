<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\User;
use App\Form\UpdateEmailType;
use App\Form\UpdatePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UpdateController extends AbstractController
{
    public function updateEmail(Request $request)
    {
        $user = new User();
        $userOld = $this->get('security.token_storage')->getToken()->getUser();
        if ($userOld == "anon.")
        {
            return new RedirectResponse('http://localhost:8080/account');   
        }
        $form = $this->createForm(UpdateEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newemail = $form->get('email')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $userDB = $entityManager->getRepository(User::class)->find($userOld->getId());
            $userDB->setVerifiedAt(null);
            $userDB->setEmail($newemail);
            $entityManager->flush();
            return new RedirectResponse('http://localhost:8080/account');   

        }

        return $this->render('update/email.html.twig', [
            'updateEmailForm' => $form->createView(),
        ]);
    }

    public function updatePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $userOld = $this->get('security.token_storage')->getToken()->getUser();
        if ($userOld == "anon.")
        {
            return new RedirectResponse('http://localhost:8080/account');  
        }
        $form = $this->createForm(UpdatePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            if ($form->get('plainPassword')->getData() == $form->get('plainPasswordConfirmation')->getData())
            {
                $entityManager = $this->getDoctrine()->getManager();
                $userDB = $entityManager->getRepository(User::class)->find($userOld->getId());
                $userDB->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ));
                $entityManager->flush();
                return $this->render('update/passwordSuccess.html.twig', [
                    'updatePasswordForm' => $form->createView(),
                ]);
            }
            else if ($form->get('plainPassword')->getData() !== $form->get('plainPasswordConfirmation')->getData()) // mdp confirmation différent
            {
                return $this->render('update/passwordErrorConfirmation.html.twig', [
                    'updatePasswordForm' => $form->createView(),
                ]);
            }
        }
        return $this->render('update/password.html.twig', [
            'updatePasswordForm' => $form->createView(),
        ]);
    }
}
