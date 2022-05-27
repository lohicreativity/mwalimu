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
		$student = User::find(Auth::user()->id)->student()->with('applicant')->first();
		$data = [
           'student'=>$student
		];
		return view('auth.change-password',$data)->withTitle('Change Password');
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
	            $request->session()->forget('password_hash_web');

		       // login the user back with his new updated credentials
		        Auth::guard('web')->login($user);
		        if(Auth::user()->hasRole('applicant')){
                   return redirect()->to('application/dashboard')->with('message','Congratulations, new password saved succeefully');
		        }elseif(Auth::user()->hasRole('student')){
                   return redirect()->to('student/dashboard')->with('message','Congratulations, new password saved succeefully');
		        }else{
	               return redirect()->to('dashboard')->with('message','Congratulations, new password saved succeefully');
	            }
	        }else{
	              return redirect()->back()->withInput()->with('error','Your old password is not identified, please provide a correct password!');
	        }
       }
}
