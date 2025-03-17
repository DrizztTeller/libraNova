<?php 
namespace App\EventSubscriber;


use App\Service\PendingNotificationService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoginSuccessSubscriber implements EventSubscriberInterface
{
    private $requestStack;
    private $pendingNotificationService;

    public function __construct(RequestStack $requestStack, PendingNotificationService $pendingNotificationService)
    {
        $this->requestStack = $requestStack;
        $this->pendingNotificationService = $pendingNotificationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $notifications = $this->pendingNotificationService->getPendingNotifications($user);

        foreach ($notifications as $notification) {
            $this->requestStack->getSession()->getFlashBag()->add('info', $notification->getMessage());
        }

        $this->pendingNotificationService->clearNotifications($user);
    }
}
