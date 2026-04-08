<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'blog_groups')]
#[ORM\Index(name: 'fk_blog_group_modifier_fk', columns: ['modifier'])]
#[ORM\Index(name: 'fk_blog_group_creator_fk', columns: ['creator'])]
#[ORM\Entity]
class BlogGroups
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 250, nullable: false)]
    private $name;

    #[ORM\Column(name: 'seo_link', type: 'string', length: 250, nullable: false)]
    private $seoLink;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private $created;

    #[ORM\Column(name: 'modified', type: 'datetime', nullable: true)]
    private $modified;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'id', nullable: false)]
    private $creator;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'id', nullable: true)]
    private $modifier;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSeoLink(): ?string
    {
        return $this->seoLink;
    }

    public function setSeoLink(string $seoLink): self
    {
        $this->seoLink = $seoLink;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(?\DateTimeInterface $modified): self
    {
        $this->modified = $modified;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getModifier(): ?User
    {
        return $this->modifier;
    }

    public function setModifier(?User $modifier): self
    {
        $this->modifier = $modifier;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }
}
