<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /**
     * @Route("book/show")
     */
    public function showAll(BookRepository $repository)
    {
        $books = $repository->findBy([], ['addedDate' => 'DESC']);

        return $this->render('book/showAll.html.twig', [
           'books' => $books
        ]);
    }

    /**
     * @Route("/book/new")
     */
    public function new(EntityManagerInterface $em, Request $request)
    {
        /*
        $book = new Book();
        $book->setTitle('TestBook#1')
            ->setAuthor('Author#1')
            ->setAddedDate(new \DateTime);

        $em->persist($book);
        $em->flush();

        return new Response(sprintf(
            'New book id: %d, title: %s, author: %s',
            $book->getId(),
            $book->getTitle(),
            $book->getAuthor()
        ));
        */

        $form = $this->createForm(BookFormType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }

        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView()
        ]);

    }

    /**
     * @Route("/book/show/{id}")
     */
    public function show($id, EntityManagerInterface $em)
    {
        $repository = $em->getRepository(Book::class);
        /** @var Book $book */
        $book = $repository->findOneBy(['id' => $id]);

        if(!$book) {
            throw $this->createNotFoundException(sprintf('No book for id = %d', $id));
        }

        //dump($book);

        return $this->render('book/show.html.twig', [
            'book' => $book
        ]);
    }


}