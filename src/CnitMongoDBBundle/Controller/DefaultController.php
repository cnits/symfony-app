<?php

namespace CnitMongoDBBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CnitMongoDBBundle:Default:index.html.twig', array('name' => $name));
    }
}
