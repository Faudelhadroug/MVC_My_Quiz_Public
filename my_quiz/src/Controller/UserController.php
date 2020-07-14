<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\Question;
use App\Entity\Categorie;
use App\Entity\Historique;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    public function home()
    {

        $categories = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAll();
        $arrayCategorie = [];
        $arrayCategorieId = [];
        foreach($categories as $categorie)
        {
            $arrayCategorie[] = $categorie->getName();
            $arrayCategorieId[] = $categorie->getId();
        }
        return $this->render('user/home.html.twig', [
            'categories' => $arrayCategorie,
            'id_categories' => $arrayCategorieId]);
    }
    public function account(MailerInterface $mailer)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $connected = $this->isGranted('ROLE_USER');
        if($connected == true && $user->getVerifiedAt() == null)
        {
            
            if(isset($_POST['sendEmail']))
            {
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $email = (new TemplatedEmail())
                    ->from('faudel.hadroug@epitech.eu')
                    ->to($user->getEmail())
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject('Time for Symfony Mailer!')
                    ->text('Sending emails is fun again!')
                    //->html('<p>See Twig integration for better HTML integration!</p>');
                    ->htmlTemplate('email_validation/mailValidation.html.twig');
        
                $mailer->send($email);
                return $this->render('user/accountWeSentYouALink.html.twig');
            }
            return $this->render('user/accountNeedVerification.html.twig');
        }
        elseif($connected !== true)
        {
            return new RedirectResponse('http://localhost:8080/login');  
        }
        else
        {
            return $this->render('user/account.html.twig');
        } 
    }
    public function historique()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if($user == 'anon.')
        {
            $historiques = $this->getDoctrine()
            ->getRepository(Historique::class)
            ->findBy(['secret' => $this->get('security.token_storage')->getToken()->getSecret()]);
            if ($historiques == [])
            {
                return $this->render('user/historiqueAnon.html.twig', [
                    "historiques" => $historiques,
                ]);
            }
            foreach($historiques as $Historique)
            {
                $arrayNameCategorie[] = $Historique->getCategorie()->getName();
                $arrayHistoriqueScore[] = $Historique->getScore();
                $arrayHistoriqueDate[] = $Historique->getDate();
                // $arrayHistoriqueId[] = $Historique->getId();
            }
            return $this->render('user/historiqueAnon.html.twig', [
                "historiques" => $historiques,
                "categories" => $arrayNameCategorie,
                "scores" => $arrayHistoriqueScore,
                "dates" => $arrayHistoriqueDate,
            ]);
        }
        else
        {
            $historiques = $this->getDoctrine()
            ->getRepository(Historique::class)
            ->findBy(['user_id' => $this->get('security.token_storage')->getToken()->getUser()->getId()]);
            if ($historiques == [])
            {
                return $this->render('user/historiqueUser.html.twig', [
                    "user" => $this->get('security.token_storage')->getToken()->getUser()->getUsername(),
                    "historiques" => $historiques,
                ]);
            }
            foreach($historiques as $Historique)
            {
                $arrayNameCategorie[] = $Historique->getCategorie()->getName();
                $arrayHistoriqueScore[] = $Historique->getScore();
                $arrayHistoriqueDate[] = $Historique->getDate();
                // $arrayHistoriqueId[] = $Historique->getId();
            }
            return $this->render('user/historiqueUser.html.twig', [
                "user" => $this->get('security.token_storage')->getToken()->getUser()->getUsername(),
                "historiques" => $historiques,
                "categories" => $arrayNameCategorie,
                "scores" => $arrayHistoriqueScore,
                "dates" => $arrayHistoriqueDate,
            ]);
        }
        
        return $this->render('user/historiqueUser.html.twig');
    }
}
