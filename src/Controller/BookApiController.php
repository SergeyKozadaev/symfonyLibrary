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
        $request = Request::create(
            '/api/v1/books',
            'POST',
            ["key" => "some_secret_key"]
        );

        if(!$this->checkApiKey($request)) {
            $arData = [
                "status" => "error",
                "message" => "invalid api key"
            ];
        } else {
            $books = $repository->findBy([], ['addedDate' => 'DESC']);

            foreach ($books as $book) {
                if($book->isDownloadable()) {
                    $book->setFile($this->getParameter('files_directory') . $book->getFile());
                } else {
                    $book->setFile(null);
                }

                $book->setCoverImage($this->getParameter('images_directory') . $book->getCoverImage());
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
    public function edit(Book $book, Request $request, EntityManagerInterface $em)
    {
        $request = Request::create(
            '/api/v1/books/42/edit',
            'POST',
            [
                "key" => "some_secret_key",
                "title" => "Тонкое искусство пофигизма",
                "author" => "",
                "addedDate" => "testing"
            ]
        );

        if(!$this->checkApiKey($request)) {
            $arData = [
                "status" => "error",
                "message" => "invalid api key"
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

            $em->persist($book);
            $em->flush();

            $arData = [
                "status" => "ok",
                "message" => "The information has been updated."
            ];
        }

        return new JsonResponse($this->formJsonResponce($arData));
    }

    /**
     * @Route("/api/v1/books/add", name="api_v1_books_add")
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $request = Request::create(
            '/api/v1/books/add',
            'POST',
            [
                "key" => "some_secret_key",
                "title" => "qwertyyy",
                "author" => "Новый автор",
                "addedDate" => "testing"
            ]
        );

        if(!$this->checkApiKey($request)) {
            $arData = [
                "status" => "error",
                "message" => "invalid api key"
            ];
        } elseif ($author = $request->get("author") && $title = $request->get("title")) {
            $book = new Book();

            $book->setTitle($title);
            $book->setAuthor($author);

            $addedDate = $request->get("addedDate") instanceof \DateTime ?: new \DateTime();
            $book->setAddedDate($addedDate);

            $em->persist($book);
            $em->flush();

            $arData = [
                "status" => "ok",
                "message" => "new book is added"
            ];
        } else {
            $arData = [
                "status" => "error",
                "message" => "no title and/or author parameters in your request"
            ];
        }

        return new JsonResponse($this->formJsonResponce($arData));
    }
}