<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Reponse;
use App\Entity\Question;
use App\Entity\Categorie;
use App\Entity\Historique;
use App\Form\CreateQuizzType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuizzController extends AbstractController
{
    /**
     * @Route("/quizz", name="quizz")
     */
    public function index()
    {
        $categories = $this->getDoctrine()
        ->getRepository(Categorie::class)
        ->findAll();
        foreach($categories as $categorie)
        {
            $arrayCategorie[] = $categorie->getName();
            $arrayCategorieId[] = $categorie->getId();
        }
        return $this->render('quizz/index.html.twig', [
            'categories' => $arrayCategorie,
            'id_categories' => $arrayCategorieId]);
    }
    // $session = new Session();
        
    // if($session->start() !== true)
    //     $session->start();
    public function play(request $request, int $id = 1)
    {
        $session = $request->getSession();

        $postQuestion = $request->get('question');
        $postStartQuizz = $request->get('startQuizz');
        $postReponse = $request->get('reponse');
        $postAnwser = $request->get('anwser');

        $user = $this->get('security.token_storage')->getToken(); // ->getSecret()
        // Nom de la catégorie (thème)
        $repositoryCategorie = $this->getDoctrine()
        ->getRepository(Categorie::class)
        ->find($id);
        if ($repositoryCategorie == null)
            return new RedirectResponse('http://localhost:8080/quizz');  
        $categorie = $repositoryCategorie->getName();

        // Question
        $repositoryQuestion = $this->getDoctrine()
            ->getRepository(Question::class);

        $questions = $repositoryQuestion->findBy(['idCategorie' => $id]);
        if (!$_POST || isset($postStartQuizz))
        {
            $score = 0;
            $session->set('score', $score);
            $nbrQuestion = 0;
            $question = $questions[$nbrQuestion]->getQuestion();
            $questionId = $questions[$nbrQuestion]->getId();
        }
        elseif (isset($postQuestion))
        {
            $nbrQuestion = intval($postQuestion) + 1;
            if (!isset($postReponse) && $nbrQuestion !== count($questions)) // affiche la nouvelle question
            {
                $question = $questions[$nbrQuestion]->getQuestion();
                $questionId = $questions[$nbrQuestion]->getId();
            }
            elseif(!isset($postReponse) && $nbrQuestion == count($questions)) // partie terminé résultat
            {
                $score = $session->get('score');
                $connectedAnnonym = $user->getUser() == 'anon.';
                if($connectedAnnonym == true)
                {
                    $entityManager = $this->getDoctrine()->getManager();
                    $CategorieInstance =  $entityManager->getRepository(Categorie::class)->find($id);

                    $historique = new Historique();
                    $historique->setSecret($this->get('security.token_storage')->getToken()->getSecret());
                    $historique->setCategorieId($id);
                    $historique->setScore($score.'/'.$nbrQuestion);
                    $historique->setDate(new \DateTime());
                    $historique->setCategorie($CategorieInstance);

                    $entityManager->persist($historique);
                    $entityManager->flush();
                }
                else
                {
                    $entityManager = $this->getDoctrine()->getManager();
                    $UserInstance = $this->get('security.token_storage')->getToken()->getUser();
                    $CategorieInstance =  $entityManager->getRepository(Categorie::class)->find($id);

                    $historique = new Historique();
                    $historique->setUserId(intval($user->getUser()->getId()));
                    $historique->setCategorieId($id);
                    $historique->setScore($score.'/'.$nbrQuestion);
                    $historique->setDate(new \DateTime());
                    $historique->setUser($UserInstance);
                    $historique->setCategorie($CategorieInstance);

                    $entityManager->persist($historique);
                    $entityManager->flush();
                }
                if ($score/$nbrQuestion >= 0.5)
                {
                    return $this->render('quizz/endsuccess.html.twig', [
                        'categorie' => $categorie,
                        'nbr_question' => $nbrQuestion,
                        'score' => $score
                    ]);
                }
                else
                {
                    return $this->render('quizz/endfail.html.twig', [
                        'categorie' => $categorie,
                        'nbr_question' => $nbrQuestion,
                        'score' => $score
                    ]);
                }
            }
            else // affiche la correction de la question
            {
                $nbrQuestion = $nbrQuestion - 1;
                $question = $questions[$nbrQuestion]->getQuestion();
                $questionId = $questions[$nbrQuestion]->getId();
                $reponsesVR = $this->getDoctrine()
                ->getRepository(Reponse::class);
                $reponsesV = $reponsesVR->findBy(['idQuestion' => $questionId]);
                foreach($reponsesV as $reponseV)
                {
                    if($reponseV->getReponseExpected() == true)
                        $successReponse = $reponseV->getReponse();
                }
              
                if($postAnwser == $successReponse)
                {
                    $score = $session->get('score');
                    $score++;
                    $session->set('score', $score);
                    return $this->render('quizz/success.html.twig', [
                        'id' => $id,
                        'id_question' => $questionId,
                        'nbr_question' => $nbrQuestion,
                        'categorie' => $categorie,
                        'question' => $question,
                        'succes_anwser' => $successReponse
                    ]);
                }
                else
                {
                    return $this->render('quizz/fail.html.twig', [
                        'id' => $id,
                        'id_question' => $questionId,
                        'nbr_question' => $nbrQuestion,
                        'categorie' => $categorie,
                        'question' => $question,
                        'succes_anwser' => $successReponse
                    ]);
                }
            }
            count($questions);
        }
        // Réponse
        $repositoryReponse = $this->getDoctrine()
        ->getRepository(Reponse::class);
        $reponses = $repositoryReponse->findBy(['idQuestion' => $questionId]);
        shuffle($reponses);
        $verifReponses = $reponses;
        $firstAnwser = $reponses[0]->getReponse();
        $secondAnwser = $reponses[1]->getReponse();
        $thirdAnwser = $reponses[2]->getReponse();

        return $this->render('quizz/play.html.twig', [
            'id' => $id,
            'id_question' => $questionId,
            'nbr_question' => $nbrQuestion,
            'categorie' => $categorie,
            'question' => $question,
            'first_anwser' => $firstAnwser,
            'second_anwser' => $secondAnwser,
            'third_anwser' => $thirdAnwser
        ]);
    }
    public function create(request $request)
    {

        $postCreateQuizz = $request->get('create_quizz');
        $postForm = $request->get('form');
        $session = $request->getSession();

        if(!isset($postCreateQuizz) && !isset($postForm))
        {
            $nbr = 0;
            $session->set('nbr', $nbr);
            $form = $this->createForm(CreateQuizzType::class);
            return $this->render('quizz/create/first.html.twig', [
                'CreateQuizzForm' => $form->createView()
            ]);
        }
        elseif($session->get('choice') !== null && $session->get('nbr') == $session->get('choice'))
        {
            $nbrQuestion = $session->get('nbr') + 1;
            $Question = $nbrQuestion - 1;
            $session->set("Question$Question", $postForm['question']);
            $session->set("anwser$Question", $postForm['anwser']);
            $session->set("2anwser$Question", $postForm['anwser2']);
            $session->set("3anwser$Question", $postForm['anwser3']);

            $entityManager = $this->getDoctrine()->getManager();
            $categorie = new Categorie();
            $categorie->setName($session->get('nameCategorie'));
            $entityManager->persist($categorie);
            $entityManager->flush();
            $repositoryCategorie = $this->getDoctrine()
            ->getRepository(Categorie::class);
            $idCategorie = $repositoryCategorie->findBy(array(),array('id'=>'DESC'),1,0)[0]->getId();
            $CategorieInstance =  $entityManager->getRepository(Categorie::class)->find($idCategorie);

            for ($i = 1; $i !== $session->get('choice')+1; $i++)
            {
                $entityManager = $this->getDoctrine()->getManager();
                $question = new Question();
                $question->setQuestion($session->get("Question$i"));
                $question->setIdCategorie($idCategorie);
                $question->setCategorie($CategorieInstance);

                $entityManager->persist($question);
                $entityManager->flush();
                $repositoryQuestion = $this->getDoctrine()
                ->getRepository(Question::class);
                $idQuestion = $repositoryQuestion->findBy(array(),array('id'=>'DESC'),1,0)[0]->getId();
                $QuestionInstance =  $entityManager->getRepository(Question::class)->find($idQuestion);

                $reponse = new Reponse();
                $reponse->setIdQuestion($idQuestion);
                $reponse->setReponse($session->get("anwser$i"));
                $reponse->setReponseExpected(true);
                $reponse->setQuestion($QuestionInstance);
                $entityManager->persist($reponse);
                $entityManager->flush();

                $reponse = new Reponse();
                $reponse->setIdQuestion($idQuestion);
                $reponse->setReponse($session->get("2anwser$i"));
                $reponse->setReponseExpected(false);
                $reponse->setQuestion($QuestionInstance);
                $entityManager->persist($reponse);
                $entityManager->flush();

                $reponse = new Reponse();
                $reponse->setIdQuestion($idQuestion);
                $reponse->setReponse($session->get("3anwser$i"));
                $reponse->setReponseExpected(false);
                $reponse->setQuestion($QuestionInstance);
                $entityManager->persist($reponse);
                $entityManager->flush();
            }
            return $this->render('quizz/create/third.html.twig', [
                'id' => $idCategorie
            ]);
        }
        else
        {
            if(isset($postCreateQuizz))
            {
                $session->set('choice', intval($postCreateQuizz['choice']));
                $session->set('nameCategorie', $postCreateQuizz['name']);
            }

            $nbrQuestion = $session->get('nbr') + 1;
            $session->set('nbr', $nbrQuestion);

            if(isset($postForm))
            {   
                $Question = $nbrQuestion - 1;
                $session->set("Question$Question", $postForm['question']);
                $session->set("anwser$Question", $postForm['anwser']);
                $session->set("2anwser$Question", $postForm['anwser2']);
                $session->set("3anwser$Question", $postForm['anwser3']);
            }

            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('app_quizz_create'))
                ->add('question', TextType::class, ['label' => "Question $nbrQuestion"])
                ->add('anwser', TextType::class, ['label' => "Correct anwser"])
                ->add('anwser2', TextType::class, ['label' => "False anwser"])
                ->add('anwser3', TextType::class, ['label' => "False anwser"])
                ->getForm();
            return $this->render('quizz/create/second.html.twig', [
                'name' => $session->get('nameCategorie'),
                'CreateQRForm' => $form->createView()
            ]);
        }
       

    }
}
