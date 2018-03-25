<?php

namespace App\Tests;

use App\JsonRequestSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class JsonRequestSubscriberTest extends TestCase
{
    /** @test */
    public function it_should_automatically_convert_json_body_to_request_data()
    {
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $request = Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{"isValid":true}');
        $event = new KernelEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $subscriber = new JsonRequestSubscriber();
        $subscriber->onKernelRequest($event);

        $this->assertEquals(['isValid'=> true], $request->request->all());
    }

    /** @test */
    public function it_should_ignore_subrequests()
    {
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $request = Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{"isValid":true}');
        $event = new KernelEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber = new JsonRequestSubscriber();
        $subscriber->onKernelRequest($event);

        $this->assertEquals([], $request->request->all());
    }

    /** @test */
    public function it_should_ignore_non_json_requests()
    {
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $request = Request::create('/', 'POST', [], [], [], [], '{"isValid":true}');
        $event = new KernelEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber = new JsonRequestSubscriber();
        $subscriber->onKernelRequest($event);

        $this->assertEquals([], $request->request->all());
    }

    /** @test */
    public function it_should_handle_invalid_json_request()
    {
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $request = Request::create('/', 'POST', [], [], [], [], '{"isValid":trus d1 e}');
        $event = new KernelEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber = new JsonRequestSubscriber();
        $subscriber->onKernelRequest($event);

        $this->assertEquals([], $request->request->all());
    }
}
