<?php


namespace App\Entity;


use Cassandra\Date;
use Knp\Bundle\TimeBundle\DateTimeFormatter;

class DateAgo
{
    public function agoDate(DateTimeFormatter $dateTimeFormatter, $date){
    $someDate = new \DateTime($date);

    $now = new \DateTime();

        return $dateTimeFormatter->formatDiff($someDate, $now);
    }
}