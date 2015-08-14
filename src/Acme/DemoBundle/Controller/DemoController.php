<?php

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Acme\DemoBundle\Form\ContactType;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class DemoController extends Controller
{
    /**
     * @Route("/", name="_demo")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/hello/{name}", name="_demo_hello")
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

    /**
     * @Route("/contact", name="_demo_contact")
     * @Template()
     */
    public function contactAction(Request $request)
    {
        $form = $this->createForm(new ContactType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $mailer = $this->get('mailer');

            // .. setup a message and send it
            // http://symfony.com/doc/current/cookbook/email.html

            $request->getSession()->getFlashBag()->set('notice', 'Message sent!');

            return new RedirectResponse($this->generateUrl('_demo'));
        }

        return array('form' => $form->createView());
    }

    public function getListJavascriptProjectAction(){
        $jsProjects = array(
            array(
                'Name' => 'AngularJS',
                'Description' => 'HTML enhanced for web apps!',
                'Website' => 'http://angularjs.org/'
            ),
            array(
                'Name' => 'Backbone',
                'Description' => 'Models for your apps.',
                'Website' => 'http://documentcloud.github.com/backbone/'
            ),
            array(
                'Name' => 'Batman',
                'Description' => 'Quick and beautiful.',
                'Website' => 'http://batmanjs.org/'
            ),
            array(
                'Name' => 'Cappucino',
                'Description' => 'Objective-J.',
                'Website' => 'http://cappuccino.org/'
            ),
            array(
                'Name' => 'Ember',
                'Description' => 'Ambitious web apps.',
                'Website' => 'http://emberjs.com/'
            ),
            array(
                'Name' => 'GWT',
                'Description' => 'JS in Java.',
                'Website' => 'https://developers.google.com/web-toolkit/'
            ),
            array(
                'Name' => 'Knockout',
                'Description' => 'MVVM pattern.',
                'Website' => 'http://knockoutjs.com/'
            ),
            array(
                'Name' => 'Sammy',
                'Description' => 'Small with class.',
                'Website' => 'http://sammyjs.org/'
            ),
            array(
                'Name' => 'Spine',
                'Description' => 'Awesome MVC Apps.',
                'Website' => 'http://spinejs.com/'
            ),
            array(
                'Name' => 'SproutCore',
                'Description' => 'Innovative web-apps.',
                'Website' => 'http://sproutcore.com/'
            )
        );
        return new Response(json_encode($jsProjects));
    }
}
