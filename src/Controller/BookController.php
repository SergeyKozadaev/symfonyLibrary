<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookAddFormType;
use App\Form\BookEditFormType;
use App\Repository\BookRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends Controller
{
    private function getBookById($id, EntityManagerInterface $em)
    {
        $repository = $em->getRepository(Book::class);

        return $repository->findOneBy(['id' => $id]);
    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function list(TagAwareAdapter $cache, Request $request, BookRepository $repository, PaginatorInterface $paginator)
    {
        $pageNumber = $request->query->getInt('page', 1);
        $cacheItem = $cache->getItem('page_'.$pageNumber);

        if (!$cacheItem->isHit()) {
            $books = $paginator->paginate(
                $repository->getFindAllQuery(),
                $pageNumber,
                $this->getParameter('app.items_per_page')
            );

            $cacheItem->set($books);
            $cacheItem->tag($this->getParameter('app.list_cache_key'));
            $cache->save($cacheItem);
        } else {
            $books = $cacheItem->get();
        }

        return $this->render('book/list.html.twig', [
            'books' => $books,
        ]);
    }

    /**
     * @Route("/books/add", name="app_books_add")
     * @IsGranted("ROLE_USER")
     */
    public function add(TagAwareAdapter $cache, Request $request, EntityManagerInterface $em, FileUploader $fileUploader)
    {
        $form = $this->createForm(BookAddFormType::class, null, ['validation_groups' => ['new']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Book $book */
            $book = $form->getData();

            if ($file = $book->getFile()) {
                $fileName = $fileUploader->upload($file, $this->getParameter('app.public_directory').$this->getParameter('app.files_directory'));
                $book->setFile($fileName);
            }

            if ($image = $book->getCoverImage()) {
                $imageName = $fileUploader->upload($image, $this->getParameter('app.public_directory').$this->getParameter('app.images_directory'));
                $book->setCoverImage($imageName);
            }

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', 'Еще одна книга прочитана, отличная работа!');

            $cache->invalidateTags([$this->getParameter('app.list_cache_key')]);

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/books/{id}/edit", name="app_books_edit")
     * @IsGranted("ROLE_USER")
     */
    public function edit($id, TagAwareAdapter $cache, Book $book, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(BookEditFormType::class, $book, ['validation_groups' => ['edit']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();

            $this->addFlash('success', 'Изменения внесены успешно!');

            $cache->invalidateTags([$this->getParameter('app.list_cache_key')]);

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('book/edit.html.twig', [
            'bookForm' => $form->createView(),
            'id' => $id,
        ]);
    }

    /**
     * @Route("/books/{id}/delete", name="app_books_delete_book", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function deleteBook($id, TagAwareAdapter $cache, Request $request, EntityManagerInterface $em)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['result' => 'error', 'message' => 'need AJAX request']);
        } elseif (!$book = $this->getBookById($id, $em)) {
            return new JsonResponse(['result' => 'error', 'message' => 'no such book']);
        } else {
            $message = 'Книга: "'.$book->getTitle().'" успешно удалена!';

            $em->remove($book);
            $em->flush();

            $cache->invalidateTags([$this->getParameter('app.list_cache_key')]);

            $this->addFlash('success', $message);

            return new JsonResponse(['result' => 'success']);
        }
    }

    /**
     * @Route("/books/{id}/delete_file", name="app_books_delete_file", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function deleteFile($id, TagAwareAdapter $cache, Request $request, Filesystem $filesystem, EntityManagerInterface $em)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['result' => 'error', 'message' => 'need AJAX request']);
        } elseif (!$book = $this->getBookById($id, $em)) {
            return new JsonResponse(['result' => 'error', 'message' => 'no such book']);
        } else {
            $fileSrc = $this->getParameter('files_directory').$book->getFile();

            if (!$filesystem->exists($fileSrc)) {
                return new JsonResponse(['result' => 'error', 'message' => 'no file found']);
            } else {
                $filesystem->remove($fileSrc);

                $book->setFile(null);
                $book->setDownloadable(false);
                $em->persist($book);
                $em->flush();

                $cache->invalidateTags([$this->getParameter('app.list_cache_key')]);

                $this->addFlash('success', 'Текстовый файл книги: "'.$book->getTitle().'" был успешно удален!');

                return new JsonResponse(['result' => 'success']);
            }
        }
    }

    /**
     * @Route("/books/{id}/delete_image", name="app_books_delete_image", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function deleteCoverImage($id, TagAwareAdapter $cache, Request $request, Filesystem $filesystem, EntityManagerInterface $em)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['result' => 'error', 'message' => 'need AJAX request']);
        } elseif (!$book = $this->getBookById($id, $em)) {
            return new JsonResponse(['result' => 'error', 'message' => 'no such book']);
        } else {
            $imageSrc = $this->getParameter('images_directory').$book->getCoverImage();

            if (!$filesystem->exists($imageSrc)) {
                return new JsonResponse(['result' => 'error', 'message' => 'no image found']);
            } else {
                $filesystem->remove($imageSrc);

                $book->setCoverImage(null);
                $em->persist($book);
                $em->flush();

                $cache->invalidateTags([$this->getParameter('app.list_cache_key')]);

                $this->addFlash('success', 'Обложка книги: "'.$book->getTitle().'" была успешно удалена!');

                return new JsonResponse(['result' => 'success']);
            }
        }
    }
}
