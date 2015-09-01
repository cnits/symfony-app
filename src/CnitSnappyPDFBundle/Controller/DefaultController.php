<?php

namespace CnitSnappyPDFBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CnitSnappyPDFBundle:Default:index.html.twig', array('name' => $name));
    }

    public function snappyPdf(){
        $html = $this->renderView('CnitSnappyPDFBundle:Default:index.html.twig', array(
            'User'  => array(
                "Name" => "PVLam",
                "Email" => "phonglam@ccintegration.com"
            ),
            "Type" => "Pdf"
        ));

        return new Response(
            $this->get('cnit_snappy_pdf.pdf') -> getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="file.pdf"'
            )
        );
    }

    public function snappyImage(){
        $html = $this->renderView('CnitSnappyPDFBundle:Default:index.html.twig', array(
            'User'  => array(
                "Name" => "PVLam",
                "Email" => "phonglam@ccintegration.com"
            ),
            "Type" => "Image"
        ));

        return new Response(
            $this->get('cnit_snappy_pdf.image') -> getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'image/jpg',
                'Content-Disposition'   => 'filename="image.jpg"'
            )
        );
    }
}
