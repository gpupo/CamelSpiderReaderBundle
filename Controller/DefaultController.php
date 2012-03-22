<?php

namespace Gpupo\CamelSpiderReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Ps\PdfBundle\Annotation\Pdf,
    PHPPdf\Parser\Exception\ParseException,
    Gpupo\CamelSpiderReaderBundle\Entity\Send,
    Gpupo\CamelSpiderBundle\Generator\News as Generator,
    Coregen\AdminGeneratorBundle\ORM\Pager;

class DefaultController extends Controller {

    /**
     * @var Coregen\AdminGeneratorBundle\Generator\Generator
     */
    protected $generator = null;

    /**
     * @var Coregen\AdminGeneratorBundle\ORM\Pager
     */
    protected $pager = null;

    /**
     * Get Pager
     *
     * @return Coregen\AdminGeneratorBundle\ORM\Pager
     */
    protected function getPager()
    {
        // Load the News generator class
        if (null === $this->generator) {
            $this->generator = new Generator();
        }

        // Loads the pager
        if (null === $this->pager) {
            $this->pager = $this->get('coregen.orm.pager');
            $this->pager->setGenerator($this->generator);
        }

        // Configure the pager
        $currentPage = $this->getRequest()->get('page', 1);
        $this->pager->setCurrent($currentPage);
        $this->pager->setLimit($this->generator->list->max_per_page);
        $this->pager->setSort($this->generator->list->sort);

        return $this->pager;
    }

    protected function getNewsRepository()
    {
        return $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:News');
    }

    protected function getNews($news_id)
    {
        return $this->getNewsRepository()->findOneById($news_id);
    }


    /**
     * Index action
     *
     * @return Response
     */
    public function indexAction()
    {

        $pager = $this->getPager();

        $qb = $this->getNewsRepository()
                ->readerQueryBuilder();

        $pager->setQueryBuilder($qb);

        return $this->render(
            'GpupoCamelSpiderReaderBundle:Default:index.html.twig',
            array('pager' => $pager)
        );
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
            return $this->render(
                sprintf(
                    'GpupoCamelSpiderReaderBundle:Default:news.%s.twig',
                    $format
                ),
                array(
                    'news'       => $news,
                    'highlights' => $request->query->get('highlights', 'false'),
                )
            );
        } catch (ParseException $e) {
            echo $e->message();
        }
    }

    public function sendSubmitAction(Request $request)
    {
        $form = $this->getSendForm();
        $form->bindRequest($request);

        if ($form->isValid()) {
            $from = $this->get('security.context')
                ->getToken()
                ->getUser()
                ->getEmail();
            $form = $request->request->get('form');
            $body = $form['body'];

            $message = \Swift_Message::newInstance()
                ->setContentType('text/html')
                ->setSubject($form['subject'])
                ->setFrom($from)
                ->setTo($form['delivery_address'])
                ->setBody(
                    $this->renderView(
                        'GpupoCamelSpiderReaderBundle:Default:mail.html.twig',
                        array('body' => $body
                    )));
            $this->get('logger')->info('Try send mail to ' . $form['delivery_address']);
            try {
                $send = $this->get('mailer')->send($message);

                $this->get('logger')->info('Send status = ' . (int) $send);

                $log = 'Mensagem "'
                    . addslashes($form['subject'])
                    . '" enviado de "<b>'
                    . $from
                    . '</b>" para "<b>'
                    . $form['delivery_address']
                    . '</b>"';

                $this->get('funpar.logger')->doLog(
                    'SEND_NEWS',
                    $log,
                    'me'
                );

            } catch (Swift_TransportException $e) {
                $log = $e->getMessage();
            }

            return $this->render(
                'GpupoCamelSpiderReaderBundle:Default:send_success.html.twig',
                array('from' => $from, 'log' => $log)
            );
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

        return $this->render(
            'GpupoCamelSpiderReaderBundle:Default:send.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Process search submit
     * @param string $keyword
     */
    public function searchAction(Request $request)
    {
        $keyword = $request->get('keyword');

        $pager = $this->getPager();

        $qb = $this->getNewsRepository()
            ->searchByKeywordQueryBuilder($keyword);

        $pager->setQueryBuilder($qb);

        return $this->render('GpupoCamelSpiderReaderBundle:Default:index.html.twig',
                array(
                    'node' => array('name' => 'Resultados da pesquisa por "' . $keyword . '"'),
                    'pager' => $pager,
                    'folderType'=> 'search',
                    'keyword' => $keyword
                )
            );
    }

    public function folderAction($type, $id)
    {
        $node = $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:' . $type)
            ->findOneById($id);

        $pager = $this->getPager();

        $qb = $this->getNewsRepository()
            ->findByTypeQueryBuilder($type, $id);
        $pager->setQueryBuilder($qb);

        return $this->render(
            'GpupoCamelSpiderReaderBundle:Default:index.html.twig',
            array('node' => $node, 'folderType'=>$type, 'pager' => $pager)
        );
    }

    public function getMenuAction($type, $node, $folderType)
    {

        $collection = $this->get('doctrine')
            ->getRepository('GpupoCamelSpiderBundle:' . $type)
            ->findForMenu();

        return $this->render(
            'GpupoCamelSpiderReaderBundle:Default:menu.html.twig',
            array(
                'type'       => $type,
                'collection' => $collection,
                'node'       => $node,
                'folderType' =>$folderType
            )
        );
    }
}
