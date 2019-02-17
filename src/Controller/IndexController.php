<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $a = 'Hello';
        $p = password_hash("pero336", PASSWORD_DEFAULT);
        return $this->render('home/index.html.twig', [
            'a' => $a,
            'p' => $p
        ]);
    }

}