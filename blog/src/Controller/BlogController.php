<?php
// src/Controller/BlogController.php
namespace App\Controller;


use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class BlogController extends AbstractController
{
    /**
     * Show all row from article's entity
     *
     * @Route("/articles", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();

        if (!$articles) {
            throw $this->createNotFoundException(
                'No article found in article\'s table.'
            );
        }

        return $this->render(
            'blog/index.html.twig',
            ['articles' => $articles]
        );
    }

    /**
     * Getting a article with a formatted slug for title
     *
     * @param string $slug The slugger
     *
     * @Route("/showarticle/{slug}",
     *     defaults={"slug" = null},
     *     name="blog_show")
     * @return Response A response instance
     */
    public function show(string $slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find an article in article\'s table.');
        }

        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );

        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article with ' . $slug . ' title, found in article\'s table.'
            );
        }

        return $this->render(
            'blog/show.html.twig',
            [
                'article' => $article,
                'slug' => $slug,
            ]
        );
    }

    /**
     * @Route("/blog/category/{categoryName}", name="blog_show_category")
     * @ParamConverter Category $category
     * @return Response
     */

    public function showByCategory(Category $category) : Response
    {
        $articles = $category->getArticles();

        return $this->render('blog/category.html.twig', [
            'articles' => $articles,
            'category' => $category
        ]);

    }

  //  public function showByCategory(string $categoryName) : Response
  //  {
    // ancienne fonction
     //   $category = $this->getDoctrine()
     //       ->getRepository(Category::class)
     //       ->findOneBy(['name' => mb_strtolower($categoryName)]);

    //    $articles = $this->getDoctrine()
    //        ->getRepository(Article::class)
    //        ->findBy(['category' => ($category)]);

     //   $articles = $category->getArticles();

      //  return $this->render(
      //      'blog/category.html.twig',
      //      [
      //          'category' => $category,
      //          'articles' => $articles
      //      ]);
   // }


    /**
     * @Route("blog/tag/{name}", name="tag")
     * @Paramconverter Tag $tag
     * @return Response A response
     */

        public function showTag(Tag $tag): Response
        {
            return $this->render('blog/tag.html.twig', [
                'tags' => $tag
            ]);
        }
}
