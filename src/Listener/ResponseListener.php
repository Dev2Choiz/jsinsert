<?php

namespace JsInsert\Listener;

use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Annotations\AnnotationReader;
use JsInsert\Annotation\JsInsert;

class ResponseListener
{
    /** @var  string $referenceTag */
    protected $referenceTag;
    /** @var  ControllerResolver $resolver */
    protected $resolver;

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest() || ! $this->hasJsInsertAnnotation($event)) {
            return;
        }

        $this->injectIntoContentResponse($event->getResponse());
    }

    /**
     * @param Response $response
     */
    public function injectIntoContentResponse(Response $response)
    {
        $view    = \JsInsert\JsInsert::renderView();
        $pattern = "/(<\s*{$this->getReferenceTag()}\s*[^>]*>)/im";
        $content = preg_replace($pattern, "\${1}\n$view", $response->getContent(), 1);

        $response->setContent($content);
    }

    /**
     * @param FilterResponseEvent $event
     * @return bool
     */
    public function hasJsInsertAnnotation(FilterResponseEvent $event)
    {
        try {
            $controller = $this->getResolver()->getController($event->getRequest());
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        if (false === $controller) {
            return false;
        }
        $action = $controller[1];
        $controller = $controller[0];

        $annoReader = new AnnotationReader();
        $reflController = new \ReflectionClass($controller);
        $reflMethod = $reflController->getMethod($action);

        return null !== $annoReader->getMethodAnnotation($reflMethod, JsInsert::class);
    }

    /**
     * @return string
     */
    public function getReferenceTag ()
    {
        return $this->referenceTag;
    }

    /**
     * @param string $referenceTag
     * @return ResponseListener
     */
    public function setReferenceTag ($referenceTag)
    {
        $this->referenceTag = $referenceTag;
        return $this;
    }

    /**
     * @return ControllerResolver
     */
    public function getResolver ()
    {
        if (null === $this->resolver) {
            $this->resolver = new ControllerResolver();
        }
        return $this->resolver;
    }

    /**
     * @param ControllerResolver $resolver
     * @return ResponseListener
     */
    public function setResolver ($resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }
}
