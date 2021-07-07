<?php

namespace App\Entity;

use App\Repository\JobRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Firm;
use App\Entity\User;
use DateTime;
use DateTimeInterface;

/**
 * @ORM\Entity(repositoryClass=JobRepository::class)
 * @ORM\Table(name="jobs")
 */
class Job
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * @Assert\NotNull(message="Title can not be null.")
	 * @Assert\Regex(pattern="/^.{3,}$/", match=true, message="Title must be at least 3 symbols")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $title;

	/**
	 * @Assert\NotNull(message="Location can not be null.")
	 * @Assert\Regex(pattern="/^[A-z\s]{3,}$/", match=true, message="Location must be at least 3 symbols and only letters.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $location;

	/**
	 * @Assert\NotNull(message="Address can not be null.")
	 * @Assert\Regex(pattern="/^.{3,}$/", match=true, message="Address must be at least 3 symbols.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $address;

	/**
	 * @Assert\NotNull(message="Description can not be null.")
	 * @Assert\Regex(pattern="/^.{5,}$/", match=true, message="Description must be at least 5 symbols.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $description;

	/**
	 * @Assert\NotNull(message="Requirements can not be null.")
	 * @Assert\Regex(pattern="/^.{5,}$/s", match=true, message="Requirements must be at least 5 symbols.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $requirements;

	/**
	 * @Assert\NotNull(message="Obligations can not be null.")
	 * @Assert\Regex(pattern="/^.{5,}$/s", match=true, message="Obligations must be at least 5 symbols.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $obligations;

	/**
	 * @ORM\ManyToOne(targetEntity=Firm::class, inversedBy="jobs")
	 * @ORM\JoinColumn(name="firm_id", nullable=false)
	 */
	private Firm $firm;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="jobs")
	 * @ORM\JoinColumn(name="owner_id", nullable=false)
	 */
	private User $owner;

	/**
	 * @ORM\Column(type="date", name="date_added")
	 */
	private DateTimeInterface $dateAdded;

	/**
	 * @ORM\Column(type="integer", options={"default":0})
	 */
	private int $views;

	/**
	 * @ORM\ManyToMany(targetEntity=User::class, mappedBy="favoriteJobs")
	 */
	private $users;

	public function __construct()
	{
		$this->dateAdded = new DateTime('now');
		$this->views = 0;
		$this->users = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTitle(): ?string
	{
		return $this->title ?? null;
	}

	public function setTitle(string $title): self
	{
		$this->title = $title;

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

	public function getAddress(): ?string
	{
		return $this->address ?? null;
	}

	public function setAddress(string $address): self
	{
		$this->address = $address;

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

	public function getRequirements(): ?string
	{
		return $this->requirements ?? null;
	}

	public function setRequirements(string $requirements): self
	{
		$this->requirements = $requirements;

		return $this;
	}

	public function requirementsToArray(): array
	{
		$requirementsToArray = explode('; ', $this->requirements);

		return $requirementsToArray;
	}

	public function getObligations(): ?string
	{
		return $this->obligations ?? null;
	}

	public function setObligations(string $obligations): self
	{
		$this->obligations = $obligations;

		return $this;
	}

	public function obligationsToArray(): array
	{
		$obligationssToArray = explode('; ', $this->obligations);

		return $obligationssToArray;
	}

	public function getFirm(): ?Firm
	{
		return $this->firm ?? null;
	}

	public function setFirm(?Firm $firm): self
	{
		$this->firm = $firm;

		return $this;
	}

	public function getOwner(): ?User
	{
		return $this->owner;
	}

	public function setOwner(?User $owner): self
	{
		$this->owner = $owner;

		return $this;
	}

	public function getDateAdded(): ?\DateTimeInterface
	{
		return $this->dateAdded;
	}

	public function setDateAdded(\DateTimeInterface $dateAdded): self
	{
		$this->dateAdded = $dateAdded;

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

	/**
	 * @return Collection|User[]
	 */
	public function getUsers(): Collection
	{
		return $this->users;
	}

	public function addUser(User $user): self
	{
		if (!$this->users->contains($user)) {
			$this->users[] = $user;
			$user->addFavoriteJob($this);
		}

		return $this;
	}

	public function removeUser(User $user): self
	{
		if ($this->users->removeElement($user)) {
			$user->removeFavoriteJob($this);
		}

		return $this;
	}
}
