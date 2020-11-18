<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SpecialityController extends AbstractController
{
    /**
     * @Route("/speciality", name="speciality")
     */
    public function index()
    {
        return $this->render('speciality/index.html.twig', [
            'controller_name' => 'SpecialityController',
        ]);
    }
}
