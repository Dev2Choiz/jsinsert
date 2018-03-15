<?php

namespace Tests\JsInsert\Listener\Mock;

use JsInsert\Annotation\JsInsert;
use Symfony\Component\HttpFoundation\Response;

class MockController
{
    /**
     * @JsInsert()
     */
    public function indexAction()
    {
        return new Response();
    }

    public function editAction()
    {
        return new Response();
    }
}
