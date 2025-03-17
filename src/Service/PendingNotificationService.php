<?php 

namespace App\Service;

use App\Entity\User;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class PendingNotificationService
{
    public function __construct(private EntityManagerInterface $em, private NotificationRepository $ntr)
    {
    }

    public function addNotification(User $user, string $message)
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setMessage($message);
        
        $this->em->persist($notification);
        $this->em->flush();
    }

    public function getPendingNotifications(User $user)
    {
        return $this->ntr->findBy(['user' => $user]);
    }

    public function clearNotifications(User $user)
    {
        $notifications = $this->getPendingNotifications($user);
        foreach ($notifications as $notification) {
            $this->em->remove($notification);
        }
        $this->em->flush();
    }
}
