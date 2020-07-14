<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SoutenanceController extends AbstractController
{

    public function soutenance()
    {
        return $this->render('soutenance/test.html.twig');
    }
}

