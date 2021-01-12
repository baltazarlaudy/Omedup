<?php

namespace App\Controller\Users;

use App\Entity\User;
use App\Repository\ProfilRepository;
use App\Repository\UserRepository;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;

/**
 * @Route("/dashboard", name="user_")
 *  @Security("is_granted('ROLE_USER')")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var ProfilRepository
     */
    private ProfilRepository $profilRepository;

    public function __construct(UserRepository $userRepository,
                                ProfilRepository $profilRepository)
{
    $this->userRepository = $userRepository;
    $this->profilRepository = $profilRepository;
}

    /**
     * @Route("/", name="index", methods={"GET"})
     * @return Response
     */
    public function index()
    {
        $users = $this->userRepository->findAllOtherUser(
            $this->getUser()->getId(),
            3
        );
        $user = $this->userRepository->findUserById(
            $this->getUser()->getId()
        );

        return $this->render('user/user_home.html.twig', [
           'user' => $user,
            'users' =>$users
        ]);
    }

    /**
     * @Route("/{username}", name="_profil")
     * @param User $user
     * @return Response
     */
    public function userProfil(User $user, Request $request){
        $hubUrl = $this->getParameter('mercure.default_hub');
        $this->addLink($request, new Link('mercure', $hubUrl));

        $key = Key\InMemory::plainText($this->getParameter('mercure_secret_key')); // don't forget to set this parameter! Test value: !ChangeMe!
        $configuration = Configuration::forSymmetricSigner(new Sha256(), $key);

        $token = $configuration->builder()
            ->withClaim('mercure', ['subscribe' => ["/dashboard/{$this->getUser()->getUsername()}"]]) // can also be a URI template, or *
            ->getToken($configuration->signer(), $configuration->signingKey())
            ->toString();


        $response = $this->render('user/Profil/user_profil.html.twig',[
            compact('user')
        ]);


        $cookie = Cookie::create('mercureAuthorization')
            ->withValue($token)
            ->withPath('/.well-known/mercure')
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withSameSite('strict');
        $response->headers->setCookie($cookie);


        return $response;
    }
}
