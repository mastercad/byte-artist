<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProjectTags
 *
 * @ORM\Table(
 *  name="project_tags",
 *  indexes={
 *      @ORM\Index(name="fk_project_modifier_fk", columns={"modifier"}),
 *      @ORM\Index(name="fk_project_tags_project_fk", columns={"project_fk"}),
 *      @ORM\Index(name="fk_project_tags_tag_fk", columns={"tag_fk"}),
 *      @ORM\Index(name="fk_project_creator_fk", columns={"creator"})
 *  }
 * )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ProjectTags
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

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
     * @var Projects
     *
     * @ORM\ManyToOne(targetEntity="Projects", cascade={"persist"}, inversedBy="projectTags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_fk", referencedColumnName="id", nullable=false)
     * })
     */
    private $project;

    /**
     * @var Tags
     *
     * @ORM\ManyToOne(targetEntity="Tags", cascade={"persist"}, inversedBy="projectTags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_fk", referencedColumnName="id", nullable=false)
     * })
     */
    private $tag;

    private $projects;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProject(): ?Projects
    {
        return $this->project;
    }

    public function setProject(?Projects $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getTag(): ?Tags
    {
        return $this->tag;
    }

    public function setTag(?Tags $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getProjects(): ?Projects
    {
        return $this->projects;
    }

    public function setProjects(?Projects $projects): self
    {
        $this->projects = $projects;

        return $this;
    }

    public function __toString()
    {
        return $this->getTag()->getName();
    }
}
