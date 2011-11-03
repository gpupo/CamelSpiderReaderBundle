<?php

namespace Gpupo\CamelSpiderReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Ps\PdfBundle\Annotation\Pdf;

class DefaultController extends Controller {


    protected function getNewsRepository()
    {
        return $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:News');
    }
    public function indexAction()
    {
        $collection = $this->getNewsRepository()
            ->findLatest()->getResult();

        return $this->render('GpupoCamelSpiderReaderBundle:Default:index.html.twig', array('collection' => $collection));
    }

    public function printAction($id)
    {
        $news = $this->getNewsRepository()
            ->findOneById($id);

        return $this->render('GpupoCamelSpiderReaderBundle:Default:news.html.twig', array('news' => $news));
    }

    /**
     * @Pdf()
     */
    public function pdfAction($id)
    {
        return $this->printAction($id);
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
