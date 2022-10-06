<?php
namespace App\Utils;

use Illuminate\Http\Request;

class Util {

  /**
   * Return success response message
   */
  public static function requestResponse(Request $request,$message,$status = 'success'){
        if($status = 'success'){
           if($request->ajax()){
               return response()->json(array('success_messages'=>array($message)));
           }else{
               return redirect()->back()->with('message',$message);
           }
        }else{
           if($request->ajax()){
               return response()->json(array('error_messages'=>array($message)));
           }else{
               return redirect()->back()->with('error',$message);
           }
        }
   }
   
   /**
    * Check if array contains
	*/
	public static function arrayContains($item, $collection)
	{
		$status = false;
		if(is_array($collection)){
			foreach($collection as $itm){
				if(str_contains($item,$itm)){
					$status = true;
					break;
				}
			}
		}
		return $status;
	}

    /**
     * Array element is contained in search key
     */
    public static function arrayIsContainedInKey($key,$array){
        array_filter($array,function($element) use($key){
           return (str_contains(strtolower($key),strtolower($element)) !== false);
        });
    }

   /**
    * Compute GPA
    */
   public static function computeGPA($total_credits, $results)
   {
       $total_weights = 0;
       foreach($results as $res){
          $total_weights += ($res->point*$res->moduleAssignment->module->credit);
       }
       if($total_credits != 0){
          return bcdiv($total_weights/$total_credits,1,4);
       }else{
          return null;
       }
   }

   /**
    * Compute GPA
    */
   public static function computeGPAPoints($total_credits, $results)
   {
       $total_weights = 0;
       foreach($results as $res){
          $total_weights += ($res->point*$res->moduleAssignment->module->credit);
       }
       return $total_weights;
   }

   /**
    * Get overall remark
    */
   public static function getOverallRemark($remarks, $results = [], $retake = [], $carry = [])
   {
      return $remark = Self::getAnnualRemark($remarks,$results,$retake,$carry);
   }

   /**
    * Get annual remark
    */
   public static function getAnnualRemark($remarks, $results = [], $retake = [], $carry = [])
   {
       $supp_exams = [];
       foreach($results as $result){  
          if($result->final_exam_remark == 'FAIL'){
              $supp_exams[] = $result->moduleAssignment->module->code;
          }
       }

       $remark = 'PASS';
       
       if (count($remarks) == 2) {
        if(count($supp_exams) > (count($results)/2)){
            $remark = 'FAIL&DISCO';
            return $remark;
         }
       }
       
       foreach($remarks as $rem){
          if($rem->remark == 'FAIL&DISCO'){
             $remark = 'FAIL&DISCO';
             break;
          }

          if($rem->remark == 'INCOMPLETE'){
             $remark = 'INCOMPLETE';
             break;
          }

          if($rem->remark == 'POSTPONED'){
             $remark = 'POSTPONED';
             break;
          }

          if($rem->remark == 'CARRY'){
             $remark = 'CARRY';
             break;
          } 

          if($rem->remark == 'RETAKE'){
             $remark = 'RETAKE';
             break;
          }
          
          if($rem->remark == 'SUPP'){
             $remark = 'SUPP';
             break;
          }
       }
       
       return $remark;
   }
    

    /**
     * Truncate number with precision
     */
    public static function truncateNumber($number, $precision = 0) {
   // warning: precision is limited by the size of the int type
       $shift = pow(10, $precision);
       if($number > 0){
            return floor($number * $shift) / $shift; 
        } else {
            return ceil($number * $shift) / $shift; 
        }
    }
   /**
    * Strip spaces upper
    */
   public static function stripSpacesUpper($str){
        return strtoupper(str_replace(' ', '', $str));
   }

   /**
    * Check if collection contains ID
    */
   public static function collectionContains($collection,$target){
      if(is_iterable($collection) && is_object($target)){
         $status = false;
         foreach($collection as $item){
            if($item->id == $target->id){
                $status = true;
                break;
            }
         }
         return $status;
      }else{
         return false;
      }
   }

   /**
    * Check if collection contains ID
    */
   public static function collectionContainsKey($collection,$target){
      if(is_iterable($collection)){
         $status = false;
         foreach($collection as $item){
            if($item->id == $target){
                $status = true;
                break;
            }
         }
         return $status;
      }else{
         return false;
      }
   }
    
    /**
     * Create a standard URL
     */
    public static function standardURL($name = null)
    {
        return strtolower(str_replace(' ','-',preg_replace('/\s+/',' ',$name)));
    }

    /**
     * Create an underscored string
     */
    public static function underscoreString($name = null)
    {
        return strtolower(str_replace(' ','_',preg_replace('/\s+/',' ',$name)));
    }

    /** 
     * Compress image
     */
    public static function compress($source, $destination, $quality) {

        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg') 
            $image = imagecreatefromjpeg($source);

        elseif ($info['mime'] == 'image/gif') 
            $image = imagecreatefromgif($source);

        elseif ($info['mime'] == 'image/png') 
            $image = imagecreatefrompng($source);

        imagejpeg($image, $destination, $quality);

        return $destination;
    }

    /**
     * Sort array collection by order of date (ASC or DEC)
     */
    public static function sortByDateCreated($products, $order = 'DESC')
    {
        for($i = 0; $i < count($products); $i++){
            if($order == 'DESC'){
                if($i < (count($products) - 1)){
                   if(strtotime($products[$i]->created_at) < strtotime($products[$i+1]->created_at)){
                      $products_buffer[] = $products[$i+1];
                      $product_holder = $products[$i];
                      $products[$i] = $products[$i+1];
                      $products[$i+1] = $product_holder;
                   }
                }else{
                    $products_buffer[] = $products[$i];
                }
            }else{
                if($i < (count($products) - 1)){
                   if(strtotime($products[$i]->created_at) > strtotime($products[$i+1]->created_at)){
                      $products_buffer[] = $products[$i+1];
                      $product_holder = $products[$i];
                      $products[$i] = $products[$i+1];
                      $products[$i+1] = $product_holder;
                   }
                }else{
                    $products_buffer[] = $products[$i];
                }
            }
         }
         // Restore the collection keys 
         $sorted_products = [];
         $counter = 0;
         foreach($products_buffer as $prod){
            $sorted_products[$counter] = $prod;
            $counter = $counter + 1;
         }
         return $sorted_products;
    }

    /**
     * Arrange array index by [0-9] digits
     */
    public static function sortArrayIndexes($products_buffer){
         $sorted_products = [];
         $counter = 0;
         foreach($products_buffer as $prod){
            $sorted_products[$counter] = $prod;
            $counter = $counter + 1;
         }
         return $sorted_products;
    }

    /**
     * Strip http or https from URL 
     */
    public static function stripURLProtocal($url = 'https://scholardream.com')
    {   
        $url = str_replace(' ','',$url);
        if(substr($url, 0,8) == 'https://'){
           return substr($url, 8);
        }if(substr($url, 0,7) == 'http://'){
           return substr($url, 7);
        }else{
           return substr($url, 0,7);
        }
    }

    
    /**
     * Refactor phone number
     */
    public static function refactorPhone($number)
    {   
        if(strlen($number) == 10 && substr($number,0,1) == '0'){
          return '+255'.substr($number, 1);
        }elseif(strlen($number) > 10 && substr($number,0,1) == '+'){
          return $number;
        }elseif(strlen($number) > 10 && substr($number,0,1) != '+'){
           return '+'.$number;
        }else{
           return $number;
        }
    }

    /**
     * Get client IP Address
     */
    public static function getClientIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * Get client IP Address
     */
    public static function getReferalURL()
    {
        $referer= '';
        if (isset($_SERVER['HTTP_REFERER'])){
            $referer = $_SERVER['HTTP_REFERER']; 
        }else{
            $referer = 'UNKNOWN';
        }
    }


    /** 
     * Get user agent
     */
    public static function getUserAgent()
    {
       return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Stringify status
     */
    public static function stringStatus($status)
    {
        return ucwords(implode(' ',explode('_',$status)));
    }

     /*
     * Reverse the order of an array
     */
    public static function reverseArray($collection)
    {
        $reversed_collection = [];
        $size = sizeof($collection);

        for($i=$size-1; $i>=0; $i--){
            $reversed_collection[] = $collection[$i];
        }
        return $reversed_collection;
    }

    /**
     * Get phone zip number 
     */
    public static function getZipNumber($number = "")
    {
       if(!empty($number)){
          return substr($number, 1, 3);
       }else{
          return null;
       }
    }

    /**
     * Get phone zip number 
     */
    public static function getLastPhoneNumber($number = "")
    {
       if(!empty($number)){
          return substr($number, 4);
       }else{
          return null;
       }
    }

     /**
     * Strip asterisk (*) from zip code
     */
    public static function stripeZip($number = "+255")
    {
       if(!empty($number)){
          return str_replace('+', '', $number);
       }else{
          return null;
       }
    }

    /**
     * Strip asterisk (*) from zip code
     */
    public static function stripPhonePlus($number = "+255759623399")
    {
       if(!empty($number)){
          return str_replace('+', '', $number);
       }else{
          return null;
       }
    }

    /**
     * Present confirmation alert dialog for delete action
     */
    public static function loadDeleteConfirmation($url = null, $message = 'Are you sure you want to proceed with this action?', $id = 'ss-delete-container')
    {
        $data = [
            'url'=>$url,
            'message'=>$message,
            'id'=>$id
        ];
        return view('templates.confirmation-alert-replacable',$data);
    }
    
    /**
     * Generate a random string of specified length
     */
    public static function randString($length,$uppercase = false){
      $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      if($uppercase == false){
         return substr(str_shuffle($chars),0,$length);
      }else{
         return strtoupper(substr(str_shuffle($chars),0,$length)); 
      }
    }

    /**
     * Trancate HTML tags from a paragraph
     */
    public static function printTruncated($maxLength, $html, $isUtf8=true)
    {
          $printedLength = 0;
          $position = 0;
          $tags = array();

          // For UTF-8, we need to count multibyte sequences as one character.
          $re = $isUtf8
              ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}'
              : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';

          while ($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position))
          {
              list($tag, $tagPosition) = $match[0];

              // Print text leading up to the tag.
              $str = substr($html, $position, $tagPosition - $position);
              if ($printedLength + strlen($str) > $maxLength)
              {
                  print(substr($str, 0, $maxLength - $printedLength));
                  $printedLength = $maxLength;
                  break;
              }

              print($str);
              $printedLength += strlen($str);
              if ($printedLength >= $maxLength) break;

              if ($tag[0] == '&' || ord($tag) >= 0x80)
              {
                  // Pass the entity or UTF-8 multibyte sequence through unchanged.
                  print($tag);
                  $printedLength++;
              }
              else
              {
                  // Handle the tag.
                  $tagName = $match[1][0];
                  if ($tag[1] == '/')
                  {
                      // This is a closing tag.

                      $openingTag = array_pop($tags);
                      assert($openingTag == $tagName); // check that tags are properly nested.

                      print($tag);
                  }
                  else if ($tag[strlen($tag) - 2] == '/')
                  {
                      // Self-closing tag.
                      print($tag);
                  }
                  else
                  {
                      // Opening tag.
                      print($tag);
                      $tags[] = $tagName;
                  }
              }

              // Continue after the tag.
              $position = $tagPosition + strlen($tag);
            }

            // Print any remaining text.
            if ($printedLength < $maxLength && $position < strlen($html))
                print(substr($html, $position, $maxLength - $printedLength));

            // Close any open tags.
            while (!empty($tags))
                printf('</%s>', array_pop($tags));
          }

    }


?>
