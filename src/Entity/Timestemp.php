<?php


namespace App\Entity;



use Doctrine\ORM\Mapping as ORM;
use Knp\Bundle\TimeBundle\DateTimeFormatter;

trait Timestemp
{
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @param DateTimeFormatter $dateTimeFormatter
     * @return mixed
     */
    public function getCreatedAt(DateTimeFormatter $dateTimeFormatter){
        $someDate = new \DateTime($this->createdAt);

        $now = new \DateTime();


        return $dateTimeFormatter->formatDiff($someDate, $now);
    }

    /**
     *@ORM\PrePersist()
     */
    public function PrePersite(){
        $this->createdAt = new \DateTime();
    }

}