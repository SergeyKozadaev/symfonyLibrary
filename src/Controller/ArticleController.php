<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
    public function news($slug, LoggerInterface $logger, SessionInterface $session, Request $request) {
        $comments = [
            "first test comment",
            "second test comment #2",
            "3rd comment in test array"
        ];

        $logger->info('news article controller');

        $this->addFlash('notice', 'message string');

        $session->set("name", "value");


        if($slug == "error") {
            throw $this->createNotFoundException("Not found exception");
        }


        return $this->render('article/show.html.twig', [
            'title' => ucfirst($slug),
            'comments' => $comments,
            'name' => $session->get("name", ""),
            'request' => $request
        ]);
    }
}
