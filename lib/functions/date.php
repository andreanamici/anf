<?php

/**
 * Ritorna la data formatata per l'utente
 *
 * @access public
 * @param string 	$fmt tipo di formattazione
 * @param integer 	$time Unix timestamp
 * 
 * @return string
 */
if ( ! function_exists('user_date'))
{
	function user_date($fmt = 'DATE_RFC822', $time = '')
	{
		$formats = array(
						'IT_DATETIME_SEC'	=>	'd/m/Y H:i:s',
						'IT_DATETIME'		=>	'd/m/Y H:i',
						'IT_DATE'			=>	'd/m/Y',
						'IT_TIME'			=>	'H:i:s'
						);

		if (!isset($formats[$fmt]))
		{
			return FALSE;
		}
		
		if($time=='')
		{
			return FALSE;
		}
		
		return date($formats[$fmt], strtotime($time));
	}
}



if (!function_exists('date_to_sql'))
{
	/*
	* Converte la data (dd/mm/YYYY) in formato SQL
	* @param string $data data
	* @param char $delimiter separatore elementi di $data
	* @return string
	*/
	function date_to_sql($date=NULL,$delimiter='/')
	{
		if ($date === NULL OR $date === '')
		{
			return FALSE;
		}	
		
		if(preg_match('@^[0-9]{1,2}'.$delimiter.'[0-9]{1,2}'.$delimiter.'[0-9]{4}$@',$date))
		{
			$date=explode($delimiter,$date);
			return $date[2].'-'.$date[1].'-'.$date[0];
		}
                else if(preg_match('@^[0-9]{1,2}'.$delimiter.'[0-9]{1,2}'.$delimiter.'[0-9]{2}$@',$date))
		{
			$date=explode($delimiter,$date);
			return '20'.$date[2].'-'.$date[1].'-'.$date[0];
		}
                else
		{
			return FALSE;
		}			
	}
}


if (!function_exists('sql_to_date'))
{
       /**
	* Converte la data (yyyy-mm-dd) in formato leggibile e tradotto
	* 
	* @param string $data   data
	* @param string $format formato (strftime) della data, caratteri utilizzabili: %a/%A, %d, %b/%B, %y%Y, default:%A %d %b %Y es Venerdì 17 Ottobre 2015
        * 
	* @return string
	*/
	function sql_to_date($date = NULL,$format = '%A %d %B %Y')
	{
		if ($date === NULL OR $date === '')
		{
			return FALSE;
		}

                
                $time = strtotime($date);
		
		if (!$time)
		{
                    return FALSE;
		}	
		
                
                $day_number = date("N",$time);
                $year_low   = date("y",$time);
                $year_upp   = date("Y",$time);
                
                $day        = intval(date("d",$time));
                $month      = intval(date("m",$time));
                
                $hour24  = date("H",$time);
                $hour12  = date("h",$time);
                $minutes = date("i",$time);
                $seconds = date("s",$time);
                
                if(!checkdate($month, $day, $year_upp))
                {
                    return FALSE;
                }
                
                if(strstr($format,'%a') !== FALSE OR strstr($format,'%A') !== FALSE)
                {
                    $short   = strstr($format,'%a') !== FALSE ? TRUE : FALSE;
                    $search  = $short ? '%a'      : '%A';
                    $replace = date_day_name($day_number,$short);                    
                    $format  = str_replace($search,$replace,$format);
                }
                
                if(strstr($format,'%b') !== FALSE OR strstr($format,'%B') !== FALSE)
                {
                    $short   = strstr($format,'%b') !== FALSE ? TRUE : FALSE;
                    $search  = $short ? '%b' : '%B';
                    $replace = date_month_name($month,$short);
                    $format  = str_replace($search,$replace,$format);
                }
                
                $format = str_replace(array('%y','%Y','%d','%m','%H','%h','%i','%s'),array($year_low,$year_upp,$day,$month,$hour24,$hour12,$minutes,$search),$format);
                
                return $format;
	}
}	
	

if(!function_exists("date_now"))
{
    /**
     * Restituisce la data di oggi con l'ora (h:i:s)
     * @return string
     */
    function date_now()
    {
        return date("Y-m-d H:i:s",time());
    }
}


if(!function_exists("date_today"))
{
    /**
     * Restituisce la data di oggi
     * @return string
     */
    function date_today($format = 'Y-m-d')
    {
        return date($format,time());
    }
}


if(!function_exists("date_week_start"))
{
    /**
     * Restituisce la data di inizio della settimana per la data indicata
     * 
     * @return string $date Data, default NULL (odierna)
     * 
     * @return string
     */
    function date_week_start($currdate = NULL)
    {
        $currdate = $currdate ? $currdate : date_today();
        $currtime = strtotime($currdate);
        
        $dayN          = date("N",$currtime);
        $start_date    = date("Y-m-d",strtotime("-".($dayN-1).' days',$currtime));
        
        return $start_date;
    }
}


if(!function_exists('date_diff_days'))
{
    /**
     * Restituisce la differenza tra due date
     * 
     * @param date $date1   data iniziale
     * @param date $date2   data finale
     * 
     * @return int
     */
    function date_diff_days($date1,$date2)
    {
        $today = new DateTime(date($date1));
        $appt  = new DateTime(date($date2));
        return $appt->diff($today)->d;
    }
}

if(!function_exists("date_week_end"))
{
    /**
     * Restituisce la data di inizio della settimana per la data indicata
     * 
     * @return string $date Data, default NULL (odierna)
     * 
     * @return string
     */
    function date_week_end($currdate = NULL)
    {
        $currdate = $currdate ? $currdate : date_today();
        $currtime = strtotime($currdate);
        $dayN         = date("N",$currtime);
        $end_date     = date("Y-m-d",strtotime("+".(7-$dayN).' days',$currtime));
        
        return $end_date;
    }
}	

if(!function_exists("date_is_valid"))
{
    /**
     * Controlla che la data indicata sia una data valida nel formato mysql YYYY-MM-DD
     * 
     * @return string $date Data, default NULL (odierna)
     * 
     * @return string
     */
    function date_is_valid($date)
    {
        if(preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $date))
        {
            return strtotime($date)!== FALSE;
        }
        
        return FALSE;
    }
}	

if(!function_exists("datetime_is_valid"))
{
    /**
     * Controlla che la data e ora indicata sia una data valida nel formato mysql YYYY-MM-DD HH:II
     * 
     * @return string $datetime Data ora, default NULL (odierna)
     * 
     * @return string
     */
    function datetime_is_valid($datetime)
    {
        if(preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{2}\:[0-9]{2}$/', $datetime))
        {
             $datetime.=':00';
        }

        if(preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}$/', $datetime))
        {
            return strtotime($datetime)!== FALSE;
        }
        
        return FALSE;
    }
}

if(!function_exists('date_day_name'))
{
    /**
     * Restituisce il nome del giorno delle settimana dal suo nr, es: 1 => Lunedi / Lun
     * 
     * @param mixed  $date     data / numero del giorno della settimana
     * @param bool   $short    indica se corto
     * 
     * @return string Giorno o FALSE
     */
    function date_day_name($date,$short = FALSE)
    {
        $day_name = FALSE;
        
        if(empty($date))
        {
            return FALSE;
        }
        
        if(is_numeric($date) && $date > 0)
        {
            switch($date)
            {
                case 1: $day_name = $short ? translate('DAY01_SHORT') : translate('DAY01');  break;
                case 2: $day_name = $short ? translate('DAY02_SHORT') : translate('DAY02');  break;
                case 3: $day_name = $short ? translate('DAY03_SHORT') : translate('DAY03');  break;
                case 4: $day_name = $short ? translate('DAY04_SHORT') : translate('DAY04');  break;
                case 5: $day_name = $short ? translate('DAY05_SHORT') : translate('DAY05');  break;
                case 6: $day_name = $short ? translate('DAY06_SHORT') : translate('DAY06');  break;
                case 7: $day_name = $short ? translate('DAY07_SHORT') : translate('DAY07');  break;
            }
        }
        
        if(!$day_name && date_is_valid($date))
        { 
           $day_number = date("N",strtotime($date));
           return date_day_name($day_number,$short);
        }

        return $day_name;
    }
}

if(!function_exists('date_month_name'))
{
    /**
     * Restituisce il nome del mese della data dal suo nr o in base ad una data indicata, es: 1 => Gennaio / Genn 
     * 
     * @param mixed  $date     data / numero del giorno della settimana
     * @param bool   $short    indica se corto
     * 
     * @return string|boolean Mese o FALSE
     */
    function date_month_name($date,$short = FALSE)
    {
        $moth_name = FALSE;
        
        if(empty($date))
        {
            return FALSE;
        }

        if(is_numeric($date) && $date > 0)
        {
            switch($date)
            {
                case 1:   $moth_name = $short ? translate('MONTH01_SHORT') : translate('MONTH01');     break;
                case 2:   $moth_name = $short ? translate('MONTH02_SHORT') : translate('MONTH02');     break;
                case 3:   $moth_name = $short ? translate('MONTH03_SHORT') : translate('MONTH03');     break;
                case 4:   $moth_name = $short ? translate('MONTH04_SHORT') : translate('MONTH04');     break;
                case 5:   $moth_name = $short ? translate('MONTH05_SHORT') : translate('MONTH05');     break;
                case 6:   $moth_name = $short ? translate('MONTH06_SHORT') : translate('MONTH06');     break;
                case 7:   $moth_name = $short ? translate('MONTH07_SHORT') : translate('MONTH07');     break;
                case 8:   $moth_name = $short ? translate('MONTH08_SHORT') : translate('MONTH08');     break;
                case 9:   $moth_name = $short ? translate('MONTH09_SHORT') : translate('MONTH09');     break;
                case 10:  $moth_name = $short ? translate('MONTH10_SHORT') : translate('MONTH10');     break;
                case 11:  $moth_name = $short ? translate('MONTH11_SHORT') : translate('MONTH11');     break;
                case 12:  $moth_name = $short ? translate('MONTH12_SHORT') : translate('MONTH12');     break;
            }
        }

        if(!$moth_name && date_is_valid($date))
        {
            $month = date("m",strotime($date));
            return date_month_name($date,$short);
        }

        return $moth_name;

    }
}


if(!function_exists('date_next'))
{
    /**
     * Restituisce la data successiva del giorno indicato
     * 
     * @param date|datetime $datetime       data / data ora di partenza       
     * @param int           $next_day_n     giorno successivo
     */
    function date_next($datetime, $next_day_n,$format = 'Y-m-d')
    {
        if(!date_is_valid($datetime) && !datetime_is_valid($datetime))
        {
            return FALSE;
        }
        
        $date      = date('Y-m-d',strtotime($datetime));
        $date_next = FALSE;

        while(!$date_next)
        {
            $date_timestamp   = strtotime('+1 day',strtotime($date));
            $date             = date('Y-m-d',$date_timestamp);
            
            $date_n           = date('N',$date_timestamp);
            
            
            if($date_n == $next_day_n)
            {
               $date_next = date($format,$date_timestamp);
            }
        }
        
        return $date_next;
        
    }
}

if(!function_exists('date_prev'))
{
    /**
     * Restituisce la data precedente del giorno indicato
     * 
     * @param date|datetime $datetime       data / data ora di partenza       
     * @param int           $prev_day_n     giorno precedente
     */
    function date_prev($datetime, $prev_day_n,$format = 'Y-m-d')
    {
        if(!date_is_valid($datetime) && !datetime_is_valid($datetime))
        {
            return FALSE;
        }
        
        $date      = date('Y-m-d',strtotime($datetime));
        $date_prev = FALSE;
        
        while(!$date_prev)
        {
            $date_timestamp   = strtotime('-1 day',strtotime($date));
            $date             = date('Y-m-d',$date_timestamp);

            $date_n           = date('N',$date_timestamp);
            
            if($date_n == $prev_day_n)
            {
               $date_prev = date($format,$date_timestamp);
            }
        }
        
        return $date_prev;
    }
}
// End of file ws_date_helper.php
// Location: ./helpers/ws_date_helper.php