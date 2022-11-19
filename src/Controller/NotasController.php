<?php

namespace App\Controller;


use App\Entity\Nota;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\NotaselectType;
use App\Form\NotaType;
use App\Form\TagType;
use App\Repository\NotaRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotasController extends AbstractController
{
    private NotaRepository $notaRepository;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    public function __construct(NotaRepository $notaRepository,
                                EntityManagerInterface $em,
                                UserRepository $userRepository)
    {
        $this->notaRepository=$notaRepository;
        $this->em=$em;
        $this->userRepository=$userRepository;
    }


    /**
     * @Route("/crearNota", name="nota.crear")
     */
    public function crearNota(Request $request): Response
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        $nota = new Nota();
        $nota->setUser($user);
        $nota->setFecha(new \DateTime());
        $nota->setIseliminada(false);
        //$form = $this->createForm(NotaType::class,$nota);
        $form = $this->createForm(NotaselectType::class,$nota);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($nota);

            $this->em->flush();
            $this->em->refresh($nota);
            return $this->redirectToRoute('nota.listado',[
                'id' =>$user->getId()
            ]);
        }
        return $this->render('notas/crearNota.html.twig',[
            'form' => $form->createView(),
            'id' => $user->getId()
        ]);
    }

    /**
     * @Route("/editarNota/{id}", name="nota.editar")
     */
    public function editarNota(Request $request,Nota $nota): Response
    {
        /*$form = $this->createForm(NotaType::class,$nota);*/
        $form = $this->createForm(NotaselectType::class,$nota);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($nota);

            $this->em->flush();
            $this->em->refresh($nota);
            return $this->redirectToRoute('nota.listado',[
                'id' =>$nota->getUser()->getId()
            ]);
        }
        return $this->render('notas/editarNota.html.twig',[
            'form' => $form->createView(),
            'id' => $nota->getUser()->getId()
        ]);
    }

    /**
     * @Route("/notas", name="nota.listado")
     */
    public function listadoNotas(): Response
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        $result = array_merge($this->notaRepository->findPublicas($user),$this->notaRepository->findNotasByUser($user));
        return $this->render('notas/listadoNotas.html.twig',[
            'notas' => $result,
            'id' =>$user->getId()
        ]);
    }

    /**
     * @Route("/crearTag", name="tag.crear")
     */
    public function crearTag(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class,$tag);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($tag);

            $this->em->flush();
            $this->em->refresh($tag);
            return $this->redirectToRoute('nota.listado');
        }
        return $this->render('notas/crearTag.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/eliminarNota/{id}", name="nota.eliminar")
     */
    public function eliminarNota(Nota $nota): Response
    {
        $nota->setIseliminada(true);
        $nota->setFechaeliminada(new \DateTime());
        $this->em->persist($nota);
        $this->em->flush();
        return $this->redirectToRoute('nota.listado',[
            'id' => $nota->getUser()->getId()
        ]);
    }

    /**
     * @Route("/notasEliminadas", name="nota.eliminadas.listado")
     */
    public function notasEliminadas(): Response
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        return $this->render('notas/listadoNotasEliminadas.html.twig',[
            'notas' => $this->notaRepository->findNotasEliminadas($user)
        ]);
    }

    /**
     * @Route("/rescatarNota/{id}", name="nota.rescatar")
     */
    public function notaRescatar(Nota $nota): Response
    {
        $nota->setIseliminada(false);
        $this->em->persist($nota);
        $this->em->flush();
        return $this->redirectToRoute('nota.eliminadas.listado',[
            'id' => $nota->getUser()->getId()
        ]);
    }

    /**
     * @Route("/notasPublicas", name="nota.publicas.listado")
     */
    public function notasPublicas(): Response
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        return $this->render('notas/listadoNotasPublicas.html.twig',[
            'notas' => $this->notaRepository->findNotasPublicasDeOtrosUsuarios($user)
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile", name="profile")
     */
    public function profile()
    {
        $user = [
            'correo' => $this->getUser()->getUsername()
        ];
        return $this->json([
            'user' => $user
        ]);
    }

}
