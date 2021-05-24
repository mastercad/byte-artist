<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Blogs.
 *
 * @ORM\Table(
 *  name="blog",
 *  indexes={
 *      @ORM\Index(name="fk_blog_group_fk", columns={"group_fk"}),
 *      @ORM\Index(name="fk_blog_modifier", columns={"modifier"}),
 *      @ORM\Index(name="fk_blog_creator", columns={"creator"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BlogRepository")
 */
class Blogs
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=250, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=0, nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_link", type="string", length=250, nullable=false)
     */
    private $seoLink;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text", length=65535, nullable=false)
     */
    private $shortDescription;

    /**
     * @var ?string
     *
     * @ORM\Column(name="preview_picture", type="string", length=250, nullable=true)
     */
    private $previewPicture;

    /**
     * @var ?int
     *
     * @ORM\Column(name="group_order", type="integer", nullable=true)
     */
    private $groupOrder;

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
     * @var ?BlogGroups
     *
     * @ORM\ManyToOne(targetEntity="BlogGroups")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="group_fk", referencedColumnName="id", nullable=true)
     * })
     */
    private $group;

    /**
     * @var ?User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modifier", referencedColumnName="id", nullable=true)
     * })
     */
    private $modifier;

    /**
     * @ORM\OneToMany(targetEntity="BlogTags", mappedBy="blog", cascade={"persist"})
     */
    private $blogTags;

    public function __construct()
    {
        $this->blogTags = new ArrayCollection();
    }

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getPreviewPicture(): ?string
    {
        return $this->previewPicture;
    }

    public function setPreviewPicture(string $previewPicture): self
    {
        $this->previewPicture = $previewPicture;

        return $this;
    }

    public function getGroupOrder(): ?int
    {
        return $this->groupOrder;
    }

    public function setGroupOrder(int $groupOrder): self
    {
        $this->groupOrder = $groupOrder;

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

    public function getGroup(): ?BlogGroups
    {
        return $this->group;
    }

    public function setGroup(?BlogGroups $group): self
    {
        $this->group = $group;

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

    /**
     * @return Collection|BlogTags[]
     */
    public function getBlogTags(): Collection
    {
        return $this->blogTags;
    }

    public function addBlogTag(BlogTags $blogTag): self
    {
        if (!$this->blogTags->contains($blogTag)) {
            $this->blogTags[] = $blogTag;
            $blogTag->addBlog($this);
        }

        return $this;
    }

    public function removeBlogTag(BlogTags $blogTag): self
    {
        if ($this->blogTags->contains($blogTag)) {
            $this->blogTags->removeElement($blogTag);
            // set the owning side to null (unless already changed)
            if ($blogTag->getBlogs()->contains($this)) {
                $blogTag->removeBlog($this);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
