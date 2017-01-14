<?php

namespace MediaLibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ImageField
 *
 * @ORM\Table(name="image_field")
 * @ORM\Entity(repositoryClass="MediaLibraryBundle\Repository\ImageFieldRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ImageField
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="MediaLibraryBundle\Entity\Image")
     */
    private $image;

    /**
     * @var int
     *
     * @ORM\Column(name="weight", type="integer")
     */
    private $weight = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        $date = new \DateTime();
        $this->setCreated($date);
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage(Image $image = null)
    {
        $this->image = $image;
    }
}
