<?php

namespace Tests\JsInsert\Listener;

use JsInsert\Listener\ResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\JsInsert\Listener\Mock\MockController;

class ResponseListenerTest extends \PHPUnit\Framework\TestCase
{
    public function __construct()
    {
        // @todo deprecié dans la version 2 de doctrine/annotations
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
        parent::__construct();
    }

    public function testOnKernelResponseWhenControllerHasTheAnnotation()
    {
        $input   = '<html><head>headContent</head></html>';

        $listener = $this->getSvcMock(true);
        $event    = $this->getFilterResponseEvent($input);
        // appel de la methode qui sera lancé par le dispatcher
        $listener->onKernelResponse($event);

        $this->assertNotEquals($input, $event->getResponse()->getContent());
    }

    public function testOnKernelResponseWhenControllerHasNotTheAnnotation()
    {
        $input = '<html><head>headContent</head></html>';

        $listener = $this->getSvcMock(false);
        $event = $this->getFilterResponseEvent($input);
        // appel de la methode qui sera lancé par le dispatcher
        $listener->onKernelResponse($event);
        $this->assertEquals($input, $event->getResponse()->getContent());
    }

    public function testOnKernelResponseWhenItIsNotMasterRequest()
    {
        $input = '<html><head>headContent</head></html>';

        $listener = $this->getSvcMock(false);
        // creation d'un evenement dont la requete est une sous-requete
        $event = $this->getFilterResponseEvent($input, HttpKernelInterface::SUB_REQUEST);
        // appel de la methode qui sera lancé par le dispatcher
        $listener->onKernelResponse($event);
        $this->assertEquals($input, $event->getResponse()->getContent());
    }

    public function testInjectIntoContentResponse()
    {
        $input   = '<html><head>headContent</head></html>';
        $expected = <<<HTML
<html><head>
<style>
body {
}
</style>
<script>


</script>
headContent</head></html>
HTML;

        $response = new Response($input);
        $listener = $this->getSvcMock(true);
        $listener->injectIntoContentResponse($response);

        $this->assertEquals($expected, $response->getContent());
    }

    public function testInjectIntoContentResponseWithNoHeadTag()
    {
        $expected = $input   = '<html><body>bodyContent</body></html>';
        $response = new Response($input);
        $listener = $this->getSvcMock(true);
        $listener->injectIntoContentResponse($response);

        $this->assertEquals($expected, $response->getContent());
    }

    public function testHasJsInsertAnnotation()
    {
        $listener = new ResponseListener();
        $event    = $this->getFilterResponseEvent(true);
        $result   = $listener->hasJsInsertAnnotation($event);

        $this->assertFalse($result);
    }

    public function testHasJsInsertAnnotationWithInvalidArgument()
    {
        $mockResolver = $this->getMockBuilder(ControllerResolver::class)
            ->setMethods(['getController'])
            ->getMock();
        $mockResolver->expects($this->any())
            ->method('getController')
            ->willThrowException(new \InvalidArgumentException());

        $listener = new ResponseListener();
        $listener->setResolver($mockResolver);

        $event    = $this->getFilterResponseEvent(true);
        $result   = $listener->hasJsInsertAnnotation($event);

        $this->assertFalse($result);
    }

    public function testHasJsInsertAnnotationWhenNoControllerMatches()
    {
        $mockResolver = $this->getMockBuilder(ControllerResolver::class)
            ->setMethods(['getController'])
            ->getMock();
        $mockResolver->expects($this->any())
            ->method('getController')
            ->willReturn(false);

        $listener = new ResponseListener();
        $listener->setResolver($mockResolver);

        $event    = $this->getFilterResponseEvent(true);
        $result   = $listener->hasJsInsertAnnotation($event);

        $this->assertFalse($result);
    }

    public function testHasJsInsertAnnotationWhenItHasAnnotation()
    {
        $controller = new MockController();
        $controller = [$controller, 'indexAction'];

        $mockResolver = $this->getMockBuilder(ControllerResolver::class)
            ->setMethods(['getController'])
            ->getMock();
        $mockResolver->expects($this->any())
            ->method('getController')
            ->willReturn($controller);

        $listener = new ResponseListener();
        $listener->setResolver($mockResolver);

        $event    = $this->getFilterResponseEvent(true);
        $result   = $listener->hasJsInsertAnnotation($event);

        $this->assertTrue($result);
    }

    public function testHasJsInsertAnnotationWhenItHasNotAnnotation()
    {
        $controller = new MockController();
        $controller = [
            $controller,
            'editAction',
        ];

        $mockResolver = $this->getMockBuilder(ControllerResolver::class)
            ->setMethods(['getController'])
            ->getMock();
        $mockResolver->expects($this->any())
            ->method('getController')
            ->willReturn($controller);

        $listener = new ResponseListener();
        $listener->setResolver($mockResolver);

        $event    = $this->getFilterResponseEvent(true);
        $result   = $listener->hasJsInsertAnnotation($event);

        $this->assertFalse($result);
    }

    /**
     * @return FilterResponseEvent
     */
    public function getFilterResponseEvent($content = null, $requestType = HttpKernelInterface::MASTER_REQUEST)
    {
        $content    = is_string($content) ? $content : '';
        $response   = new Response($content);
        $request    = new Request();
        $dipsacther = new EventDispatcher();
        $resolver   = new ControllerResolver();
        $kernel     = new HttpKernel($dipsacther, $resolver);
        return new FilterResponseEvent($kernel, $request, $requestType, $response);
    }

    /**
     * @param $hasJsInsertAnnotation
     * @return ResponseListener
     */
    public function getSvcMock($hasJsInsertAnnotation)
    {
        $mockSvc = $this->getMockBuilder(ResponseListener::class)
            ->setMethods(['hasJsInsertAnnotation'])
            ->getMock();
        $mockSvc->setReferenceTag('head');
        $mockSvc->expects($this->any())
                ->method('hasJsInsertAnnotation')
                ->will($this->returnValue($hasJsInsertAnnotation));

        return $mockSvc;
    }
}
