<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth, Validator, Hash;

class SessionController extends Controller
{
	/**
	 * Change password
	 */
	public function changePassword(Request $request)
	{
		return view('auth.change-password')->withTitle('Change Password');
	}
	/**
	 * Update password 
	 */
    public function update(Request $request)
    {
         $validation = Validator::make($request->all(), array(
               'old_password'=>'required',
               'password'=>'required|min:8',
               'password_confirmation'=>'required|same:password|min:8'
            ));

	        if($validation->fails()){
	           if($request->ajax()){
	              return response()->json(array('error_messages'=>$validation->messages()));
	           }else{
	              return redirect()->back()->withInput()->withErrors($validation->messages());
	           }
	        }

	        if(Hash::check($request->get('old_password'), Auth::user()->password)){
	            $user = User::find(Auth::user()->id);
	            $user->password = Hash::make($request->get('password'));
	            $user->must_update_password = 0;
	            $user->save();
	            return redirect()->back()->with('message','Congratulations, new password saved succeefully');
	        }else{
	              return redirect()->back()->withInput()->with('error','Your old password is not identified, please provide a correct password!');
	        }
       }
}
