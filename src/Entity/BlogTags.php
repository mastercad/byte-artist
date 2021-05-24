<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * BlogTags
 *
 * @ORM\Table(
 *  name="blog_tags",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="un_blog_tag", columns={"blog_fk", "tag_fk"})},
 *  indexes={
 *      @ORM\Index(name="fk_blog_tag_modifier_fk", columns={"modifier"}),
 *      @ORM\Index(name="fk_blog_tag_tag_fk", columns={"tag_fk"}),
 *      @ORM\Index(name="fk_blog_tag_creator_fk", columns={"creator"}),
 *      @ORM\Index(name="IDX_8F6C18B6CDC77FC9", columns={"blog_fk"})
 *  }
 * )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class BlogTags
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
     * @var Tags
     *
     * @ORM\ManyToOne(targetEntity="Tags", cascade={"persist"}, inversedBy="blogTags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_fk", referencedColumnName="id", nullable=false)
     * })
     */
    private $tag;

    /**
     * @var Blogs
     *
     * @ORM\ManyToOne(targetEntity="Blogs", cascade={"persist"}, inversedBy="blogTags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blog_fk", referencedColumnName="id", nullable=false)
     * })
     */
    private $blog;

    private $blogs;

    public function __construct()
    {
        $this->blogs = new ArrayCollection();
    }

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

    public function getTag(): ?Tags
    {
        return $this->tag;
    }

    public function setTag(?Tags $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getBlogs()
    {
        return $this->blogs;
    }

    public function addBlog(Blogs $blog): self
    {
        /*
        if (!$this->blogs->contains($blog)) {
            $this->blogs->add($blog);
            $blog->addBlogTag($this);
        }
        */
        return $this;
    }

    public function removeBlog(Blogs $blog): self
    {
        if ($this->blogs->contains($blog)) {
            $this->blogs->removeElement($blog);
            // set the owning side to null (unless already changed)
            if ($blog->getBlogTags()->contains($this)) {
                $blog->getBlogTags()->removeElement($this);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getTag()->getName();
    }
}
