<?php

namespace App\Controller\Api;

use App\Entity\Nota;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\NotaType;
use App\Form\TagType;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Repository\NotaRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ApiController extends AbstractFOSRestController
{
    private NotaRepository $notaRepository;
    private UserRepository $userRepository;
    private TagRepository $tagRepository;
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $passwordEncoder;
    public function __construct(NotaRepository $notaRepository,
                                EntityManagerInterface $em,
                                TagRepository $tagRepository,
                                UserPasswordEncoderInterface $passwordEncoder,
                                UserRepository $userRepository)
    {
        $this->tagRepository=$tagRepository;
        $this->notaRepository=$notaRepository;
        $this->passwordEncoder=$passwordEncoder;
        $this->em=$em;
        $this->userRepository=$userRepository;
    }

    /**
     * @Rest\Get(path="/notas")
     * @Rest\View(serializerGroups={"nota"}, serializerEnableMaxDepthChecks=true)
     */
    public function notas()
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        $result = array_merge($this->notaRepository->findPublicas($user),$this->notaRepository->findNotasByUser($user));
        return $result;
    }
    /**
     * @Rest\Post(path="/notaCrear")
     * @Rest\View(serializerGroups={"nota"}, serializerEnableMaxDepthChecks=true)
     */
    public function crearNota(Request $request)
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        $nota = new Nota();
        $nota->setUser($user);
        $nota->setFecha(new \DateTime());
        $nota->setIseliminada(false);
        $form = $this->createForm(NotaType::class,$nota);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            foreach ($nota->getTags() as $key=> $tag) {
                $tag = $this->tagRepository->findOneBy(['titulo'=>$tag->getTitulo()]);
                if($tag){
                    $nota->getTags()->remove($key);
                    $nota->getTags()->add($tag);
                }
            }
            $this->em->persist($nota);

            $this->em->flush();
            $this->em->refresh($nota);
            return $nota;
        }

        return $form;
    }

    /**
     * @Rest\Post(path="/notaEditar/{id}")
     * @Rest\View(serializerGroups={"nota"}, serializerEnableMaxDepthChecks=true)
     */
    public function notaEditar(Request $request,Nota $nota)
    {
        foreach ($nota->getTags() as $key => $tag){
                $nota->getTags()->remove($key);
        }

        $form = $this->createForm(NotaType::class,$nota);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($nota);

            $this->em->flush();
            $this->em->refresh($nota);
            return $nota;
        }

        return $form;
    }

    /**
     * @Rest\Get(path="/notaEliminar/{id}")
     * @Rest\View(serializerGroups={"nota"}, serializerEnableMaxDepthChecks=true)
     */
    public function notaEliminar(Nota $nota)
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        if($nota->getUser()->getId() == $user->getId()) {
            $nota->setIseliminada(true);
            $nota->setFechaeliminada(new \DateTime());
            $this->em->persist($nota);
            $this->em->flush();
        }

        return $nota;
    }

    /**
     * @Rest\Get(path="/notasEliminadas")
     * @Rest\View(serializerGroups={"nota","eliminada"}, serializerEnableMaxDepthChecks=true)
     */
    public function notasEliminadas()
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        $notas = $this->notaRepository->findNotasEliminadas($user);

        return $notas;
    }

    /**
     * @Rest\Get(path="/notaRestaurar/{id}")
     * @Rest\View(serializerGroups={"nota"}, serializerEnableMaxDepthChecks=true)
     */
    public function notaRestaurar(Nota $nota)
    {
        $nota->setIseliminada(false);
        $this->em->persist($nota);
        $this->em->flush();

        return $nota;
    }

    /**
     * @Rest\Post(path="/registro")
     * @Rest\View(serializerGroups={"usuario"}, serializerEnableMaxDepthChecks=true)
     */
    public function registro(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class,$user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user,$user->getPassword()));
            $this->em->persist($user);

            $this->em->flush();
            $this->em->refresh($user);
        }

        return $user;
    }

    /**
     * @Rest\Post(path="/login",name="api_login")
     * @Rest\View(serializerGroups={"usuario"}, serializerEnableMaxDepthChecks=true)
     */
    public function login()
    {
        return $this->json(['result'=>true]);
    }

    /**
     * @Rest\Post(path="/cambiarContrasenia")
     * @Rest\View(serializerGroups={"usuario"}, serializerEnableMaxDepthChecks=true)
     */
    public function cambiarContrasenia(Request $request)
    {
        $id = $this->getUser()->getId();
        $user = $this->userRepository->find($id);
        $form = $this->createForm(UserPasswordType::class,$user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $passwordAnterior = $request->request->get('passwordAnterior');

            $passwordNueva = $request->request->get('passwordNueva');
            if ($this->passwordEncoder->isPasswordValid($user, $passwordAnterior)) {
                $user->setPassword($this->passwordEncoder->encodePassword($user,$passwordNueva));
                $this->em->persist($user);

                $this->em->flush();
                $this->em->refresh($user);
                return $this->json(['result'=>true]);
            }

            return $this->json(['error'=>'Password anterior incorrecta']);
        }
        return $form;
    }

    /**
     * @Rest\Post(path="/crearTag")
     * @Rest\View(serializerGroups={"nota"}, serializerEnableMaxDepthChecks=true)
     */
    public function crearTag(Request $request)
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class,$tag);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
                if(!$this->tagRepository->findOneBy(['titulo'=>$tag->getTitulo()])) {
                    $this->em->persist($tag);
                    $this->em->flush();
                    $this->em->refresh($tag);
                    return $tag;
                }
            return $this->json(['error'=>'Ya existe el Tag']);
        }
        return $form;
    }


}
