<?php

namespace AppBundle\Entity;

use AppBundle\Util\EntityUtil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Brewery
 *
 * @ORM\Table(name="brewery", indexes={@ORM\Index(name="country_id", columns={"country_id"})}, uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BreweryRepository")
 * @UniqueEntity(
 *  fields={"name"},
 *  message="This brewery already exists."
 * )
 */
class Brewery
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", length=10, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     */
    private $name;

    /**
     * @var \AppBundle\Entity\Country
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $country;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Beer", mappedBy="brewery")
     */
    private $beers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdDate = new \DateTime('now', new \DateTimeZone(EntityUtil::DEFAULT_TIMEZONE));
        $this->beers       = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Brewery
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set country
     *
     * @param \AppBundle\Entity\Country $country
     *
     * @return Brewery
     */
    public function setCountry(\AppBundle\Entity\Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \AppBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Add beer
     *
     * @param \AppBundle\Entity\Beer $beer
     *
     * @return Brewery
     */
    public function addBeer(\AppBundle\Entity\Beer $beer)
    {
        $this->beers[] = $beer;

        return $this;
    }

    /**
     * Remove beer
     *
     * @param \AppBundle\Entity\Beer $beer
     */
    public function removeBeer(\AppBundle\Entity\Beer $beer)
    {
        $this->beers->removeElement($beer);
    }

    /**
     * Get beers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBeers()
    {
        return $this->beers;
    }
}
