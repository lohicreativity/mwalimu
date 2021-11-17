<?php

namespace App\Utils;

use App\User;
use File;

class SystemLocation {

	// Path to user directory
	public static function userDirectory(User $user){
		$path = public_path().'/users/'.sha1(date('jS F Y',strtotime($user->created_at)).'_'.$user->id);
		$path .= '/';
		if(!File::isDirectory($path)){
			File::makeDirectory($path);
			return $path;
		}else{
			return $path; 
		}
	}

	// Path to avatars folder
	public static function avatarsDirectory()
	{
		return public_path().'/avatars/';
	}
	// Path to uploads folder
	public static function uploadsDirectory()
	{
		return public_path().'/uploads/';
	}
	// Path to uploads folder
	public static function documentsDirectory()
	{
		return public_path().'/documents/';
	}

	// Path to renamed image
	public static function removeImage($file_path, $name, $ext = null){
		$path = $file_path.$name;
		if(File::exists($path)){
           unlink($path);
           return;
		}
	}

	// Hashed user directory
	public static function userHashedDirectory(User $user){
		return sha1(date('jS F Y',strtotime($user->created_at)).'_'.$user->id).'/';
	}

	
	// Path to renamed file
	public static function renameFile($old_path, $name, $ext = null,$new_name = null){
		$path = $old_path.$name;
		if($new_name){
            $new_path = $old_path.str_replace('/', '',$new_name).'.'.$ext;
		}else{
			$new_path = $old_path.sha1(date('Y_m_d',strtotime(now())).$name);
		}
		if(File::exists($path)){
           rename($path,$new_path);
           if($new_name){
              return str_replace('/', '',$new_name).'.'.$ext;
           }else{
           	  return sha1(date('Y_m_d',strtotime(now())).$name);
           }
		}else{
		   return $name;
		}
	}

	// Check if user file exists
	public static function userImageExists(User $user)
	{   if(!empty($user->image)){
			$file_path = public_path().'/users/'.Self::userHashedDirectory($user).$user->image;
	        if(File::exists($file_path)){
	        	return $file_path;
	        }else{
	        	return $file_path;
	        }
        }else{
        	return null;
        }
	}

	// Check if upload file exists
	public static function uploadFileExists($file_name = null)
	{
		$file_path = public_path().'/assets/uploads/'.$file_name;
        if(File::exists($file_path)){
          return true;
        }else{
       	  return false;
        }
	}
}