<?php

namespace App\Controller;

use App\Entity\Actualite;
use App\Repository\ActualiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param ActualiteRepository $actualite
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ActualiteRepository $actualite)
    {
        return $this->render('home/home.html.twig', [
            'actualite' => $actualite->findLimit(6)
        ]);
    }
}
