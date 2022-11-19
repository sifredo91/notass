<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $em;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder,
                                EntityManagerInterface $em)
    {
        $this->passwordEncoder=$passwordEncoder;
        $this->em=$em;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function registro(Request $request): Response
    {

        $user = new User();
        $form = $this->createForm(UserType::class,$user);
        //dd('asd');
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            //dd($user);
            $user->setPassword($this->passwordEncoder->encodePassword($user,$user->getPassword()));
            $this->em->persist($user);

            $this->em->flush();
            $this->em->refresh($user);
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/register.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/cambiarContrasenia", name="app_cambiarContrasenia")
     */
    public function cambiarContraseña(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserPasswordType::class,$user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $passwordAnterior = $request->request->get('user_password')['passwordAnterior'];
            $passwordNueva = $request->request->get('user_password')['passwordNueva'];
            if ($this->passwordEncoder->isPasswordValid($user, $passwordAnterior)) {
                $user->setPassword($this->passwordEncoder->encodePassword($user,$passwordNueva));
                $this->em->persist($user);

                $this->em->flush();
                $this->em->refresh($user);
                return $this->redirectToRoute('nota.listado',[
                    'id' => $user->getId()
                ]);
            }
            return $this->redirectToRoute('app_cambiarContrasenia');

        }
        return $this->render('security/cambiarContrasenia.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
