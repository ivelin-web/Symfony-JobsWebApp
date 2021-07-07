<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact_index")
     */
    public function index(Request $request, MailerInterface $mailer)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $submittedToken = $request->request->get('_csrf_token');

            if (!$this->isCsrfTokenValid('message_send', $submittedToken)) {
                return $this->redirectToRoute('jobs_index');
            }

            $errors = $this->getFormErrors($form);

            if (count($errors) > 0) {
                return $this->render('contact/index.html.twig', [
                    'errors' => $errors,
                    'data' => $form->getData()
                ]);
            }

            $email = (new TemplatedEmail())
                ->from($form->getData()['email'])
                ->to('jobsweb.moderator@gmail.com')
                ->subject($form->getData()['subject'])
                ->htmlTemplate('contact/email.html.twig')
                ->context([
                    'data' => $form->getData()
                ]);

            $mailer->send($email);

            $this->addFlash(
                'success',
                'Your message was sent successfully'
            );
        }

        return $this->render('contact/index.html.twig');
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getFormErrors($form): array
    {
        $errors = [];

        $formData = $form->getData();

        // Check name
        if (!preg_match("/^[A-z\s]+$/", $formData['name'])) {
            $errors[] = [
                'type' => 'name',
                'message' => 'Invalid name.'
            ];
        }

        // Check email
        if (!preg_match("/^[\w]+[\w\-\.]+@[\w]+\.[\w]{2,4}$/", $formData['email'])) {
            $errors[] = [
                'type' => 'email',
                'message' => 'Invalid email.'
            ];
        }

        // Check subject
        if (!preg_match("/^.+$/", $formData['subject'])) {
            $errors[] = [
                'type' => 'subject',
                'message' => 'Invalid subject.'
            ];
        }

        // Check message
        if (!preg_match("/^.+$/", $formData['message'])) {
            $errors[] = [
                'type' => 'message',
                'message' => 'Invalid message.'
            ];
        }

        return $errors;
    }
}
