<?php

namespace App\Entity;

use App\Repository\FilterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

class Filter
{
    private ?int $id = null;

    private ?Site $site = null;

    private ?string $nom = null;

    private ?\DateTimeInterface $dateHeureDebut = null;

    private ?\DateTimeInterface $dateHeureFin = null;

    private ?bool $sortieOrganisateur = null;

    private ?bool $sortieInscrit = null;

    private ?bool $sortiePasInscrit = null;

    private ?bool $sortiePasse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(?\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDateHeureFin(): ?\DateTimeInterface
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin(?\DateTimeInterface $dateHeureFin): self
    {
        $this->dateHeureFin = $dateHeureFin;

        return $this;
    }

    public function isSortieOrganisateur(): ?bool
    {
        return $this->sortieOrganisateur;
    }

    public function setSortieOrganisateur(bool $sortieOrganisateur): self
    {
        $this->sortieOrganisateur = $sortieOrganisateur;

        return $this;
    }

    public function isSortieInscrit(): ?bool
    {
        return $this->sortieInscrit;
    }

    public function setSortieInscrit(bool $sortieInscrit): self
    {
        $this->sortieInscrit = $sortieInscrit;

        return $this;
    }

    public function isSortiePasInscrit(): ?bool
    {
        return $this->sortiePasInscrit;
    }

    public function setSortiePasInscrit(bool $sortiePasInscrit): self
    {
        $this->sortiePasInscrit = $sortiePasInscrit;

        return $this;
    }

    public function isSortiePasse(): ?bool
    {
        return $this->sortiePasse;
    }

    public function setSortiePasse(bool $sortiePasse): self
    {
        $this->sortiePasse = $sortiePasse;

        return $this;
    }
}
