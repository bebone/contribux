<?php

namespace ContribuxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;

class DefaultController extends Controller
{

    /**
     *
     * @Route("/", name="index")
     *
     */
    public function indexAction()
    {
        return $this->render('ContribuxBundle:Default:index.html.twig');
    }

    /**
     *
     * @Route("/mentions-legales", name="mentionsL")
     */
    public function mentionsLAction()
    {
        return $this->render('ContribuxBundle:Default:mentions-legales.html.twig');
    }

    /**
     *
     * @Route("/qui-sommes-nous", name="about")
     */
    public function quisommesNousAction()
    {
        return $this->render('ContribuxBundle:Default:qui-sommes-nous.html.twig');
    }
}