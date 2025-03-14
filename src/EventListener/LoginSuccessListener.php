<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\LoginHistory;
use DeviceDetector\DeviceDetector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessListener
{
  private $entityManager;
  // private $requestStack;

  public function __construct(
    EntityManagerInterface $entityManager,
    // RequestStack $requestStack
  ) {
    $this->entityManager = $entityManager;
    // $this->requestStack = $requestStack;
  }

  public function onLoginSuccess(Request $request, LoginSuccessEvent $event): void
  {
    $user = $event->getUser();
    if (!$user instanceof User) {
      return;
    }

    $userAgent = $request->headers->get('User-Agent');

    $deviceDetector = new DeviceDetector($userAgent);
    $deviceDetector->parse();

    $loginHistory = new LoginHistory();
    $loginHistory->setUser($user)
      ->setIpAddress($request->getClientIp())
      ->setDevice($deviceDetector->getDeviceName())
      ->setOs($deviceDetector->getOs()['name'])
      ->setBrowser($deviceDetector->getClient()['name']);

    $this->entityManager->persist($loginHistory);
    $this->entityManager->flush();
  }
}
