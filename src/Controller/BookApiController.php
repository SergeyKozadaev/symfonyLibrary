<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\SerializerInterface;

class BookApiController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    private  function checkApiKey(Request $request)
    {
        return $request->get("key") === $this->getParameter("api_v1_key") ? true : false;
    }

    private function formJsonResponce($arData)
    {
        $dataSerialized = $this->serializer->serialize($arData, "json");
        $jsonDecode = new JsonDecode();
        return $jsonDecode->decode($dataSerialized, "json");
    }

    /**
     * @Route("/api/v1/books", name="api_v1_books")
     */
    public function books(Request $request, BookRepository $repository)
    {
        if(!$this->checkApiKey($request)) {
            $arData = [
                "status" => "error",
                "message" => "invalid api key"
            ];
        } else {
            $books = $repository->findBy([], ['addedDate' => 'DESC']);

            foreach ($books as $book) {
                if(!$book->isDownloadable()) {
                    $book->setFile(null);
                } elseif ($book->getFile()) {
                    $fileSrc = $this->getParameter('public_directory') . $this->getParameter('files_directory') . $book->getFile();
                    $book->setFile($fileSrc);
                }

                if($book->getCoverImage()) {
                    $coverImageSrc = $this->getParameter('public_directory') . $this->getParameter('images_directory') . $book->getCoverImage();
                    $book->setCoverImage($coverImageSrc);
                }
            }

            $arData = [
                "status" => "ok",
                "message" => $books
            ];
        }

        return new JsonResponse($this->formJsonResponce($arData));
    }

    /**
     * @Route("/api/v1/books/{id}/edit", name="api_v1_books_edit")
     */
    public function edit($id, $cache, Request $request, EntityManagerInterface $em)
    {
        if(!$this->checkApiKey($request)) {
            $arData = [
                "status" => "error",
                "message" => "invalid api key"
            ];
        } else {
            $repository = $em->getRepository(Book::class);
            /** @var Book $book */
            $book = $repository->findOneBy(['id' => $id]);

            if(!$book) {
                $arData = [
                    "status" => "error",
                    "message" => "no such book to edit"
                ];
            } else {
                if($title = $request->get("title")) {
                    $book->setTitle($title);
                }

                if($author = $request->get("author")) {
                    $book->setAuthor($author);
                }

                if($addedDate = $request->get("addedDate")) {
                    $book->setAddedDate($addedDate instanceof \DateTime ?: new \DateTime());
                }

                if($downloadable = $request->get("downloadable")) {
                    $book->setDownloadable($downloadable);
                }

                $em->persist($book);
                $em->flush();

                $cache->invalidateTags([$this->getParameter("list_cache_key")]);

                $arData = [
                    "status" => "success",
                    "message" => "The information has been updated."
                ];
            }
        }
        return new JsonResponse($this->formJsonResponce($arData));
    }

    /**
     * @Route("/api/v1/books/add", name="api_v1_books_add")
     */
    public function add($cache, Request $request, EntityManagerInterface $em)
    {
        if(!$this->checkApiKey($request)) {
            $arData = [
                "status" => "error",
                "message" => "invalid api key"
            ];
        } else {
            $author = $request->get("author");
            $title = $request->get("title");

            if (!$author || !$title ) {
                $arData = [
                    "status" => "error",
                    "message" => "no title and/or author parameters found in request"
                ];
            } else {
                $book = new Book();

                $book->setTitle($title);
                $book->setAuthor($author);

                $addedDate = $request->get("addedDate") instanceof \DateTime ?: new \DateTime();
                $book->setAddedDate($addedDate);

                $em->persist($book);
                $em->flush();

                $cache->invalidateTags([$this->getParameter("list_cache_key")]);

                $arData = [
                    "status" => "ok",
                    "message" => "new book has been added"
                ];
            }
        }
        return new JsonResponse($this->formJsonResponce($arData));
    }
}