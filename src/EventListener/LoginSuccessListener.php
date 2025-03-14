<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\LoginHistory;
use DeviceDetector\DeviceDetector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;


class LoginSuccessListener
{
  private $entityManager;
  private $requestStack;

  public function __construct(
    EntityManagerInterface $entityManager,
    RequestStack $requestStack
  ) {
    $this->entityManager = $entityManager;
    $this->requestStack = $requestStack;
  }

  public function onLoginSuccess(AuthenticationSuccessEvent $event): void
  {
    $user = $event->getAuthenticationToken()->getUser();

    if (!$user instanceof User) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();
    $userAgent = $request->headers->get('User-Agent');

    $deviceDetector = new DeviceDetector($userAgent);
    $deviceDetector->parse();

    $loginHistory = new LoginHistory();
    $loginHistory->setUser($user)
      ->setLoginDate(new \DateTimeImmutable())
      ->setIpAddress($request->getClientIp())
      ->setDevice($deviceDetector->getDeviceName())
      ->setOs($deviceDetector->getOs()['name'])
      ->setBrowser($deviceDetector->getClient()['name']);

    $this->entityManager->persist($loginHistory);
    $this->entityManager->flush();
  }
}
