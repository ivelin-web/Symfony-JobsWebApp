<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 * @ORM\Table(name="users_messages")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Assert\NotNull(message="Text can not be null.")
     * @Assert\Regex(pattern="/^.{5,1000}$/", match=true, message="The Text must be in range 5 - 1000 symbols.")
     * @ORM\Column(type="text")
     */
    private string $text;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sendedMessages")
     * @ORM\JoinColumn(name="sender_id", nullable=false)
     */
    private User $sender;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="receivedMessages")
     * @ORM\JoinColumn(name="recipient_id", nullable=false)
     */
    private User $recipient;

    /**
     * @ORM\Column(type="boolean", name="is_read", options={"default":0})
     */
    private bool $isRead;

    /**
     * @ORM\Column(type="datetime", name="date_sended")
     */
    private $dateSended;

    public function __construct()
    {
        $this->isRead = false;
        $this->dateSended = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getDateSended(): ?\DateTimeInterface
    {
        return $this->dateSended;
    }

    public function setDateSended(\DateTimeInterface $dateSended): self
    {
        $this->dateSended = $dateSended;

        return $this;
    }
}
