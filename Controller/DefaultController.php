<?php

namespace Gpupo\CamelSpiderReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Ps\PdfBundle\Annotation\Pdf,
    PHPPdf\Parser\Exception\ParseException,
    Gpupo\CamelSpiderReaderBundle\Entity\Send;

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

    public function sendSubmitAction(Request $request)
    {
        $form = $this->getSendForm();
        $form->bindRequest($request);

        if ($form->isValid()) {
            $from = $this->get('security.context')->getToken()->getUser()->getEmail();
            $form = $request->request->get('form');
            $body = $form['body'];

            $message = \Swift_Message::newInstance()
                ->setContentType('text/html')
                ->setSubject($form['subject'])
                ->setFrom($from)
                ->setTo($form['delivery_address'])
                ->setBody($this->renderView('GpupoCamelSpiderReaderBundle:Default:mail.html.twig', array('body' => $body)));
            $this->get('mailer')->send($message);

            $log = 'Email "'
                . $from['subject']
                . '" enviado de "'
                . $from
                . '" para "'
                . $form['delivery_address']
                . '"';

            $this->get('funpar.logger')->doLog(
                'SEND_NEWS',
                $log,
                'me'
            );

            return $this->render('GpupoCamelSpiderReaderBundle:Default:send_success.html.twig', array('from' => $from, 'log' => $log));
        }
    }

    protected function getSend($news_id = null)
    {
        $default = array(
                'delivery_address' => '',
                'subject'          => '',
                'body'             => ''
        );

        if ($news_id) {
            $news = $this->getNews($news_id);
            $default = array(
                'delivery_address' => '',
                'subject'          => $news->getTitle(),
                'body'             => $news->getContent()
            );
        }

        return new Send($default);
    }

    protected function getSendForm($news_id = null)
    {
        return $this->createFormBuilder($this->getSend($news_id))
            ->add('delivery_address', 'email', array('label' => 'Para:'))
            ->add('subject', 'text', array('label' => 'Título'))
            ->add('body', 'textarea', array('label' => 'Conteúdo'))
            ->getForm();
    }

    public function sendAction($news_id)
    {

        $form = $this->getSendForm($news_id);

        return $this->render('GpupoCamelSpiderReaderBundle:Default:send.html.twig',
            array('form' => $form->createView())
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
