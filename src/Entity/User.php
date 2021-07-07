<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * @Assert\NotNull(message="Email can not be null.")
	 * @Assert\Email(message = "The email is not a valid email.")
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private string $email;

	/**
	 * @Assert\NotNull(message="First Name can not be null.")
	 * @Assert\Regex(pattern="/^[A-Z]+[A-z]{2,15}$/", match=true, message="The First Name must start with UPPER letter. Letters range 3 - 15.")
	 * @ORM\Column(type="string", length=255, name="first_name")
	 */
	private string $firstName;

	/**
	 * @Assert\NotNull(message="Last Name can not be null.")
	 * @Assert\Regex(pattern="/^[A-Z]+[A-z]{2,15}$/", match=true, message="The Last Name must start with UPPER letter. Letters range 3 - 15.")
	 * @ORM\Column(type="string", length=255, name="last_name")
	 */
	private string $lastName;

	/**
	 * @Assert\NotNull(message="Password can not be null.")
	 * @Assert\Regex(pattern="/^[0-9A-z]{6,}$/", match=true, message="The password must be min 6 characters. Only numbers and letters.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $password;

	private string $repeatedPassword;

	private $roles;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true, name="avatar_url")
	 */
	private string $avatarUrl;

	/**
	 * @ORM\OneToMany(targetEntity=Firm::class, mappedBy="director")
	 */
	private Collection $firms;

	/**
	 * @ORM\OneToMany(targetEntity=Job::class, mappedBy="owner")
	 */
	private Collection $jobs;

	/**
	 * @Assert\NotNull(message="Description can not be null.")
	 * @Assert\Regex(pattern="/^.{5,}$/", match=true, message="The Description must be min 5 characters.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $description;

	/**
	 * @Assert\NotNull(message="Location can not be null.")
	 * @Assert\Regex(pattern="/^[A-z\s]{3,}$/", match=true, message="The Location must be min 3 characters. Only letters.")
	 * @ORM\Column(type="string", length=255)
	 */
	private string $location;

	/**
	 * @ORM\ManyToMany(targetEntity=Job::class, inversedBy="users")
	 * @ORM\JoinTable(name="users_favorite_jobs",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="job_id", referencedColumnName="id")}
	 *      )
	 */
	private Collection $favoriteJobs;

	/**
	 * @var User[]|Collection|ArrayCollection $sendedMessages
	 * @ORM\OneToMany(targetEntity=Message::class, mappedBy="sender")
	 */
	private Collection $sendedMessages;

	/**
	 * @var User[]|Collection|ArrayCollection $receivedMessages
	 * @ORM\OneToMany(targetEntity=Message::class, mappedBy="recipient")
	 */
	private Collection $receivedMessages;

	/**
	 * @Assert\NotNull(message="Phone can not be null.")
	 * @Assert\Regex(pattern="/^[0-9]{10,12}$/", match=true, message="Phone must be only digits from 10 to 12 numbers.")
	 * @ORM\Column(type="string", length=255)
	 */
	private $phone;

	public function __construct()
	{
		$this->firms = new ArrayCollection();
		$this->jobs = new ArrayCollection();
		$this->favoriteJobs = new ArrayCollection();
		$this->sendedMessages = new ArrayCollection();
		$this->receivedMessages = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getEmail(): ?string
	{
		return $this->email ?? null;
	}

	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}

	public function getFirstName(): ?string
	{
		return $this->firstName ?? null;
	}

	public function setFirstName(string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	public function getLastName(): ?string
	{
		return $this->lastName ?? null;
	}

	public function setLastName(string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getPassword(): ?string
	{
		return $this->password ?? null;
	}

	public function setPassword(string $password): self
	{
		$this->password = $password;

		return $this;
	}

	public function getRepeatedPassword(): ?string
	{
		return $this->repeatedPassword ?? null;
	}

	/**
	 * @return self
	 */
	public function setRepeatedPassword($repeatedPassword): self
	{
		$this->repeatedPassword = $repeatedPassword;

		return $this;
	}

	public function isPasswordsEqual(): bool
	{
		return $this->getPassword() === $this->getRepeatedPassword();
	}

	/**
	 * @see UserInterface
	 */
	public function getUsername(): string
	{
		return (string) $this->email;
	}

	public function getAvatarUrl(): ?string
	{
		return $this->avatarUrl ?? null;
	}

	public function setAvatarUrl(?string $avatarUrl): self
	{
		$this->avatarUrl = $avatarUrl;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		// $roles = [];

		// foreach ($this->roles as $role) {
		// 	$roles[] = $role->getName();
		// }

		// return $roles;

		return ['ROLE_USER'];
	}

	public function addRole($role): self
	{
		$this->roles[] = $role;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getSalt(): ?string
	{
		return null;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials()
	{
	}

	/**
	 * @return Collection|Firm[]
	 */
	public function getFirms(): Collection
	{
		return $this->firms;
	}

	public function addFirm(Firm $firm): self
	{
		if (!$this->firms->contains($firm)) {
			$this->firms[] = $firm;
			$firm->setDirector($this);
		}

		return $this;
	}

	public function removeFirm(Firm $firm): self
	{
		if ($this->firms->removeElement($firm)) {
			// set the owning side to null (unless already changed)
			if ($firm->getDirector() === $this) {
				$firm->setDirector(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|Job[]
	 */
	public function getJobs(): Collection
	{
		return $this->jobs;
	}

	public function addJob(Job $job): self
	{
		if (!$this->jobs->contains($job)) {
			$this->jobs[] = $job;
			$job->setOwner($this);
		}

		return $this;
	}

	public function removeJob(Job $job): self
	{
		if ($this->jobs->removeElement($job)) {
			// set the owning side to null (unless already changed)
			if ($job->getOwner() === $this) {
				$job->setOwner(null);
			}
		}

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

	public function getDescription(): ?string
	{
		return $this->description ?? null;
	}

	public function setDescription(string $description): self
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @return Collection|Job[]
	 */
	public function getFavoriteJobs(): Collection
	{
		return $this->favoriteJobs;
	}

	public function addFavoriteJob(Job $favoriteJob): self
	{
		if (!$this->favoriteJobs->contains($favoriteJob)) {
			$this->favoriteJobs[] = $favoriteJob;
		}

		return $this;
	}

	public function removeFavoriteJob(Job $favoriteJob): self
	{
		$this->favoriteJobs->removeElement($favoriteJob);

		return $this;
	}

	/**
	 * @return Collection|Message[]
	 */
	public function getSendedMessages(): Collection
	{
		return $this->sendedMessages;
	}

	public function addSendedMessage(Message $sendedMessage): self
	{
		if (!$this->sendedMessages->contains($sendedMessage)) {
			$this->sendedMessages[] = $sendedMessage;
			$sendedMessage->setSender($this);
		}

		return $this;
	}

	public function removeSendedMessage(Message $sendedMessage): self
	{
		if ($this->sendedMessages->removeElement($sendedMessage)) {
			// set the owning side to null (unless already changed)
			if ($sendedMessage->getSender() === $this) {
				$sendedMessage->setSender(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|Message[]
	 */
	public function getReceivedMessages(): Collection
	{
		return $this->receivedMessages;
	}

	public function addReceivedMessage(Message $receivedMessage): self
	{
		if (!$this->receivedMessages->contains($receivedMessage)) {
			$this->receivedMessages[] = $receivedMessage;
			$receivedMessage->setRecipient($this);
		}

		return $this;
	}

	public function removeReceivedMessage(Message $receivedMessage): self
	{
		if ($this->receivedMessages->removeElement($receivedMessage)) {
			// set the owning side to null (unless already changed)
			if ($receivedMessage->getRecipient() === $this) {
				$receivedMessage->setRecipient(null);
			}
		}

		return $this;
	}

	public function getUnreadMessages()
	{
		$allReceivedMessages = [];

		foreach ($this->getReceivedMessages() as $message) {
			$allReceivedMessages[] = $message;
		}

		// Return only unreaded
		return array_filter($allReceivedMessages, function ($message) {
			return $message->getIsRead() ? null : $message;
		});
	}

	public function getReadMessages()
	{
		$allReceivedMessages = [];

		foreach ($this->getReceivedMessages() as $message) {
			$allReceivedMessages[] = $message;
		}

		// Return only readed
		return array_filter($allReceivedMessages, function ($message) {
			return $message->getIsRead() ? $message : null;
		});
	}

	public function getPhone(): ?string
	{
		return $this->phone;
	}

	public function setPhone(?string $phone): self
	{
		$this->phone = $phone;

		return $this;
	}
}
