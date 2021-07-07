<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/messages", name="messages")
     */
    public function index()
    {
        return $this->render('message/index.html.twig', []);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/messages/{id}", name="messages_get_one")
     */
    public function getOne(?Message $message)
    {
        if (!$message || $message->getRecipient()->getId() !== $this->getUser()->getId()) {
            return $this->redirectToRoute('jobs_index');
        }

        if (!$message->getIsRead()) {
            $message->setIsRead(true);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($message);
            $manager->flush();
        }

        return $this->render('message/message.html.twig', [
            'message' => $message
        ]);
    }
}
