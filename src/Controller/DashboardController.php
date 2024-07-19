<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;


class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(UserRepository $userRepo, CategoryRepository $catRepo): Response
    {

        if(!in_array('ROLE_ADMIN', $this->getUser()->getRoles() ) )
        {
            return $this->redirectToRoute('app_home');
        }


        return $this->render('dashboard/index.html.twig', [
            'users' => $userRepo->findAll(),
            'categories' => $catRepo->findAll()
        ]);
    }

    #[Route('dashboard/new-category', name: 'app_new-category')]
    #[Route('dashboard/edit-category/{category}', name: 'app_edit-category')]
    public function categoryForm( ?Category $category, Request $request, EntityManagerInterface $entityManager ): Response
    {

        $editMode = true;

        if ( !$category )
        {
            $editMode = false;
            $category = new Category();
        }

        // on fabrique un formulaire basé sur la classe CategoryType (fomrulaire type pour manipuler une entité Category)
        // et on donne à ce formulaire, une instance de l'entité Category pour qu'il gere celle ci.
        $categoryForm = $this->createForm( CategoryType::class, $category );

        // On demande au formulaire de regarder et utiliser ce qui vient par la requete HTTP
        // Ca lui permet de savoir si le formulaire est soumis, si les champs on été remplis correctement
        // et ca lui permet de remplir les propriété de l'entité Category qu'on lui à donné avec les valeur des champs correspondants à ces propriété.
        $categoryForm->handleRequest($request);
        

        if( $categoryForm->isSubmitted() && $categoryForm->isValid() )
        {
            // enregistrer la catégorie dans la DB

            // Créer l'objet
            $entityManager->persist($category);

            // Ajoute l'objet à la DB
            $entityManager->flush();

            return $this->redirectToRoute('app_dashboard');
        }   

        return $this->render('dashboard/category/form.html.twig', [
            'categoryForm' => $categoryForm,
            'textEdit'     => $editMode,
            'editButton'   => $editMode ? 'Update' : 'Add'

        ]);
    }

    #[Route('dashboard/delete-category/{category}', name: 'app_delete-category')]
    public function deleteCategory( ?Category $category, EntityManagerInterface $entityManager ): Response
    {
        if($category)
        {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dashboard');

    }

    
}
