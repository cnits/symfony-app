<?php

namespace CnitLdapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CnitLdapBundle:Default:index.html.twig', array('name' => $name));
    }
}
