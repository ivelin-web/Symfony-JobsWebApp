<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\Message;
use App\Form\JobType;
use App\Form\MessageType;
use App\Repository\FirmRepository;
use App\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JobController extends AbstractController
{
	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/jobs/{id}/messages/apply", name="jobs_apply_message", methods={"POST"})
	 */
	public function applyMessage(
		?Job $job,
		Request $request,
		ValidatorInterface $validator
	) {
		if (!$job || $job->getOwner()->getId() === $this->getUser()->getId()) {
			return $this->redirectToRoute('jobs_index');
		}

		$submittedToken = $request->request->get('_csrf_token');

		if (!$this->isCsrfTokenValid('job_message_apply', $submittedToken)) {
			return $this->redirectToRoute('jobs_index');
		}

		$message = new Message();

		$form = $this->createForm(MessageType::class, $message);
		$form->handleRequest($request);

		$errors = $validator->validate($message);

		$errorMessages = $this->getErrorMessages($errors);

		if (count($errorMessages) > 0) {
			$this->addFlash(
				'error',
				$errorMessages[0]['message']
			);

			return $this->redirectToRoute('jobs_get_one', [
				'id' => $job->getId(),
			]);
		}

		$message->setSender($this->getUser());
		$message->setRecipient($job->getOwner());

		$manager = $this->getDoctrine()->getManager();
		$manager->persist($message);
		$manager->flush();

		$this->addFlash(
			'success',
			'The message was sended successfully'
		);

		return $this->redirectToRoute('jobs_get_one', [
			'id' => $job->getId(),
		]);
	}

	/**
	 * @Route("/jobs/favorites", name="jobs_favorites")
	 */
	public function favorites()
	{
		$jobs = $this->getUser()->getFavoriteJobs();

		return $this->render('job/favorites.html.twig', [
			'jobs' => $jobs
		]);
	}

	/**
	 * @Route("/", name="jobs_index")
	 */
	public function index(
		JobRepository $jobRepository,
		FirmRepository $firmRepository,
		Request $request
	) {
		$title = $request->request->get('title') ?? null;

		if ($title && preg_match('/^[\S]+$/', $title)) {
			$jobs = $jobRepository->findByTitle($title);

			return $this->render('job/index.html.twig', [
				'jobs' => $jobs,
				'title' => $title
			]);
		}

		$jobs = $jobRepository->findBy([], ['dateAdded' => 'desc']);
		$firms = $firmRepository->findBy([], ['dateOfCreation' => 'desc']);

		return $this->render('job/index.html.twig', [
			'jobs' => $jobs,
			'firms' => $firms
		]);
	}

	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/jobs/add", name="jobs_add")
	 */
	public function add(
		Request $request,
		ValidatorInterface $validator,
		FirmRepository $firmRepository
	) {
		$job = new Job();
		$firms = $firmRepository->findBy([
			'director' => $this->getUser()->getId()
		]);

		$form = $this->createForm(JobType::class, $job);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$submittedToken = $request->request->get('_csrf_token');

			if (!$this->isCsrfTokenValid('job_create', $submittedToken)) {
				return $this->redirectToRoute('jobs_index');
			}

			if (count($firms) === 0) {
				$this->addFlash('job', 'The firm can not be null.');

				return $this->render('job/add.html.twig', [
					'firms' => $firms,
					'data' => $form->getData()
				]);
			}

			$errors = $validator->validate($job);

			$errorMessages = $this->getErrorMessages($errors);

			$selectedFirmName = $request->request->get('job')['firm'] ?? null;

			$selectedFirm = null;

			foreach ($firms as $firm) {
				if ($firm->getName() === $selectedFirmName) {
					$selectedFirm = $firm;
				}
			}

			if ($selectedFirm === null) {
				$errorMessages[] = [
					'type' => 'firm',
					'message' => 'Invalid firm.'
				];
			}

			if (count($errorMessages) > 0) {
				return $this->render('job/add.html.twig', [
					'firms' => $firms,
					'errors' => $errorMessages,
					'data' => $form->getData()
				]);
			}

			$job->setOwner($this->getUser());
			$job->setFirm($selectedFirm);

			$manager = $this->getDoctrine()->getManager();
			$manager->persist($job);
			$manager->flush();

			$this->addFlash('success', 'The job was added successfully');

			return $this->redirectToRoute('user_profile', [
				'id' => $this->getUser()->getId()
			]);
		}

		return $this->render('job/add.html.twig', [
			'firms' => $firms,
			'data' => $form->getData()
		]);
	}

	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/jobs/{id}/edit", name="jobs_edit")
	 */
	public function edit(
		?Job $job,
		FirmRepository $firmRepository,
		Request $request,
		ValidatorInterface $validator
	) {
		if (!$job || $job->getOwner()->getId() !== $this->getUser()->getId()) {
			return $this->redirectToRoute('jobs_index');
		}

		$firms = $firmRepository->findBy([
			'director' => $this->getUser()->getId()
		]);

		$editedJob = new Job();

		$form = $this->createForm(JobType::class, $editedJob);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$submittedToken = $request->request->get('_csrf_token');

			if (!$this->isCsrfTokenValid('job_edit', $submittedToken)) {
				return $this->redirectToRoute('jobs_index');
			}

			if (count($firms) === 0) {
				$this->addFlash('job', 'The firm can not be null.');

				return $this->render('job/edit.html.twig', [
					'firms' => $firms,
					'job' => $job,
					'data' => $form->getData()
				]);
			}

			$errors = $validator->validate($editedJob);

			$errorMessages = $this->getErrorMessages($errors);

			$selectedFirmName = $request->request->get('job')['firm'] ?? null;

			$selectedFirm = null;

			foreach ($firms as $firm) {
				if ($firm->getName() === $selectedFirmName) {
					$selectedFirm = $firm;
				}
			}

			if ($selectedFirm === null) {
				$errorMessages[] = [
					'type' => 'firm',
					'message' => 'Invalid firm.'
				];
			}

			if (count($errorMessages) > 0) {
				return $this->render('job/edit.html.twig', [
					'firms' => $firms,
					'errors' => $errorMessages,
					'job' => $job,
					'data' => $form->getData()
				]);
			}

			$job
				->setFirm($selectedFirm)
				->setTitle($editedJob->getTitle())
				->setDescription($editedJob->getDescription())
				->setLocation($editedJob->getLocation())
				->setAddress($editedJob->getAddress())
				->setRequirements($editedJob->getRequirements())
				->setObligations($editedJob->getObligations());

			$manager = $this->getDoctrine()->getManager();
			$manager->persist($job);
			$manager->flush();

			$this->addFlash('success', 'The job was edited successfully');

			return $this->redirectToRoute('jobs_get_one', [
				'id' => $job->getId()
			]);
		}

		return $this->render('job/edit.html.twig', [
			'job' => $job,
			'firms' => $firms
		]);
	}

	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/jobs/{id}/delete", name="jobs_delete")
	 */
	public function delete(?Job $job)
	{
		if (!$job || $job->getOwner()->getId() !== $this->getUser()->getId()) {
			return $this->redirectToRoute('jobs_index');
		}

		$manager = $this->getDoctrine()->getManager();
		$manager->remove($job);
		$manager->flush();

		$this->addFlash(
			'success',
			'The job was deleted successfully'
		);

		return $this->redirectToRoute('user_profile', [
			'id' => $this->getUser()->getId()
		]);
	}

	/**
	 * @Route("/jobs/{id}", name="jobs_get_one")
	 */
	public function getOne(?Job $job)
	{
		if (!$job) {
			return $this->redirectToRoute('jobs_index');
		}

		$job->setViews($job->getViews() + 1);

		$isJobInFavorite = false;

		if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
			foreach ($this->getUser()->getFavoriteJobs() as $userJob) {
				if ($userJob->getId() === $job->getId()) {
					$isJobInFavorite = true;
				}
			}
		}

		$manager = $this->getDoctrine()->getManager();
		$manager->persist($job);
		$manager->flush();

		return $this->render('job/job.html.twig', [
			'job' => $job,
			'isJobInFavorite' => $isJobInFavorite,
		]);
	}

	/**
	 * @Route("/jobs/{id}/add-to-favorite", name="jobs_add_to_favorite")
	 */
	public function addJobToUserFavorites(?Job $job)
	{
		if (!$job) {
			return $this->redirectToRoute('jobs_index');
		}

		$this->getUser()->addFavoriteJob($job);

		$manager = $this->getDoctrine()->getManager();
		$manager->persist($job);
		$manager->persist($this->getUser());
		$manager->flush();

		$this->addFlash(
			'job_added_to_favorite',
			'The job was added to your favorites successfully'
		);

		return $this->redirectToRoute('jobs_get_one', [
			'id' => $job->getId()
		]);
	}

	/**
	 * @Route("/jobs/{id}/remove-from-favorite", name="jobs_remove_from_favorite")
	 */
	public function removeJobToUserFavorites(?Job $job)
	{
		if (!$job) {
			return $this->redirectToRoute('jobs_index');
		}

		$this->getUser()->removeFavoriteJob($job);

		$manager = $this->getDoctrine()->getManager();
		$manager->persist($job);
		$manager->persist($this->getUser());
		$manager->flush();

		$this->addFlash(
			'job_added_to_favorite',
			'The job was removed from your favorites successfully'
		);

		return $this->redirectToRoute('jobs_get_one', [
			'id' => $job->getId()
		]);
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
