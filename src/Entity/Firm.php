<?php

namespace App\Entity;

use App\Repository\FirmRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use App\Entity\Job;

/**
 * @ORM\Entity(repositoryClass=FirmRepository::class)
 * @ORM\Table(name="firms")
 */
class Firm
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * @Assert\NotNull(message="Name can not be null.")
	 * @Assert\Regex(pattern="/^[A-z\s]{2,}$/", match=true, message="Name must be at least 2 symbols and only letters.")
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private string $name;

	/**
	 * @Assert\NotNull(message="Description can not be null.")
	 * @Assert\Regex(pattern="/^.{5,}$/", match=true, message="Description must be at least 5 symbols.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $description;

	/**
	 * @Assert\NotNull(message="Location can not be null.")
	 * @Assert\Regex(pattern="/^[A-z\s]{3,}$/", match=true, message="Location must be at least 3 symbols and only letters.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $location;

	/**
	 * @ORM\Column(type="date")
	 */
	private DateTimeInterface $dateOfCreation;

	/**
	 * @Assert\NotNull(message="Baner can not be null.")
	 * @ORM\Column(type="string", length=255, name="baner_url")
	 */
	private string $banerUrl;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="firms")
	 * @ORM\JoinColumn(name="director_id", nullable=false)
	 */
	private User $director;

	/**
	 * @ORM\OneToMany(targetEntity=Job::class, mappedBy="firm")
	 * @param Collection|ArrayCollection|Job[] $jobs
	 */
	private Collection $jobs;

	/**
	 * @ORM\Column(type="integer", options={"default":0})
	 */
	private $views;

	/**
	 * @Assert\NotNull(message="Phone can not be null.")
	 * @Assert\Regex(pattern="/^[0-9]{10,12}$/", match=true, message="Phone must be only digits from 10 to 12 numbers.")
	 * @ORM\Column(type="string", length=255)
	 */
	private $phone;

	public function __construct()
	{
		$this->dateOfCreation = new DateTime('now');
		$this->jobs = new ArrayCollection();
		$this->views = 0;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getName(): ?string
	{
		return $this->name ?? null;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description ?? null;
	}

	public function setDescription(string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getLocation(): ?string
	{
		return $this->location ?? null;
	}

	public function setLocation(string $location): self
	{
		$this->location = $location;

		return $this;
	}

	public function getDateOfCreation(): ?DateTimeInterface
	{
		return $this->dateOfCreation;
	}

	public function setDateOfCreation(DateTimeInterface $dateOfCreation): self
	{
		$this->dateOfCreation = $dateOfCreation;

		return $this;
	}

	public function getBanerUrl(): ?string
	{
		return $this->banerUrl ?? null;
	}

	public function setBanerUrl(string $banerUrl): self
	{
		$this->banerUrl = $banerUrl;

		return $this;
	}

	public function getDirector(): ?User
	{
		return $this->director;
	}

	public function setDirector(?User $director): self
	{
		$this->director = $director;

		return $this;
	}

	/**
	 * @return Collection|ArrayCollection|Job[]
	 */
	public function getJobs(): Collection
	{
		return $this->jobs;
	}

	public function addJob(Job $job): self
	{
		if (!$this->jobs->contains($job)) {
			$this->jobs[] = $job;
			$job->setFirm($this);
		}

		return $this;
	}

	public function removeJob(Job $job): self
	{
		if ($this->jobs->removeElement($job)) {
			// set the owning side to null (unless already changed)
			if ($job->getFirm() === $this) {
				$job->setFirm(null);
			}
		}

		return $this;
	}

	public function getViews(): ?int
	{
		return $this->views;
	}

	public function setViews(int $views): self
	{
		$this->views = $views;

		return $this;
	}

	public function getPhone(): ?string
	{
		return $this->phone;
	}

	public function setPhone(string $phone): self
	{
		$this->phone = $phone;

		return $this;
	}
}
