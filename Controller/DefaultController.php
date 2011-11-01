<?php

namespace Gpupo\CamelSpiderReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller {
    public function indexAction()
    {
        return $this->render('GpupoCamelSpiderReaderBundle:Default:index.html.twig');
    }

    public function folderAction($type, $id)
    {
        return $this->render('GpupoCamelSpiderReaderBundle:Default:index.html.twig');
    }

    public function getMenuAction($type)
    {

        $collection = $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:' . $type)
            ->findForMenu();

        return $this->render('GpupoCamelSpiderReaderBundle:Default:menu.html.twig', array('type' => $type, 'collection'=> $collection));
    }
}
