<?php

namespace Gpupo\CamelSpiderReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller {


    protected function getNewsRepository()
    {
        return $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:News');
    }
    public function indexAction()
    {
        return $this->render('GpupoCamelSpiderReaderBundle:Default:index.html.twig');
    }

    public function folderAction($type, $id)
    {
        $node = $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:' . $type)
            ->findOneById($id);

        $collection = $this->getNewsRepository()
            ->findByType($type, $id)->getResult();

        return $this->render('GpupoCamelSpiderReaderBundle:Default:index.html.twig', array('node' => $node, 'collection' => $collection));
    }

    public function getMenuAction($type)
    {

        $collection = $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:' . $type)
            ->findForMenu();

        return $this->render('GpupoCamelSpiderReaderBundle:Default:menu.html.twig', array('type' => $type, 'collection'=> $collection));
    }
}
