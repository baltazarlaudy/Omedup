<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SpecialitiesController extends AbstractController
{
    /**
     * @Route("/specialities", name="specialities")
     */
    public function index()
    {
        return $this->render('specialities/index.html.twig', [
            'controller_name' => 'SpecialitiesController',
        ]);
    }
}
