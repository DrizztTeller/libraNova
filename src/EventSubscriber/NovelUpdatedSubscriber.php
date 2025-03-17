<?php

namespace App\EventSubscriber;

use Symfony\Component\Mime\Email;
use App\EventListener\NovelUpdatedEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NovelUpdatedSubscriber implements EventSubscriberInterface
{
  public function __construct(private MailerInterface $mailer, private NotifierInterface $notifier) {}

  public static function getSubscribedEvents()
  {
    return [
      NovelUpdatedEvent::class => 'onNovelUpdated',
    ];
  }

  public function onNovelUpdated(NovelUpdatedEvent $event)
  {
    $novel = $event->getNovel();
    $favoriteUsers = $novel->getLikes(); // Supposons que cette méthode existe

    foreach ($favoriteUsers as $user) {
      // Envoyer une notification 
      $this->sendNotification($user, $novel);

      // Envoyer un email
      // $this->sendEmail($user, $novel);
    }
  }

  private function sendNotification($user, $novel) {
    $notification = (new Notification())
        ->subject('Mise à jour de novel')
        ->content('Le novel "' . $novel->getTitle() . '" a été mis à jour.')
        ->importance(Notification::IMPORTANCE_MEDIUM);

    $recipient = new Recipient($user->getEmail());

    $this->notifier->send($notification, $recipient);
  }

  // private function sendEmail($user, $novel)
  // {
  //   $email = (new Email())
  //     ->from('disponibilite@libranova.com')
  //     ->to($user->getEmail())
  //     ->subject('Un livre que vous suivez a été mis à jour')
  //     ->text('Le livre "' . $novel->getTitle() . '" a été mis à jour.');

  //   $this->mailer->send($email);
  // }
}
