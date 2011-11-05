<?php

namespace Gpupo\CamelSpiderReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Ps\PdfBundle\Annotation\Pdf,
    PHPPdf\Parser\Exception\ParseException;

class DefaultController extends Controller {


    protected function getNewsRepository()
    {
        return $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:News');
    }

    protected function getNews($news_id)
    {
        return $this->getNewsRepository()->findOneById($news_id);
    }

    public function indexAction()
    {
        $collection = $this->getNewsRepository()
            ->findLatest()->getResult();

        return $this->render('GpupoCamelSpiderReaderBundle:Default:index.html.twig', array('collection' => $collection));
    }

    /**
     * Print or PDF
     *
     * @Pdf()
     */
    public function newsAction($news_id, Request $request)
    {
        $news = $this->getNews($news_id);
        $format = $request->get('_format', 'html');
        try {
            return $this->render(sprintf('GpupoCamelSpiderReaderBundle:Default:news.%s.twig', $format), array('news' => $news));
        } catch (ParseException $e) {
            echo $e->message();
        }

    }

    public function sendAction($news_id)
    {
        $news = $this->getNews($news_id);
        $form = $this->createFormBuilder($news)
            //->add('email_address', 'text')
            ->add('id', 'text')
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->getForm();

        return $this->render('GpupoCamelSpiderReaderBundle:Default:send.html.twig',
            array('news' => $news, 'form' => $form->createView())
        );

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
