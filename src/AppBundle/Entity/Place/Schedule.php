<?php

namespace AppBundle\Entity\Place;

use AppBundle\Util\EntityUtil;
use Doctrine\ORM\Mapping as ORM;

/**
 * Schedule
 *
 * @ORM\Table(name="place_schedule", indexes={@ORM\Index(name="place_id", columns={"place_id"})})
 * @ORM\Entity
 */
class Schedule
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
     * @var boolean
     *
     * @ORM\Column(name="day", type="boolean", nullable=false)
     */
    private $day;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="opening_time", type="time", nullable=false)
     */
    private $openingTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closure_time", type="time", nullable=false)
     */
    private $closureTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false)
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_date", type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var \AppBundle\Entity\Place
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Place", inversedBy="schedules")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="place_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $place;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdDate = new \DateTime('now', new \DateTimeZone(EntityUtil::DEFAULT_TIMEZONE));
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
     * Set day
     *
     * @param boolean $day
     *
     * @return Schedule
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return boolean
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set openingTime
     *
     * @param \DateTime $openingTime
     *
     * @return Schedule
     */
    public function setOpeningTime($openingTime)
    {
        $this->openingTime = $openingTime;

        return $this;
    }

    /**
     * Get openingTime
     *
     * @return \DateTime
     */
    public function getOpeningTime()
    {
        return $this->openingTime;
    }

    /**
     * Set closureTime
     *
     * @param \DateTime $closureTime
     *
     * @return Schedule
     */
    public function setClosureTime($closureTime)
    {
        $this->closureTime = $closureTime;

        return $this;
    }

    /**
     * Get closureTime
     *
     * @return \DateTime
     */
    public function getClosureTime()
    {
        return $this->closureTime;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Schedule
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set updatedDate
     *
     * @param \DateTime $updatedDate
     *
     * @return Schedule
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * Get updatedDate
     *
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Set place
     *
     * @param \AppBundle\Entity\Place $place
     *
     * @return Schedule
     */
    public function setPlace(\AppBundle\Entity\Place $place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \AppBundle\Entity\Place
     */
    public function getPlace()
    {
        return $this->place;
    }
}
