<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookController extends AbstractController
{
	/** @var EntityManagerInterface */
	private $manager;

	public function __construct(EntityManagerInterface $manager)
	{
		$this->manager = $manager;
	}

	/**
	 * @Route("/api/books", name="books", methods={"GET"})
	 * @param BookRepository $bookRepository
	 *
	 * @return Response
	 */
	public function index(BookRepository $bookRepository): Response
	{
		$books = $bookRepository->findAll();
		return $this->json($books);
	}

	/**
	 * @Route("/api/books/{id}", name="book", methods={"GET"})
	 * @param int            $id
	 * @param BookRepository $bookRepository
	 *
	 * @return Response
	 */
	public function getBook(int $id, BookRepository $bookRepository): Response
	{
		$book = $bookRepository->find($id);
		return $this->json($book);
	}


	/**
	 * @Route("/api/books/add", name="add-book", methods={"PUT"})
	 * @param Request             $request
	 * @param SerializerInterface $serializer
	 * @param ValidatorInterface  $validator
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function addBook(Request $request, SerializerInterface $serializer, ValidatorInterface $validator)
	{
		$jsonData = $request->getContent();

		try {
			$book = $serializer->deserialize($jsonData, Book::class, 'json');
			$book->setCreatedAt(new \DateTime());

			$errors = $validator->validate($book);

			if (count($errors) > 0) {
				return $this->json($errors, 400);
			}

			$this->manager->persist($book);
			$this->manager->flush();

			return $this->json($book, 201, [], ['groups' => 'book:read']);
		} catch (NotEncodableValueException $exception) {
			return $this->json([
				'status' => 201,
				'message' => $exception->getMessage()
			], 400);
		}
	}

	/**
	 * @Route("/api/books/edit/{id}", name="edit-book", methods={"PUT"})
	 * @param Request             $request
	 * @param BookRepository      $bookRepository
	 * @param SerializerInterface $serializer
	 * @param ValidatorInterface  $validator
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function editBook(Book $book, Request $request, BookRepository $bookRepository, SerializerInterface $serializer, ValidatorInterface $validator)
	{
		$jsonData = $request->getContent();

		try {
			/** @var Book $newBook */
			$newBook = $serializer->deserialize($jsonData, Book::class, 'json');
			$errors = $validator->validate($newBook);
			if (count($errors) > 0) {
				return $this->json($errors, 400);
			}

			$book->setCode($newBook->getCode());
			$book->setName($newBook->getName());
			$book->setAuthor($newBook->getAuthor());
			$book->setStatus($newBook->getStatus());

			$this->manager->persist($book);
			$this->manager->flush();

			return $this->json($book, 201, [], ['groups' => 'book:read']);
		} catch (NotEncodableValueException $exception) {
			return $this->json([
				'status' => 201,
				'message' => $exception->getMessage()
			], 400);
		}
	}


	/**
	 * @Route("/api/books/delete/{id}", name="delete-book", methods={"DELETE","GET"})
	 * @param Book $book
	 *
	 */
	public function deleteBook(Book $book)
	{
		try {
			$this->manager->remove($book);
			$this->manager->flush();
			return $this->json([
				'status' => 201,
			]);
		} catch (NotEncodableValueException $exception) {
			return $this->json([
				'status' => 400,
				'message' => $exception->getMessage()
			], 400);
		}
	}

}
