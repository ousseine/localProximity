<?php

namespace App\Entity;

use App\Repository\EmailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\UniqueConstraint(name: 'UNIQ_EMAIL', fields: ['email'])]
#[UniqueEntity('email', message: "Votre email est déjà envoyé")]
#[ORM\Entity(repositoryClass: EmailRepository::class)]
class Email
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Assert\Email]
    #[Assert\NotBlank(message: "Ce champ est ne doit pas être vide.")]
    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
    private ?string $email = null;

    public function __toString(): string
    {
        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
