<?php

namespace AppBundle\Entity\Beer;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 *
 * @ORM\Table(name="beer_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Beer\TypeRepository")
 */
class Type
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
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Beer\Type\Translation", mappedBy="type")
     */
    private $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add translation
     *
     * @param \AppBundle\Entity\Beer\Type\Translation $translation
     *
     * @return Type
     */
    public function addTranslation(\AppBundle\Entity\Beer\Type\Translation $translation)
    {
        $this->translations[] = $translation;

        return $this;
    }

    /**
     * Remove translation
     *
     * @param \AppBundle\Entity\Beer\Type\Translation $translation
     */
    public function removeTranslation(\AppBundle\Entity\Beer\Type\Translation $translation)
    {
        $this->translations->removeElement($translation);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Clear translations
     */
    public function clearTranslations()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param \AppBundle\Entity\Beer\Type\Translation $translation
     *
     * @return bool
     */
    public function hasTranslation(\AppBundle\Entity\Beer\Type\Translation $translation)
    {
        return $this->translations->contains($translation);
    }
}
