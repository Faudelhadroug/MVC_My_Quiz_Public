<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DateTimeInterface;

class EmailValidationController extends AbstractController
{
    public function checkToken(UserRepository $repository)
    {
        
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($user == "anon.")
        {
            return new RedirectResponse('http://localhost:8080/login');  
        }
        $token = substr($user->getToken(), 30, -40);

        $verified = $user->getVerifiedAt();
        if($verified == null && isset($_GET['token']))
        {
            $_GET['token'] = str_replace(' ', '', $_GET['token']);
            $token = str_replace('+', '', $token);

            if($_GET['token'] == $token)
            {
                $user->setVerifiedAt(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                
                return $this->render('email_validation/success.html.twig', [
                    'controller_name' => 'EmailValidationController',
                ]);
            }
            else
            {
                return new RedirectResponse('http://localhost:8080/account');    
            }
        }
        else
        {
            return new RedirectResponse('http://localhost:8080/account');    
        }    
    }
}
