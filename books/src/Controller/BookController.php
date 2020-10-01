<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

/**
 * @Route("/")
 */
class BookController extends AbstractController
{

    /**
     * @Route("/", name="book_index", methods={"GET"})
     */
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
        ]);
    }

    /**
    * @Route("/search", name="book_search", methods={"GET"})
    */
      public function searchAction(Request $request, LoggerInterface $logger){
        $data = $request->query->get("search");

        $entityManager = $this->getDoctrine()->getManager();
        $book =$entityManager->getRepository(Book::class)->findOneBy(['title' => $data]);

        if(!is_null($book) ){
            return $this->redirectToRoute('book_show', array(
                                          'id' => $book->getId(),
                                          ));
        }else{
            return $this->render('book/searcherror.html.twig', [
                        ]);
        }
      }

    /**
    * @Route("/errorname", name="book_error", methods={"GET"})
    */
    public function errorname(): Response
        {
            return $this->render('book/errorname.html.twig', [
            ]);
        }

    /**
     * @Route("/new", name="book_new", methods={"GET","POST"})
     */
    public function new(Request $request, LoggerInterface $logger): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
    
      try{
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $exist =$entityManager->getRepository(Book::class)->findOneBy(['title' => $book->getTitle()]);
            if(is_null($exist)){
                $entityManager->persist($book);
                $entityManager->flush();
            }else{

            return $this->redirectToRoute('book_error');
             }
            return $this->redirectToRoute('book_index');

        }
      } catch (\Doctrine\DBAL\DBALException $e) {
            return $this->redirectToRoute('book_error');
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="book_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Book $book): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        try{

            if ($form->isSubmitted() && $form->isValid()) {

                $entityManager = $this->getDoctrine()->getManager();
                $exist =$entityManager->getRepository(Book::class)->findOneBy(['title' => $book->getTitle()]);

                if(is_null($exist)){
                $this->getDoctrine()->getManager()->flush();
                }else if($exist->getIsbn() != $book->getIsbn()) {
                     return $this->redirectToRoute('book_error');
                }
                $this->getDoctrine()->getManager()->flush();
            }
        }catch (\Doctrine\DBAL\DBALException $e) {
            return $this->redirectToRoute('book_error');
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Book $book): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('book_index');
    }
}
