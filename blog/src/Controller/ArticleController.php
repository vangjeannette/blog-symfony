<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\Slugify;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="article_new", methods={"GET","POST"})
     */
    public function new(Request $request, Slugify $slugify, \Swift_Mailer $mailer): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($article->getTitle());
            $article->setSlug($slug);
            $author = $this->getUser();
            $article->setAuthor($author);
            $entityManager->persist($article);
            $entityManager->flush();

            $mailContent = $this->renderView(
                'article/mail/notificationMail.html.twig',
                array('article' => $article)
            );

            $message = (new \Swift_Message('Un nouvel article vient d\'être publié !'))
                ->setFrom($this->getParameter('mailer_from'))
                ->setTo('legrandhotelorleans@gmail.com')
                ->setContentType('text/html')
                ->setBody($mailContent);

            $mailer->send($message);

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_show", methods={"GET"})
     */
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="article_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function edit(Request $request, Article $article, Slugify $slugify): Response
    {
        $user = $this->getUser();
        $author = $article->getAuthor();
        if ($user != $author && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas les droits" );
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($article->getTitle());
            $article->setSlug($slug);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_index', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"DELETE"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function delete(Request $request, Article $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_index');
    }
}
