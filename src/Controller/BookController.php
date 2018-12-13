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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    private function checkUserAuthorisation()
    {
        if(!$this->isGranted('ROLE_USER')) {
            $this->addFlash('warning', "Доступ для неавторизованного пользователя запрещен! Пожалуйста, авторизуйтесь.");
            throw $this->createAccessDeniedException();
        } else {
            return true;
        }
    }

    private function clearCacheByKey(string $key)
    {
        $cache = new FilesystemAdapter();
        $cache->deleteItem($key);
    }

    private function getBookById($id, EntityManagerInterface $em){
        $repository = $em->getRepository(Book::class);
        return $repository->findOneBy(['id' => $id]);
    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function list(BookRepository $repository)
    {
        $cache = new FilesystemAdapter();
        $cachedBooks = $cache->getItem($this->getParameter("list_cache_key"));

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
        $this->checkUserAuthorisation();

        $form = $this->createForm(BookAddFormType::class, null, ["validation_groups" => ["new"]]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Book $book */
            $book = $form->getData();

            if($file = $book->getFile()) {
                $fileName = $fileUploader->upload($file, $this->getParameter('files_directory'));
                $book->setFile($fileName );
            };

            if($image = $book->getCoverImage()) {
                $imageName = $fileUploader->upload($image, $this->getParameter('images_directory'));
                $book->setCoverImage($imageName );
            }

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', "Еще одна книга прочитана, отличная работа!");

            $this->clearCacheByKey($this->getParameter("list_cache_key"));

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
        $this->checkUserAuthorisation();

        if(!$book = $this->getBookById($id, $em)) {
            throw $this->createNotFoundException(sprintf('Ошибка! Книга с id = %d не найдена.', $id));
        }

        $form = $this->createForm(BookEditFormType::class, $book, ["validation_groups" => ["edit"]]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();

            $this->addFlash('success', "Изменения внесены успешно!");

            $this->clearCacheByKey($this->getParameter("list_cache_key"));

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('book/edit.html.twig', [
            'bookForm' => $form->createView(),
            'id' => $id
        ]);
    }

    /**
     * @Route("/book/delete/{id}", name="app_delete_book", methods={"POST"})
     */
    public function bookDelete($id, Request $request, EntityManagerInterface $em)
    {
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse(["result" => "error", "message" => "need AJAX request"]);
        } elseif(!$book = $this->getBookById($id, $em)) {
            return new JsonResponse(["result" => "error", "message" => "no such book"]);
        } else {
            $message = "Книга: \"". $book->getTitle() ."\" успешно удалена!";

            $em->remove($book);
            $em->flush();

            $this->clearCacheByKey($this->getParameter("list_cache_key"));

            $this->addFlash('success', $message);

            return new JsonResponse(["result" => "success"]);
        }
    }

    /**
     * @Route("/book/file_delete/{id}", name="app_delete_file", methods={"POST"})
     */
    public function fileDelete($id, Request $request, Filesystem $filesystem, EntityManagerInterface $em)
    {
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse(["result" => "error", "message" => "need AJAX request"]);
        } elseif(!$book = $this->getBookById($id, $em)) {
            return new JsonResponse(["result" => "error", "message" => "no such book"]);
        } else {
            $fileSrc = $this->getParameter("files_directory") . $book->getFile();

            if(!$filesystem->exists($fileSrc)) {
                return new JsonResponse(["result" => "error", "message" => "no file found"]);
            } else {
                $filesystem->remove($fileSrc);

                $book->setFile(null);
                $book->setDownloadable(false);
                $em->persist($book);
                $em->flush();

                $this->clearCacheByKey($this->getParameter("list_cache_key"));

                $this->addFlash('success', "Текстовый файл книги: \"" . $book->getTitle() . "\" был успешно удален!");

                return new JsonResponse(["result" => "success"]);
            }
        }
    }

    /**
     * @Route("/book/image_delete/{id}", name="app_delete_image", methods={"POST"})
     */
    public function coverImageDelete($id, Request $request, Filesystem $filesystem, EntityManagerInterface $em)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(["result" => "error", "message" => "need AJAX request"]);
        } elseif (!$book = $this->getBookById($id, $em)) {
            return new JsonResponse(["result" => "error", "message" => "no such book"]);
        } else {
            $imageSrc = $this->getParameter("images_directory") . $book->getCoverImage();

            if (!$filesystem->exists($imageSrc)) {
                return new JsonResponse(["result" => "error", "message" => "no image found"]);
            } else {
                $filesystem->remove($imageSrc);

                $book->setCoverImage(null);
                $em->persist($book);
                $em->flush();

                $this->clearCacheByKey($this->getParameter("list_cache_key"));

                $this->addFlash('success', "Обложка книги: \"" . $book->getTitle() . "\" была успешно удалена!");

                return new JsonResponse(["result" => "success"]);
            }
        }
    }
}