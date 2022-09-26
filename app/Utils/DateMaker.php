<?php
namespace App\Utils;

use Carbon;
use DateTime;

class DateMaker{

	// Create date from yyyy-mm-dd to Day Month Year string
	public static function toStringDate($date){
       if(!is_null($date)){
       	  $date = date('jS F Y',strtotime($date));
          return $date;
       }
	}

  // Create date from yyyy-mm-dd to Day Month Year string
  public static function toStringDayDate($date){
       if(!is_null($date)){
          $date = date('l jS F Y',strtotime($date));
          return $date;
       }
  }

  // Extract time from string date
  public static function toDateTime($date){
        if(!is_null($date)){
          $date = date('jS F Y h:i A',strtotime($date));
          return $date;
       }
  }

  // Extract time from string date
  public static function toTime($date){
        if(!is_null($date)){
          $date = date('h:i A',strtotime($date));
          return $date;
       }
  }

  // Extract time from string date
  public static function toFullTime($date){
        if(!is_null($date)){
          $date = date('l jS \of F Y h:i A',strtotime($date));
          return $date;
       }
  }
    
  // Create date from mm/dd/yyyy to yyyy-mm-dd format
	public static function toDashedDate($date){
       if(!is_null($date)){
          $date = str_replace('/', '-', $date);
          $date = date('Y-m-d',strtotime($date));
          return $date;
       }
	}

  // Create date from yyyy-mm-dd to dd-mm-yyyy format
  public static function toStandardDate($date){
       if(!is_null($date)){
          $date = date('d-m-Y',strtotime($date));
          return $date;
       }
  }

  // Create date from dd-mm-yyyy to yyyy-mm-dd format
  public static function toDBDate($date){
       if(!is_null($date)){
          $date = date('Y-m-d',strtotime($date));
          return $date;
       }
  }

  // Create date from yyyy-mm-dd to dd-mm-yyyy format
  public static function toProfessionalDate($date){
       if(!is_null($date)){
          $date = date('M d Y h:i A',strtotime($date));
          return $date;
       }
  }

  // Get ellapsed human time
  public static function timePassed($timestamp)
  {  
      $time = strtotime($timestamp);

      $time = time() - $time; // to get the time since that moment
      $time = ($time<1)? 1 : $time;
      $tokens = array (
          31536000 => 'year',
          2592000 => 'month',
          604800 => 'week',
          86400 => 'day',
          3600 => 'hour',
          60 => 'minute',
          1 => 'second'
      );

      foreach ($tokens as $unit => $text) {
          if ($time < $unit) continue;
          $numberOfUnits = floor($time / $unit);
          return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
      }
  }

  // Get human time to be ellapsed
  public static function timeRemaining($timestamp)
  {  
      $time = strtotime($timestamp);

      $time = $time - time(); // to get the time since that moment
      $time = ($time<1)? 1 : $time;
      $tokens = array (
          31536000 => 'year',
          2592000 => 'month',
          604800 => 'week',
          86400 => 'day',
          3600 => 'hour',
          60 => 'minute',
          1 => 'second'
      );

      foreach ($tokens as $unit => $text) {
          if ($time < $unit) continue;
          $numberOfUnits = floor($time / $unit);
          return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
      }
  }

	// Create date from yyyy-mm-dd to dd/mm/yyyy format
	public static function toSlashedDate($date){
       if(!is_null($date)){
          $date = str_replace('-', '/', $date);
          $date = date('d/m/Y',strtotime($date));
          return $date;
       }
	}

	// Find the time interval between current date and given future date 
	// Accept date format yyyy-mm-dd
	public static function timeBeforeDate($date){
        if(!is_null($date)){
        	$current_date = new DateTime(date('Y-m-d'));
            $future_date = new DateTime($date);
            $difference = $current_date->diff($future_date);
            return Self::stringTimeInterval($difference);   
        }
	}

	// Find the time interval between current date and given past date 
	// Accept date format yyyy-mm-dd
	public static function timeAfterDate($date){
        if(!is_null($date)){
        	$current_date = new DateTime(date('Y-m-d'));
            $future_date = new DateTime($date);
            $difference = $future_date->diff($current_date);
            return Self::stringTimeInterval($difference);   
        }
	}

	// Determine if date has passed from the format yyyy-mm-dd
	public static function isDatePassed($date){
        if(!is_null($date)){
            $current_date = new DateTime(date('Y-m-d'));
            $future_date = new DateTime($date);
           if($current_date > $future_date){
              return true;
           }else{
           	  return false;
           }
        }
	}

  // Determine if time has passed from the format yyyy-mm-dd
  public static function isTimePassed($date){
        if(!is_null($date)){
            $current_date = new DateTime(date('Y-m-d H:m:s'));
            $future_date = new DateTime($date);
           if($current_date > $future_date){
              return true;
           }else{
              return false;
           }
        }
  }

  // Determine if today is deadline in the format yyyy-mm-dd
  public static function todayDate($date){
        if(!is_null($date)){
            $current_date = new DateTime(date('Y-m-d'));
            $future_date = new DateTime($date);
           if($current_date == $future_date){
              return true;
           }else{
              return false;
           }
        }
  }

	// Time difference in human readable string
	private static function stringTimeInterval(DateInterval $difference){
        if($difference->y == 0){
               $interval = '';
            }else if($difference->y == 1){
                $interval = $difference->y.' year ';
            }else{
                $interval = $difference->y.' years ';
            }

            if($difference->m == 0){
               $interval .= '';
            }else if($difference->m == 1){
               if($difference->d == 0){
               	  $interval .= $difference->m.' month ';
               }else{
               	  $interval .= $difference->m.' month and ';
               }
            }else{
               if($difference->d == 0){
               	  $interval .= $difference->m.' months ';
               }else{
               	  $interval .= $difference->m.' months and ';
               }
            }

            if($difference->d == 0){
               $interval .= '';
            }else if($difference->d == 1){
               $interval .= $difference->d.' day ';
            }else{
               $interval .= $difference->d.' days ';
            }

            return $interval;
	}

  /**
   * Determine if specific months have passed
   */
  public static function monthsPassed($date, $months = 3){
     if(!is_null($date)){
            $current_date = new DateTime(date('Y-m-d'));
            $past_date = new DateTime($date);
            $difference = $past_date->diff($current_date);  
            if($difference->m <= $months){
               return true;
            }else{
              return false;
            }
     }
  }

  // Get date suffix
  public static function getSuffix($day){
     if($day == 1 || $day == 21 || $day == 31){
        return 'st';
     }elseif($day == 2 || $day == 22){
        return 'nd';
     }elseif($day == 3 || $day == 23){
        return 'rd';
     }else{
        return 'th';
     }
  }

  // Get number of days in a given month and year
  public static function daysInMonth($month, $year){
      return cal_days_in_month(CAL_GREGORIAN,$month,$year);
  }

  // Get day suffix from date
  public static function getDaySuffix($year, $month, $day){
      return substr(Carbon::now()->year($year)->month($month)->day($day)->format('l'),0,1);
  }
}