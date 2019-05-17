<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class WelcomeController extends AbstractController
{

    /**
     * @Route("/", name="app_welcome")
     * @Template("welcome\index.html.twig")
     */
    public function indexAction(){

        return ['name' => 'WebMedic'];
    }

}