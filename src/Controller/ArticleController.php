<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage() {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/news/{slug}", name="app_news")
     */
    public function news($slug) {
        $comments = [
            "first test comment",
            "second test comment #2",
            "3rd comment in test array"
        ];

        return $this->render('article/show.html.twig', [
            'title' => ucfirst($slug),
            'comments' => $comments
        ]);
    }
}
