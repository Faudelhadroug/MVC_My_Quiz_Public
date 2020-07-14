<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Categorie;
use App\Form\EditUserType;
use App\Form\EditCategorieType;
use App\Repository\UserRepository;
use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{

    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    public function usersList(UserRepository $users)
    {
        return $this->render('admin/index.html.twig', [
           'users' => $users->findAll()
        ]);
    }
    public function editUser(User $user, Request $request)
    {
        $form = $this->createform(EditUserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityMananger = $this->getDoctrine()->getManager();
            $entityMananger->persist($user);
            $entityMananger->flush();
        }
        return $this->render('admin/edituser.html.twig', [
            'userForm' => $form->createView()
        ]);
    }
    public function deleteUser(User $user, Request $request)
    {
        $entityMananger = $this->getDoctrine()->getManager();
        $entityMananger->remove($user);
        $entityMananger->flush();
        return new RedirectResponse('http://localhost:8080/admin');  
    }
    public function categoriesList(CategorieRepository $categories)
    {
        return $this->render('admin/categorie.html.twig', [
           'categories' => $categories->findAll()
        ]);
    }
    public function editCategorie(Categorie $categorie, Request $request)
    {
        $form = $this->createform(EditCategorieType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityMananger = $this->getDoctrine()->getManager();
            $entityMananger->persist($categorie);
            $entityMananger->flush();
        }
        return $this->render('admin/editcategorie.html.twig', [
            'CategorieForm' => $form->createView()
        ]);
    }
}
