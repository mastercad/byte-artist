<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * Projects
 *
 * @ORM\Table(
 *  name="projects",
 *  indexes={
 *      @ORM\Index(name="fk_project_modifier", columns={"modifier"}),
 *      @ORM\Index(name="fk_project_state", columns={"state_fk"}),
 *      @ORM\Index(name="fk_project_creator", columns={"creator"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ProjectsRepository")
 */
class Projects
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="short_description", type="text", length=65535, nullable=true)
     */
    private $shortDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(name="preview_picture", type="string", length=250, nullable=true)
     */
    private $previewPicture;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="link", type="string", length=250, nullable=true)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_link", type="string", length=250, nullable=false)
     */
    private $seoLink;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=false)
     */
    private $isPublic;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var string|null
     *
     * @ORM\Column(name="original_link", type="string", length=250, nullable=true)
     */
    private $originalLink;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator", referencedColumnName="id", nullable=false)
     * })
     */
    private $creator;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modifier", referencedColumnName="id", nullable=true)
     * })
     */
    private $modifier;

    /**
     * @var ProjectStates
     *
     * @ORM\ManyToOne(targetEntity="ProjectStates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_fk", referencedColumnName="id")
     * })
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity="ProjectTags", mappedBy="project")
     */
    private $projectTags;

    public function __construct()
    {
        $this->projectTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getPreviewPicture(): ?string
    {
        return $this->previewPicture;
    }

    public function setPreviewPicture(?string $previewPicture): self
    {
        $this->previewPicture = $previewPicture;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getSeoLink(): ?string
    {
        return $this->seoLink;
    }

    public function setSeoLink(?string $seoLink): self
    {
        $this->seoLink = $seoLink;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

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

    public function getOriginalLink(): ?string
    {
        return $this->originalLink;
    }

    public function setOriginalLink(?string $originalLink): self
    {
        $this->originalLink = $originalLink;

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

    public function getState(): ?ProjectStates
    {
        return $this->state;
    }

    public function setState(?ProjectStates $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection|Tags[]
     */
    public function getProjectTags(): Collection
    {
        return $this->projectTags;
    }

    public function addProjectTag(ProjectTags $projectTag): self
    {
        if (!$this->projectTags->contains($projectTag)) {
            $this->projectTags[] = $projectTag;
//            $tag->setProjects($this);
        }

        return $this;
    }

    public function removeProjectTag(ProjectTags $projectTag): self
    {
        if ($this->projectTags->contains($projectTag)) {
            $this->projectTags->removeElement($projectTag);
            // set the owning side to null (unless already changed)
            if ($projectTag->getProjects() === $this) {
                $projectTag->setProjects(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
