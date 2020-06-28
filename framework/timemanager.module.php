<?php

class TimeManager {

    public function __construct() {
        //empty one
    }

    static public function freeze($time = null) {
        static $frozen = null;

        if($time) {
            $frozen = $time;
        }

        return $frozen;
    }

    static public function time($shift = 0, $utc = '+0') {
        if( TimeManager::freeze() != null ) {
            return TimeManager::freeze();
        }

        return (time() + $shift);
    }

    static public function isLeapYear($year=false) {
        if (!$year) {
            $year = date('Y');
        }
        return ((bool) ( cal_days_in_month(CAL_GREGORIAN, 2, $year) - 28 ));
    }

    static public function yearDays($year = false) {
        if (!$year) {
            $year = date('Y');
        }
        if (TimeManager::isLeapYear($year)) {
            return 366;
        } else {
            return 365;
        }
    }

    static public function humanizeTime($scs){

            $seconds = $scs;
            $result = $seconds.lng::read('seconds');
            if ($seconds > 60) {
                $minutes = floor( $seconds / 60 );
                $seconds = (int)$seconds % 60;
                $result = $minutes.lng::read('minutes').' '.$seconds.lng::read('seconds');
                if ($minutes > 60) {
                    $hours = floor( $minutes / 60 );
                    $minutes = $minutes % 60;
                    $result = $hours.lng::read('hours').' '.$minutes.lng::read('minutes').' '.$seconds.lng::read('seconds');
                    if ($hours > 24) {
                        $days = floor( $hours / 24 );
                        $hours = $hours % 24;
                        $result = $days.lng::read('days').' '.$hours.lng::read('hours').' '.$minutes.lng::read('minutes').' '.$seconds.lng::read('seconds');
                        if ($days > TimeManager::yearDays()) {
                            $years = floor( $days / TimeManager::yearDays() );
                            $days = $days % TimeManager::yearDays();
                            $result = $years.lng::read('years').' '.$days.lng::read('days').' '.$hours.lng::read('hours').' '.$minutes.lng::read('minutes').' '.$seconds.lng::read('seconds');
                            if ($years > 100) {
                                $years = floor( $years / 100 );
                                $centuries = $years % 100;
                                $result = $centuries.lng::read('centuries').' '.$years.lng::read('years').' '.$days.lng::read('days').' '.$hours.lng::read('hours').' '.$minutes.lng::read('minutes').' '.$seconds.lng::read('seconds');
                            }
                        }
                    }
                }
            }
            return $result;
    }

    static public function timePlus($format) {
        if ($format == 'now') {
            return TimeManager::time();
        }
        preg_match('#^([0-9]+)#i',$format,$fmatches);
        preg_match('#([A-z]{3,})$#i',$format,$tmatches);
        $count = $fmatches['1'];
        $type = substr($tmatches['1'],0,3);
        if (empty($count) || empty($type)) {
            ExceptionManager::ThrowError(__CLASS__.':'.__FUNCTION__.' - Invalid params.');
        }
        $result = 0;
        switch($type) {
            case'sec';
                $result = TimeManager::time() + $count;
                break;
            case'min';
                $result = TimeManager::time() + ($count * 60);
                break;
            case'hou';
                $result = TimeManager::time() + ($count * 3600);
                break;
            case'day';
                $result = TimeManager::time() + ($count * 86400);
                break;
            case'mon';
                $result = TimeManager::time() + ($count * (86400 * 30));
                break;
            case'yea';
                $result = TimeManager::time() + ($count * (2592000 * 365));
                break;
            case'cen';
                $result = TimeManager::time() + ($count * (946080000 * 100));
                break;
        }
        return $result;
    }

    static public function timeMinus($format) {
        if ($format == 'now') {
            return TimeManager::time();
        }
        preg_match('#^([0-9]+)#i',$format,$fmatches);
        preg_match('#([A-z]{3,})$#i',$format,$tmatches);
        $count = $fmatches['1'];
        $type = substr($tmatches['1'],0,3);
        if (empty($count) || empty($type)) {
            ExceptionManager::ThrowError(__CLASS__.':'.__FUNCTION__.' - Invalid params.');
        }
        $result = 0;
        switch($type) {
            case'sec';
                $result = TimeManager::time() - $count;
                break;
            case'min';
                $result = TimeManager::time() - ($count * 60);
                break;
            case'hou';
                $result = TimeManager::time() - ($count * 3600);
                break;
            case'day';
                $result = TimeManager::time() - ($count * 86400);
                break;
            case'mon';
                $result = TimeManager::time() - ($count * (86400 * 30));
                break;
            case'yea';
                $result = TimeManager::time() - ($count * (2592000 * 365));
                break;
            case'cen';
                $result = TimeManager::time() - ($count * (946080000 * 100));
                break;
        }
        return $result;
    }

    static public function drop($timestamp = null)
    {
        if( $timestamp === null )
        {
            $timestamp = TimeManager::time();
        }

        return strtotime(date('d.m.Y', $timestamp));
    }

}

class lng {
    static public function read($key)
    {
        $language = array(
            'seconds' => 'сек.',
            'minutes' => 'мин.',
            'hours' => 'ч.',
            'days' => 'дн.',
            'years' => 'г.',
            'centuries' => 'век(-ов)'
        );

        return $language[$key];
    }
}