<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Registration\Models\Student;
use App\Models\Role;
use Auth, Validator, Hash;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Application\Models\Applicant;

class SessionController extends Controller
{
	/**
	 * Change password
	 */
	public function changePassword(Request $request)
	{
		$student = User::find(Auth::user()->id)->student()->with('applicant:id')->first();
		$applicant = User::find(Auth::user()->id);
		$ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
		if($student){
			$loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
										 ->where('campus_id',$student->applicant->campus_id)
										 ->count();
		}else{
			$loan_status = LoanAllocation::where(function($query) use($applicant){$query->where('applicant_id',$applicant->id);})
										 ->where('campus_id',$applicant->campus_id)
										 ->count();
		}

		$data = [
           'student'=>$student,
		   'applicant'=> empty($student)? $applicant : $student,
		   'study_academic_year'=>$ac_year,
		   'loan_status'=>$loan_status
		];
		return view('auth.change-password',$data)->withTitle('Change Password');
	}

		/**
	 * Staff Change password
	 */
	public function staffChangePassword(Request $request)
	{
		$staff = User::find(Auth::user()->id);

		$data = [
           'staff'=> User::find(Auth::user()->id),
		   'student' => null,
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
               'password'=>[
							'required',
							'string',
							'min:8',             // must be at least 10 characters in length
							'regex:/[a-z]/',      // must contain at least one lowercase letter
							'regex:/[A-Z]/',      // must contain at least one uppercase letter
							'regex:/[0-9]/',      // must contain at least one digit
							'regex:/[@$!%*#?&]/', // must contain a special character
						],
               'password_confirmation'=>'required|same:password|min:8'
			   //['required', 'min:9','regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/', new Password, 'confirmed'];
            ));

	        if($validation->fails()){
	           if($request->ajax()){
	              return response()->json(array('error_messages'=>$validation->messages()));
	           }else{
	              return redirect()->back()->withInput()->withErrors($validation->messages());
	           }
	        }

			if(Hash::check($request->get('old_password'), Hash::make($request->get('password')))){
				return redirect()->back()->with('error','Your new password should differ the current password!');
			}
			
			$student = Student::where('applicant_id', $request->get('applicant_id'))->first();
			$applicant = Applicant::where('id', $request->get('appl_id'))->first();


			if(!empty($student)){

				$user = User::find(Auth::user()->id);
				$old_password = $user->password;

				if($old_password == Hash::make($request->get('password'))){
					return redirect()->back()->with('error','Your new password should differ the current password!');
				}
				$user->username = $student->registration_number;
				$user->email = $student->email;
				$user->password = Hash::make($request->get('password'));
				$user->must_update_password = 0;

				$role = Role::where('name','student')->first();
        		$user->roles()->sync([$role->id]);

				if ($user->save()) {

					Auth::guard('web')->logout();
					$request->session()->invalidate();
					$request->session()->regenerateToken();
					return redirect()->to('student/login')->with('message', 'Please use your registration number with your new password to login to your student account');

				}

			}elseif(empty($student) && !empty($applicant)){

				$user = User::find(Auth::user()->id);

				$old_password = $user->password;

				if($old_password == Hash::make($request->get('password'))){
					return redirect()->back()->with('error','Your new password should differ the current password!');
				}

				$user->password = Hash::make($request->get('password'));
				$user->must_update_password = 0;
				$user->save();

				$request->session()->forget('password_hash_web');
	
				Auth::guard('web')->login($user);

				return redirect()->to('application/dashboard')->with('message', 'Congratulations, password changed succeefully');

			}else{

				if(Hash::check($request->get('old_password'), Auth::user()->password)){

					$user = User::find(Auth::user()->id);
					$user->password = Hash::make($request->get('password'));
					$user->must_update_password = 0;
					$user->save();
					$request->session()->forget('password_hash_web');
	
				   // login the user back with his new updated credentials
					Auth::guard('web')->login($user);
					if(Auth::user()->hasRole('applicant')){
					   return redirect()->to('application/dashboard')->with('message','Congratulations, password changed succeefully');
					}elseif(Auth::user()->hasRole('student')){
					   return redirect()->to('student/dashboard')->with('message','Congratulations, password changed succeefully');
					}else{
					   return redirect()->to('dashboard')->with('message','Congratulations, password changed succeefully');
					}
				}else{
					  return redirect()->back()->withInput()->with('error','Your old password is not identified, please provide a correct password!');
				}

			}   
       }
}
