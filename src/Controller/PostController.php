<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Like;
use App\Entity\Post;
use App\Form\ComentsType;
use App\Repository\CommentsRepository;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
            'posts' => $postRepository->findAll()
        ]);
    }

    #[Route('post/{slug}', name: 'app_post_detail')]
    public function detail(Post $post, $slug, Request $request, ComentsType $comments, EntityManagerInterface $entityManager, CommentsRepository $com_rep): Response
    {
        $comment = new Comments();
        $form = $this->createForm(ComentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser())
                ->setPost($post);

            $entityManager->persist($comment);
            $entityManager->flush($comment);

            return $this->redirectToRoute('app_post_detail', [
                'slug' => $slug,
                
            ]);

        }

        return $this->render('post/unique.html.twig', [
            'comments' => $comment,
            'form' => $form->createView(),
            'post' => $post
        ]);
    }


    #[Route('post/{slug}/liked', name: 'app_like')]
    public function like(Post $post, $slug, EntityManagerInterface $entityManager, LikeRepository $likeRepository)
    {


        $like = new Like();

        $like->setUser($this->getUser())
            ->setPostId($post);


        $entityManager->persist($like);
        $entityManager->flush($like);

        return $this->redirectToRoute('app_post_detail', [
            'slug' => $slug,

        ]);
    }
}
