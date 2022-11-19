<?php

namespace App\Entity;

use App\Repository\NotaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotaRepository::class)
 */
class Nota
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titulo;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $publica;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notas", cascade={"persist"})
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="notas", cascade={"persist"})
     */
    private $tags;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $iseliminada;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaeliminada;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(?string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getPublica(): ?bool
    {
        return $this->publica;
    }

    public function setPublica(?bool $publica): self
    {
        $this->publica = $publica;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function __toString(): string
    {
        return $this->getTitulo();
    }

    public function getIseliminada(): ?bool
    {
        return $this->iseliminada;
    }

    public function setIseliminada(?bool $iseliminada): self
    {
        $this->iseliminada = $iseliminada;

        return $this;
    }

    public function getFechaeliminada(): ?\DateTimeInterface
    {
        return $this->fechaeliminada;
    }

    public function setFechaeliminada(?\DateTimeInterface $fechaeliminada): self
    {
        $this->fechaeliminada = $fechaeliminada;

        return $this;
    }
}
