<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookFormType;
use App\Repository\BookRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function list(BookRepository $repository)
    {
        $books = $repository->findBy([], ['addedDate' => 'DESC']);

        return $this->render('book/list.html.twig', [
           'books' => $books
        ]);
    }

    /**
     * @Route("/book/new", name="app_new_book")
     */
    public function new(Request $request, EntityManagerInterface $em, FileUploader $fileUploader)
    {
        $form = $this->createForm(BookFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var Book $book */
            $book = $form->getData();

            $file = $book->getFile();
            $image = $book->getCoverImage();

            $fileName = $fileUploader->upload($file, $this->getParameter('files_directory'));
            $imageName = $fileUploader->upload($image, $this->getParameter('images_directory'));

            $book->setCoverImage($imageName);
            $book->setFile($fileName);

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', "You are great!");

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/book/edit/{id}", name="app_edit_book")
     */
    public function edit(Book $book, Request $request, EntityManagerInterface $em)
    {
        if($book->getCoverImage() !== null) {
            $book->setCoverImage(
                new File($this->getParameter('images_directory').'/'.$book->getCoverImage())
            );
        }

        if($book->getFile() !== null) {
            $book->setFile(
                new File($this->getParameter('files_directory').'/'.$book->getFile())
            );
        }

        $form = $this->createForm(BookFormType::class, $book);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            /** @var Book $book */
            $book = $form->getData();

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', "You are great!");

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/book/show/{id}", name="app_show_book")
     */
    public function show($id, EntityManagerInterface $em)
    {
        $repository = $em->getRepository(Book::class);
        /** @var Book $book */
        $book = $repository->findOneBy(['id' => $id]);

        if(!$book) {
            throw $this->createNotFoundException(sprintf('No book for id = %d', $id));
        }

        return $this->render('book/show.html.twig', [
            'book' => $book
        ]);
    }
}