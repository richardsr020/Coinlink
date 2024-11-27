<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $is_email_verified = false;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $has_accepted_terms = false;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $activation_token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\OneToOne(mappedBy: 'userid', cascade: ['persist', 'remove'])]
    private ?Account $account = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'authorid', orphanRemoval: true)]
    private Collection $posts;

    #[ORM\Column]
    public ?bool $islocked = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'sendto', orphanRemoval: true)]
    private Collection $notification;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender', orphanRemoval: true)]
    private Collection $messages;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'receiver', orphanRemoval: true)]
    private Collection $receivedMessage;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fullname = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $brithdate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $humanproofimage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idcard = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idcardback = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $residenceproofimage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->notification = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->receivedMessage = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->is_email_verified ?? false;
    }

    public function setEmailVerified(bool $is_email_verified): static
    {
        $this->is_email_verified = $is_email_verified;

        return $this;
    }

    public function hasAcceptedTerms(): bool
    {
        return $this->has_accepted_terms ?? false;
    }

    public function setHasAcceptedTerms(bool $has_accepted_terms): static
    {
        $this->has_accepted_terms = $has_accepted_terms;

        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activation_token;
    }

    public function setActivationToken(?string $activation_token): static
    {
        $this->activation_token = $activation_token;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        if ($account->getUserid() !== $this) {
            $account->setUserid($this);
        }

        $this->account = $account;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials():void
    {
        // Optionnel : supprimer des données sensibles si nécessaire
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthorid($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthorid() === $this) {
                $post->setAuthorid(null);
            }
        }

        return $this;
    }

    public function islocked(): ?bool
    {
        return $this->islocked;
    }

    public function setLocked(bool $islocked): static
    {
        $this->islocked = $islocked;

        return $this;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }
    public function __toString(): string
    {
        // Retournez une propriété que vous voulez afficher, comme l'email ou le nom
        return $this->username ?? '';
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notification->contains($notification)) {
            $this->notification->add($notification);
            $notification->setSendto($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notification->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getSendto() === $this) {
                $notification->setSendto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReceivedMessage(): Collection
    {
        return $this->receivedMessage;
    }

    public function addReceivedMessage(Message $receivedMessage): static
    {
        if (!$this->receivedMessage->contains($receivedMessage)) {
            $this->receivedMessage->add($receivedMessage);
            $receivedMessage->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): static
    {
        if ($this->receivedMessage->removeElement($receivedMessage)) {
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getReceiver() === $this) {
                $receivedMessage->setReceiver(null);
            }
        }

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(?string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getBrithdate(): ?\DateTimeImmutable
    {
        return $this->brithdate;
    }

    public function setBrithdate(?\DateTimeImmutable $brithdate): static
    {
        $this->brithdate = $brithdate;

        return $this;
    }

    public function getHumanproofimage(): ?string
    {
        return $this->humanproofimage;
    }

    public function setHumanproofimage(?string $humanproofimage): static
    {
        $this->humanproofimage = $humanproofimage;

        return $this;
    }

    public function getIdcard(): ?string
    {
        return $this->idcard;
    }

    public function setIdcard(?string $idcard): static
    {
        $this->idcard = $idcard;

        return $this;
    }

    public function getIdcardback(): ?string
    {
        return $this->idcardback;
    }

    public function setIdcardback(?string $idcardback): static
    {
        $this->idcardback = $idcardback;

        return $this;
    }

    public function getResidenceproofimage(): ?string
    {
        return $this->residenceproofimage;
    }

    public function setResidenceproofimage(?string $residenceproofimage): static
    {
        $this->residenceproofimage = $residenceproofimage;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

}
