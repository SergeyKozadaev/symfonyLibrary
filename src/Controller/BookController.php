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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        //$cache->deleteItem($this->getParameter("book_list_cache_name"));
        $cachedBooks = $cache->getItem($this->getParameter("book_list_cache_name"));

        if(!$cachedBooks->isHit()) {
            $books = $repository->findBy([], ['addedDate' => 'DESC']);

            $cachedBooks->expiresAfter(86400);
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
            $cache->deleteItem($this->getParameter("book_list_cache_name"));

            /** @var Book $book */
            $book = $form->getData();

            if($file = $book->getFile()) {
                $fileName = $fileUploader->upload($file, $this->getParameter('files_directory'));
                $book->setFile($fileName ?: null);
            };

            if($image = $book->getCoverImage()) {
                $imageName = $fileUploader->upload($image, $this->getParameter('images_directory'));
                $book->setCoverImage($imageName ?: null);
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
    public function edit($id, Request $request, EntityManagerInterface $em)
    {
        /*
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
        */
        $repository = $em->getRepository(Book::class);
        /** @var Book $book */
        $book = $repository->findOneBy(['id' => $id]);

        $form = $this->createForm(BookEditFormType::class, $book);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $cache = new FilesystemAdapter();
            $cache->deleteItem($this->getParameter("book_list_cache_name"));

            /** @var Book $update */
            $update = $form->getViewData();
            $book =

            dd($update);

            //$book = $form->getData();

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
     * @Route("/book/delete/{id}", name="app_delete_book", methods={"POST"})
     */
    public function bookDelete($id, Request $request, EntityManagerInterface $em)
    {
        if($request->isXmlHttpRequest()) {
            $repository = $em->getRepository(Book::class);
            /** @var Book $book */
            $book = $repository->findOneBy(['id' => $id]);

            $em->remove($book);
            $em->flush();

            $cache = new FilesystemAdapter();
            $cache->deleteItem($this->getParameter("book_list_cache_name"));

            $this->addFlash('success', "Изменения внесены успешно!");

            return new JsonResponse(["result" => "success"]);
        } else {
            return new JsonResponse(["result" => "error"]);
        }
    }

    /**
     * @Route("/book/file_delete/{id}", name="app_delete_file", methods={"POST"})
     */
    public function fileDelete($id, Request $request, Filesystem $filesystem, EntityManagerInterface $em)
    {
        if($request->isXmlHttpRequest()) {
            $repository = $em->getRepository(Book::class);
            /** @var Book $book */
            $book = $repository->findOneBy(['id' => $id]);

            $fileSrc = $this->getParameter("files_directory") . $book->getFile();

            if($filesystem->exists($fileSrc)) {
                $cache = new FilesystemAdapter();
                $cache->deleteItem($this->getParameter("book_list_cache_name"));

                $filesystem->remove($fileSrc);

                $book->setFile(null);
                $em->persist($book);
                $em->flush();

                $this->addFlash('success', "Изменения внесены успешно!");
            }
            return new JsonResponse(["result" => "success"]);
        } else {
            return new JsonResponse(["result" => "error"]);
        }
    }

    /**
     * @Route("/book/image_delete/{id}", name="app_delete_image", methods={"POST"})
     */
    public function coverImageDelete($id, Request $request, Filesystem $filesystem, EntityManagerInterface $em)
    {
        if($request->isXmlHttpRequest()) {
            $repository = $em->getRepository(Book::class);
            /** @var Book $book */
            $book = $repository->findOneBy(['id' => $id]);

            $imageSrc = $this->getParameter("images_directory") . $book->getCoverImage();

            if($filesystem->exists($imageSrc)) {
                $cache = new FilesystemAdapter();
                $cache->deleteItem($this->getParameter("book_list_cache_name"));

                $filesystem->remove($imageSrc);

                $book->setCoverImage(null);
                $em->persist($book);
                $em->flush();

                $this->addFlash('success', "Изменения внесены успешно!");
            }

            return new JsonResponse(["result" => "success"]);
        } else {
            return new JsonResponse(["result" => "error"]);
        }
    }
}