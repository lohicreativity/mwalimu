<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Registration\Models\Student;
use App\Models\Role;
use Auth, Validator, Hash;

class SessionController extends Controller
{
	/**
	 * Change password
	 */
	public function changePassword(Request $request)
	{
		$student = User::find(Auth::user()->id)->student()->with('applicant')->first();
		$ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
		$data = [
           'student'=>$student,
		   'study_academic_year'=>$ac_year
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

			$student = Student::where('applicant_id', $request->get('applicant_id'))->first();

			if ($student) {

				$user = User::find(Auth::user()->id);
				$user->username = $student->registration_number;
				$user->password = Hash::make($student->birth_date);

				$role = Role::where('name','student')->first();
        		$user->roles()->sync([$role->id]);

				if ($user->save()) {

					Auth::logout();
					$request->session()->invalidate();
					$request->session()->regenerateToken();
					return redirect()->to('student/login');

				}

			} else {

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
}
