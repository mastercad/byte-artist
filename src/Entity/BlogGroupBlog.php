<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogGroupBlog.
 *
 * @ORM\Table(
 *  name="blog_group_blog",
 *  indexes={
 *      @ORM\Index(name="fk_blog_group_blog_group_fk", columns={"group_fk"}),
 *      @ORM\Index(name="fk_blog_group_creator", columns={"creator"}),
 *      @ORM\Index(name="fk_blog_group_modifier", columns={"modifier"}),
 *      @ORM\Index(name="fk_blog_group_blog_fk", columns={"blog_fk"})
 *  }
 * )
 * @ORM\Entity
 */
class BlogGroupBlog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Blogs", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blog_fk", referencedColumnName="id")
     * })
     * @ORM\Column(name="blog_fk", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $blog;

    /**
     * @ORM\OneToMany(targetEntity="BlogGroups", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blog_fk", referencedColumnName="id")
     * })
     * @ORM\Column(name="group_fk", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $group;

    /**
     * @var int
     *
     * @ORM\Column(name="ordering", type="integer", nullable=false)
     */
    private $ordering;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

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
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @var ?User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modifier", referencedColumnName="id", nullable=true)
     * })
     */
    private $modifier;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBlog(): ?int
    {
        return $this->blog;
    }

    public function setBlog(Blogs $blog): self
    {
        $this->blog = $blog;

        return $this;
    }

    public function getGroup(): ?int
    {
        return $this->group;
    }

    public function setGroup(int $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    public function setOrdering(int $ordering): self
    {
        $this->ordering = $ordering;

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

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): self
    {
        $this->creator = $creator;

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

    public function getModifier(): ?User
    {
        return $this->modifier;
    }

    public function setModifier(?User $modifier): self
    {
        $this->modifier = $modifier;

        return $this;
    }
}
