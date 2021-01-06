<?php

namespace App\Controller\Users;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/dashboard", name="user")
     *  @Security("is_granted('ROLE_USER')")
     */
    public function index()
    {
        return $this->render('user/user_home.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
