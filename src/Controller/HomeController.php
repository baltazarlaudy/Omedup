<?php

namespace App\Controller;

use App\Repository\ActualiteRepository;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
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
     * @var Key
     */
    private $key;



    /**
     * @Route("/", name="home")
     * @param ActualiteRepository $actualite
     * @param Request $request
     * @return Response
     */
    public function index(ActualiteRepository $actualite, Request $request)
    {
        if($this->getUser()) {
            $id = $this->getUser()->getUsername();

            $secret = base64_encode($this->getParameter('mercure_secret_key'));


            $hubUrl = $this->getParameter('mercure.default_hub');
            $this->addLink($request, new Link('mercure', $hubUrl));
            //$key = InMemory::plainText($this->getParameter('mercure_secret_key'));

            $key = InMemory::base64Encoded($secret);
            $configuration = Configuration::forSymmetricSigner(new Sha256(), $key);


            $token = $configuration->builder()
                ->withClaim('mercure', ['subscribe' => [sprintf("/%s", $id)]])
                ->getToken($configuration->signer(), $configuration->signingKey())
                ->toString();

            $response = $this->render('home/home.html.twig', [
                'actualite' => $actualite->findLimit(6)
            ]);

            $now = new \DateTimeImmutable();
            $cookie = Cookie::create('mercureAuthorization')
                ->withValue($token)
                ->withExpires($now->modify('+2 hour'))
                ->withPath('/.well-known/mercure')
                ->withSecure(false)
                ->withHttpOnly(true)
                ->withSameSite('strict');
            $response->headers->setCookie($cookie);

            return $response;
        }
        return $this->render('home/home.html.twig', [
            'actualite' => $actualite->findLimit(6)
        ]);
    }
}
