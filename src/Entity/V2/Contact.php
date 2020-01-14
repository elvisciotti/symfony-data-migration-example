<?php

namespace App\Entity\V2;

use App\Entity\V2\Traits\AuditTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Table(name="contact")
 * @ORM\Entity(repositoryClass="App\Repository\ContactRepository")
 */
class Contact
{
    use AuditTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var ArrayCollection|Employment[]
     *
     * @ORM\OneToMany(targetEntity="Employment", mappedBy="contact")
     */
    private $employments;

    /**
     * Contact constructor.
     * @param string|null $name
     * @param bool $active
     */
    public function __construct(string $name, bool $active)
    {
        $this->name = $name;
        $this->active = $active;
        $this->createdOn = new \DateTime();
        $this->updatedOn = new \DateTime();
        $this->employments = new ArrayCollection();
    }


    /**
     * @Groups("contact-id")
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    /**
     * @Groups("contact")
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
    }

    /**
     * @Groups("contact")
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @Groups("contact")
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @Groups("contact")
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @Groups("contact-employments")
     * @return Employment[]|ArrayCollection
     */
    public function getEmployments()
    {
        return $this->employments;
    }
}
