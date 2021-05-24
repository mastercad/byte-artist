<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogSubscribers
 *
 * @ORM\Table(
 *  name="blog_subscribers",
 *  indexes={
 *      @ORM\Index(name="fk_blog_subscriber_creator_fk", columns={"creator"}),
 *      @ORM\Index(name="fk_blog_subscriber_modifier_fk", columns={"modifier"}),
 *      @ORM\Index(name="fk_blog_subscriber_user_fk", columns={"user_fk"}),
 *      @ORM\Index(name="fk_blog_subscriber_blog_fk", columns={"blog_fk"})
 *  }
 * )
 * @ORM\Entity
 */
class BlogSubscribers
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
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @var Blogs
     *
     * @ORM\ManyToOne(targetEntity="Blogs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blog_fk", referencedColumnName="id", nullable=false)
     * })
     */
    private $blog;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_fk", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

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

    public function getBlog(): ?Blogs
    {
        return $this->blog;
    }

    public function setBlog(?Blogs $blog): self
    {
        $this->blog = $blog;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
