<?php

namespace App\Controller;

use App\Repository\ActualiteRepository;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param ActualiteRepository $actualite
     * @param Request $request
     * @return Response
     */
    public function index(ActualiteRepository $actualite, Request $request)
    {

        return $this->render('home/home.html.twig', [
            'actualite' => $actualite->findLimit(6)
        ]);
    }
}
