<?php

namespace App\Controller;

use App\Entity\Firm;
use App\Form\FirmType;
use App\Repository\FirmRepository;
use App\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FirmController extends AbstractController
{
	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/firms/add", name="firms_add")
	 */
	public function add(Request $request, ValidatorInterface $validator, FirmRepository $firmRepository)
	{
		$firm = new Firm();

		$form = $this->createForm(FirmType::class, $firm);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$submittedToken = $request->request->get('_csrf_token');

			if (!$this->isCsrfTokenValid('firm_create', $submittedToken)) {
				return $this->redirectToRoute('jobs_index');
			}

			$firmByNameExists = $firmRepository->findOneBy([
				'name' => $firm->getName()
			]);

			if ($firmByNameExists) {
				$this->addFlash('firm', 'The name already exists.');

				return $this->render('firm/add.html.twig', [
					'data' => $form->getData()
				]);
			}

			$errors = $validator->validate($firm);

			$errorMessages = $this->getErrorMessages($errors);

			if (count($errorMessages) > 0) {
				return $this->render('firm/add.html.twig', [
					'errors' => $errorMessages,
					'data' => $form->getData()
				]);
			}

			$firm->setDirector($this->getUser());

			$manager = $this->getDoctrine()->getManager();
			$manager->persist($firm);
			$manager->flush();

			$this->addFlash('success', 'The firm was created successfully');

			return $this->redirectToRoute('user_profile', [
				'id' => $this->getUser()->getId()
			]);
		}

		return $this->render('firm/add.html.twig', [
			'data' => $form->getData()
		]);
	}

	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/firms/{id}/edit", name="firms_edit")
	 */
	public function edit(
		?Firm $firm,
		Request $request,
		FirmRepository $firmRepository,
		ValidatorInterface $validator
	) {
		if (!$firm || $firm->getDirector()->getId() !== $this->getUser()->getId()) {
			return $this->redirectToRoute('jobs_index');
		}

		$editedFirm = new Firm();

		$form = $this->createForm(FirmType::class, $editedFirm);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$submittedToken = $request->request->get('_csrf_token');

			if (!$this->isCsrfTokenValid('firm_edit', $submittedToken)) {
				return $this->redirectToRoute('jobs_index');
			}

			$firmByNameExists = $firmRepository->findOneBy([
				'name' => $editedFirm->getName()
			]);

			if ($firmByNameExists && $firmByNameExists->getName() !== $firm->getName()) {
				$this->addFlash('firm', 'The name already exists.');

				return $this->render('firm/edit.html.twig', [
					'data' => $form->getData(),
					'firm' => $firm,
				]);
			}

			$errors = $validator->validate($editedFirm);

			$errorMessages = $this->getErrorMessages($errors);

			if (count($errorMessages) > 0) {
				return $this->render('firm/edit.html.twig', [
					'errors' => $errorMessages,
					'data' => $form->getData(),
					'firm' => $firm,
				]);
			}

			$firm
				->setName($editedFirm->getName())
				->setDescription($editedFirm->getDescription())
				->setLocation($editedFirm->getLocation())
				->setBanerUrl($editedFirm->getBanerUrl())
				->setPhone($editedFirm->getPhone());

			$manager = $this->getDoctrine()->getManager();
			$manager->persist($firm);
			$manager->flush();

			$this->addFlash('success', 'The firm was edited successfully');

			return $this->redirectToRoute('user_profile', [
				'id' => $this->getUser()->getId()
			]);
		}

		return $this->render('firm/edit.html.twig', [
			'firm' => $firm
		]);
	}

	/**
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 * @Route("/firms/{id}/delete", name="firms_delete")
	 */
	public function delete(?Firm $firm)
	{
		if (!$firm || $firm->getDirector()->getId() !== $this->getUser()->getId()) {
			return $this->redirectToRoute('jobs_index');
		}

		$manager = $this->getDoctrine()->getManager();
		$manager->remove($firm);
		$manager->flush();

		$this->addFlash(
			'success',
			'The firm was deleted successfully'
		);

		return $this->redirectToRoute('user_profile', [
			'id' => $this->getUser()->getId()
		]);
	}

	/**
	 * @Route("/firms/{id}", name="firms_get_one")
	 */
	public function getOne(?Firm $firm)
	{
		if (!$firm) {
			return $this->redirectToRoute('jobs_index');
		}

		$firm->setViews($firm->getViews() + 1);

		$manager = $this->getDoctrine()->getManager();
		$manager->persist($firm);
		$manager->flush();

		return $this->render('firm/firm.html.twig', [
			'firm' => $firm
		]);
	}

	/**
	 * @Route("/firms/{id}/jobs", name="firms_jobs")
	 */
	public function getAll(Firm $firm, JobRepository $jobRepository)
	{
		if (!$firm) {
			return $this->redirectToRoute('jobs_index');
		}

		$jobs = $jobRepository->findBy([
			'firm' => $firm->getId()
		]);

		return $this->render('firm/jobs.html.twig', [
			'firm' => $firm,
			'jobs' => $jobs
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
