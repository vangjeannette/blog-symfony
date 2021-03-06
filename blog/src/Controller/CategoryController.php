<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return Response A response
     */
    public function addNewCategory(Request $request): Response
    {
        $category = new Category();
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data= $form->getData();
            $categoryManager = $this->getDoctrine()->getManager();
            $categoryManager->persist($data);
            $categoryManager->flush();
            return $this->redirectToRoute('category');

        }

        $this->render('category/index.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories,
    ]);
        return $this->render('category/index.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

}
