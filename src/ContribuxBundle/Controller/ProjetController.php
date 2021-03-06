<?php

namespace ContribuxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use ContribuxBundle\Entity\Projet;
use ContribuxBundle\Entity\Categorie;
use Symfony\Component\HttpFoundation\Request;
use ContribuxBundle\Form\Type\ProjetType;

class ProjetController extends Controller {

    /**
     *
     * @Route("/projets", name="projets_list")
     *
     */
    public function projetsListAction() {
        $em = $this->getDoctrine()->getManager();
        //récuperation des catégories pour l'affichage
        $categories = $em->getRepository('ContribuxBundle:Categorie')->findAll();
        return $this->render('ContribuxBundle:Projet:projetsList.html.twig', array('categories' => $categories));
    }



    /**
     * Envoi des données HTML avec Ajax
     * @Route("/projets_ajax/{page}", name="projets_ajax")
     *
     */
    public function projetsAjaxAction($page) {

        $nbParPage = 4; 
        $em = $this->getDoctrine()->getManager();
        $projets = $em->getRepository('ContribuxBundle:Projet')->getAllProjets($page, $nbParPage);


        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($projets) / $nbParPage),
            'nomRoute' => 'projets_ajax',
            'paramsRoute' => array()
        );

        return $this->render('ContribuxBundle:Projet:projetsListAjax.html.twig', array('projets' => $projets, 'pagination' => $pagination));
    }

    /**
     *
     * @Route("/mes-projets", name="mes_projets_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function mesProjetsListAction() {

        return $this->render('ContribuxBundle:Projet:mesProjetsList.html.twig');
    }

    /**
     *
     * @Route("/mes-projets_ajax/{page}", name="mes_projets_ajax")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function mesProjetsAjaxAction($page) {
        $nbParPage = 4; 
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); //On récupère l'utilisateur
        //On récupere les projets de l'utilisateur via la methode getMyProjets()
        $projets = $em->getRepository('ContribuxBundle:Projet')->getMyProjets($page, $nbParPage, $user);


        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($projets) / $nbParPage),
            'nomRoute' => 'mes_projets',
            'paramsRoute' => array()
        );

        return $this->render('ContribuxBundle:Projet:projetsListAjax.html.twig', array('projets' => $projets, 'pagination' => $pagination));
    }
    
    
    /************* C R U D **************/
    

    
    /**
     * Affichage d'un projet
     * @Route("/projet/{id}", name="projet_view", requirements={"id": "\d+"})
     *
     */
    public function projetViewAction($id) {
        $em = $this->getDoctrine()->getManager();
        $projet = $em->getRepository('ContribuxBundle:Projet')->find($id);
        return $this->render('ContribuxBundle:Projet:projet.html.twig', array('projet' => $projet));
    }
    
    
    
    
    /**
     *
     * @Route("/projet/create", name="projet_create")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function projetCreateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $projet = new Projet();
        $user = $this->getUser(); //On récupère l'utilisateur
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form["file"]->getData() != null) {
                $projet->uploadProjetPicture(); //On enregistre l'image dans le dossier /web/uploads
            }
            $projet->setUser($user);
            $em->persist($projet);
            $em->flush(); //Enregistrement
            $this->get('session')->getFlashBag()->add('info', "Le projet a bien été ajouté");
            return $this->redirect($this->generateUrl('mes_projets_list'));
        }

        return $this->render('ContribuxBundle:Projet:projetCreate.html.twig', array(
                    'entity' => $projet,
                    'form' => $form->createView(),
                    'action' => 'Ajouter',
                    'route' => 'projet_create'
        ));
    }

    
    /**
     *
     * @Route("/projet/{id}/edit", name="projet_edit")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function projetEditAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $projet = $em->getRepository('ContribuxBundle:Projet')->find(array('id' => $id));

        if (!$projet) {
            throw $this->createNotFoundException("Impossible");
        }
        if ($this->getUser() == $projet->getUser()) {  //On vérifie bien qu'il s'agit de l'auteur
            $editForm = $this->createForm(ProjetType::class, $projet);
            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $projet->setDateModif(new \DateTime()); //nouvelle date de modif
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', "Le projet a bien été modifié.");
                return $this->redirect($this->generateUrl('projet_view', array('id' => $id)));
            }
            return $this->render('ContribuxBundle:Projet:projetEdit.html.twig', array(
                        'projet' => $projet,
                        'form' => $editForm->createView(),
            ));
        } else {
            //On lui indique l'erreur Forbidden 403
            return $this->render('UserBundle:Default:privileges.html.twig', array('error' => 403));
        }
    }
    

    /**
     *
     * @Route("/projet/{id}/delete", name="projet_delete")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function projetDeleteAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $projet = $em->getRepository('ContribuxBundle:Projet')->find(array('id' => $id));

        if (!$projet) {
            throw $this->createNotFoundException("Impossible");
        }
        if ($this->getUser() == $projet->getUser()) {  //On vérifie bien qu'il s'agit de l'auteur
            $em->remove($projet); //suppression
            $em->flush();
            $this->get('session')->getFlashBag()->add('info', "Le projet a bien été supprimé.");
            return $this->redirect($request->headers->get('referer'));
        } else {
            //On lui indique l'erreur Forbidden 403
            return $this->render('UserBundle:Default:privileges.html.twig', array('error' => 403));
        }
    }

}
