<?php

namespace CnitCrmBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CnitCrmBundle:Default:index.html.twig', array('name' => $name));
    }
}
