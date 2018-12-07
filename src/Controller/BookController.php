<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookAddFormType;
use App\Form\BookEditFormType;
use App\Repository\BookRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
        $cache = new FilesystemAdapter();
        //$cache->deleteItem('list_of_books');
        $cachedBooks = $cache->getItem('list_of_books');

        if(!$cachedBooks->isHit()) {
            $books = $repository->findBy([], ['addedDate' => 'DESC']);

            $cachedBooks->expiresAfter(600);
            $cachedBooks->set($books);
            $cache->save($cachedBooks);
        } else {
            $books = $cachedBooks->get();
        }

        return $this->render('book/list.html.twig', [
           'books' => $books
        ]);
    }

    /**
     * @Route("/book/new", name="app_new_book")
     */
    public function new(Request $request, EntityManagerInterface $em, FileUploader $fileUploader)
    {
        $form = $this->createForm(BookAddFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $cache = new FilesystemAdapter();
            $cache->deleteItem('list_of_books');

            /** @var Book $book */
            $book = $form->getData();

            if($file = $book->getFile()) {
                $fileName = $fileUploader->upload($file, $this->getParameter('files_directory'));
                $book->setFile($fileName);
            };

            if($image = $book->getCoverImage()) {
                $imageName = $fileUploader->upload($image, $this->getParameter('images_directory'));
                $book->setCoverImage($imageName);
            }

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', "Еще одна книга прочитана, отличная работа!");

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/book/edit/{id}", name="app_edit_book")
     */
    public function edit($id, Book $book, Request $request, EntityManagerInterface $em)
    {

        if($book->getCoverImage() !== null) {
            $book->setCoverImage(
                new File($this->getParameter('images_directory') . $book->getCoverImage())
            );
        }

        if($book->getFile() !== null) {
            $book->setFile(
                new File($this->getParameter('files_directory') . $book->getFile())
            );
        }

        $form = $this->createForm(BookEditFormType::class, $book);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $cache = new FilesystemAdapter();
            $cache->deleteItem('list_of_books');

            /** @var Book $book */
            $book = $form->getData();

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', "Изменения внесены успешно!");

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('book/edit.html.twig', [
            'bookForm' => $form->createView(),
            'id' => $id
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

    /**
     * @Route("/book/delete/{id}", name="app_delete_book")
     */
    public function delete(Book $book, EntityManagerInterface $em)
    {
        $em->remove($book);
        $em->flush();

        $cache = new FilesystemAdapter();
        $cache->deleteItem('list_of_books');

        return $this->redirectToRoute('app_homepage');
    }
}