<?php

namespace App;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonRequestSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(KernelEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->getContentType() !== 'json') {
            return;
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $request->request = new ParameterBag($data);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
