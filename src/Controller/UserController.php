<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserAvatarType;
use App\Form\UserType;
use App\Repository\FirmRepository;
use App\Repository\JobRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
	/**
	 * @Route("/register", name="user_register")
	 */
	public function register(
		Request $request,
		UserPasswordEncoderInterface $passwordEncoder,
		ValidatorInterface $validator,
		UserRepository $userRepository
	) {
		// If user is logged
		if ($this->getUser()) {
			return $this->redirectToRoute('user_profile');
		}

		$user = new User();

		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$submittedToken = $request->request->get('_csrf_token');

			if (!$this->isCsrfTokenValid('register', $submittedToken)) {
				return $this->redirectToRoute('jobs_index');
			}

			$userByEmailExists = $userRepository->findOneBy([
				'email' => $user->getEmail()
			]);

			if ($userByEmailExists) {
				$this->addFlash('user', 'The email is already taken.');

				return $this->render('user/register.html.twig', [
					'data' => $form->getData()
				]);
			}

			$errors = $validator->validate($user);

			$errorMessages = $this->getErrorMessages($errors);

			if (!$user->isPasswordsEqual()) {
				$errorMessages[] = [
					'type' => 'repeatedPassword',
					'message' => 'Passwords mismatch.'
				];
			}

			if (count($errorMessages) > 0) {
				return $this->render('user/register.html.twig', [
					'errors' => $errorMessages,
					'data' => $form->getData()
				]);
			}

			$hashedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());

			$user->setPassword($hashedPassword);

			$manager = $this->getDoctrine()->getManager();
			$manager->persist($user);
			$manager->flush();

			return $this->redirectToRoute('app_login');
		}

		return $this->render('user/register.html.twig', [
			'data' => $form->getData()
		]);
	}

	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/profile/{id}", name="user_profile")
	 */
	public function profile(?User $user, FirmRepository $firmRepository, JobRepository $jobRepository, Request $request)
	{
		if (!$user) {
			return $this->redirectToRoute('user_profile', [
				'id' => $this->getUser()->getId()
			]);
		}

		if (count($request->files) > 0) {
			$uploads_directory = $this->getParameter('uploads_directory');

			$filesystem = new Filesystem();

			if ($user->getAvatarUrl()) {
				$filesystem->remove($uploads_directory . '/' . $user->getAvatarUrl());
			}

			$file = $request->files->get('file');

			$filename = md5(uniqid()) . '.' . $file->guessExtension();

			$file->move($uploads_directory, $filename);

			$user->setAvatarUrl($filename);

			$manager = $this->getDoctrine()->getManager();
			$manager->persist($user);
			$manager->flush();

			return $this->redirectToRoute('user_profile', [
				'id' => $user->getId()
			]);
		}

		$firms = $firmRepository->findBy([
			'director' => $user->getId()
		]);

		$jobs = $jobRepository->findBy([
			'owner' => $user->getId()
		]);


		return $this->render('user/profile.html.twig', [
			'firms' => $firms,
			'jobs' => $jobs,
			'user' => $user
		]);
	}

	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/profile/{id}/edit", name="user_profile_edit")
	 */
	public function edit(
		?User $user,
		Request $request,
		UserRepository $userRepository,
		ValidatorInterface $validator,
		UserPasswordEncoderInterface $passwordEncoder
	) {
		if (!$user || $user->getId() !== $this->getUser()->getId()) {
			return $this->redirectToRoute('user_profile', [
				'id' => $this->getUser()->getId()
			]);;
		}

		$newUser = new User();

		$form = $this->createForm(UserType::class, $newUser);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$submittedToken = $request->request->get('_csrf_token');

			if (!$this->isCsrfTokenValid('user_edit', $submittedToken)) {
				return $this->redirectToRoute('jobs_index');
			}

			$userByEmailExists = $userRepository->findOneBy([
				'email' => $newUser->getEmail()
			]);

			if ($userByEmailExists && $userByEmailExists->getEmail() !== $user->getEmail()) {
				$this->addFlash('user', 'The email is already taken.');

				return $this->render('user/edit.html.twig', [
					'data' => $form->getData()
				]);
			}

			$errors = $validator->validate($newUser);

			$errorMessages = $this->getErrorMessages($errors);

			$oldPassword = $request->request->get('old_password') ?? '';

			if (!$passwordEncoder->isPasswordValid($user, $oldPassword)) {
				$this->addFlash('user', 'Wrong current password.');

				return $this->render('user/edit.html.twig', [
					'data' => $form->getData()
				]);
			}

			if ($newUser->getPassword() !== null && !$newUser->isPasswordsEqual()) {
				$errorMessages[] = [
					'type' => 'repeatedPassword',
					'message' => 'Passwords mismatch.'
				];
			}

			$isNewPasswordInvalid = false;
			$newPasswordInvalidIndex = 0;

			foreach ($errorMessages as $errorMessage) {
				if ($errorMessage['type'] === 'password') {
					$isNewPasswordInvalid = true;
					break;
				}

				$newPasswordInvalidIndex++;
			}

			// Check if the only one error is new password and if its null then remove error
			// Because new password is optional
			if ($isNewPasswordInvalid && $newUser->getPassword() === null) {
				unset($errorMessages[$newPasswordInvalidIndex]);
				$errorMessages = array_values($errorMessages);
			}

			if (count($errorMessages) > 0) {
				return $this->render('user/edit.html.twig', [
					'data' => $form->getData(),
					'errors' => $errorMessages,
				]);
			}

			if ($newUser->getPassword() !== null) {
				$hashedPassword = $passwordEncoder->encodePassword($newUser, $newUser->getPassword());
				$newUser->setPassword($hashedPassword);

				$user->setPassword($newUser->getPassword());
			}

			$user
				->setEmail($newUser->getEmail())
				->setFirstName($newUser->getFirstName())
				->setLastName($newUser->getLastName())
				->setDescription($newUser->getDescription())
				->setLocation($newUser->getLocation())
				->setPhone($newUser->getPhone());

			$manager = $this->getDoctrine()->getManager();
			$manager->persist($user);
			$manager->flush();

			$this->addFlash('success', 'Profile was edited successfully');

			return $this->redirectToRoute('user_profile', [
				'id' => $user->getId()
			]);
		}

		return $this->render('user/edit.html.twig');
	}

	private function getErrorMessages(ConstraintViolationList $errors): array
	{
		$errorMessages = [];

		foreach ($errors as $error) {
			$errorMessages[] = [
				'type' => $error->getPropertyPath(),
				'message' => $error->getMessage()
			];
		}

		return $errorMessages;
	}
}
