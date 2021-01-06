<?php

namespace App\Controller\Users;

use App\Entity\User;
use App\Repository\ProfilRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     * @Route("/{slug}", name="_profil")
     * @param User $user
     * @return Response
     */
    public function userProfil(User $user){
        return $this->render('user/Profil/user_profil.html.twig',[
            compact('user')
        ]);
    }
}
