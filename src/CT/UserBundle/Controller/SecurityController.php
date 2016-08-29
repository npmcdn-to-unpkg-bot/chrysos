<?php


namespace CT\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CT\UserBundle\Entity\User;
use CT\UserBundle\Form\UserType;

class SecurityController extends Controller
{
    public function indexAction()
    {
        return $this->render('CTUserBundle:Security:index.html.twig');
    }
    public function signinAction(Request $request)
    {
        $user  = new User();

        $form = $this->get('form.factory')->create(UserType::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
          $em = $this->getDoctrine()->getManager();
          // On ne se sert pas du sel pour l'instant
          $user->setSalt('');
          // On définit uniquement le role ROLE_USER qui est le role de base
          $user->setRoles(array('ROLE_USER'));

          $validator = $this->get('validator');
          $listErrors = $validator->validate($user);

          if(count($listErrors) == 0){
            $em->persist($user);
            $em->flush();
            return new Response("User saved!");
          }

        }

        return $this->render('CTUserBundle:Security:signin.html.twig', array(
          'form' => $form->createView(),
        ));
    }
    public function loginAction()
    {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
          return $this->redirectToRoute('ct_user_homepage');
        }

        // Le service authentication_utils permet de récupérer le nom d'utilisateur
        // et l'erreur dans le cas où le formulaire a déjà été soumis mais était invalide
        // (mauvais mot de passe par exemple)
        $authenticationUtils = $this->get('security.authentication_utils');


        return $this->render('CTUserBundle:Security:login.html.twig', array(
          'last_username' => $authenticationUtils->getLastUsername(),
          'error'         => $authenticationUtils->getLastAuthenticationError(),
        ));
    }
}
