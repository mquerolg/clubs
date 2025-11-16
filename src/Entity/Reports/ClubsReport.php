<?php

namespace App\Entity\Reports;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClubsReport
 *
 * @ORM\Table(name="CLUBS_REPORT_CLUBS")
 * @ORM\Entity
 */
class ClubsReport
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="CLUBS_REPORT_CLUBS_SEQ", initialValue=1, allocationSize=1)
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="year", type="integer", nullable=false)
     * @ORM\OrderBy({"year" = "DESC"})
     */
    private $year;

    /**
     * @var int
     *
     * @ORM\Column(name="created", type="integer", nullable=false)
     */
    private $created = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="discharged", type="integer", nullable=false)
     */
    private $discharged = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="total", type="integer", nullable=false)
     */
    private $total = 0;

    /*
    |--------------------------------------------------------------------------
    | GETTER'S & SETTER'S
    |--------------------------------------------------------------------------
    */

    /**
     * Get the value of id
     *
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of year
     *
     * @return  int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set the value of year
     *
     * @param  int  $year
     *
     * @return  self
     */
    public function setYear(int $year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the value of created
     *
     * @return  int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the value of created
     *
     * @param  int  $created
     *
     * @return  self
     */
    public function setCreated(int $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of discharged
     *
     * @return  int
     */
    public function getDischarged()
    {
        return $this->discharged;
    }

    /**
     * Set the value of discharged
     *
     * @param  int  $discharged
     *
     * @return  self
     */
    public function setDischarged(int $discharged)
    {
        $this->discharged = $discharged;

        return $this;
    }

    /**
     * Get the value of total
     *
     * @return  int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set the value of total
     *
     * @param  int  $total
     *
     * @return  self
     */
    public function setTotal(int $total)
    {
        $this->total = $total;

        return $this;
    }
}
