<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\CampusDepartment;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Academic\Models\Department;
use App\Models\User;
use Auth;

class HomeController extends Controller
{
    /**
     * Return a list of regions given country id
     */
    public function getRegions(Request $request)
    {
    	$regions = Region::where('country_id',$request->get('country_id'))->get();
    	if(count($regions) != 0){
            return response()->json(['status'=>'success','regions'=>$regions]);
    	}else{
    		return response()->json(['status'=>'failed','regions'=>$regions]);
    	}
    }

    public function getParents(Request $request)
    {
        $faculties = Faculty::where('campus_id', $request->get('campus_id'))->get();

        if ($request->get('unit_category_id') == 1 || $request->get('unit_category_id') == 2) {

            if(count($faculties) > 0){
                return response()->json(['status'=>'success','faculties'=>$faculties]);
            } else if (count($faculties) == 0) {
                $campus = Campus::find($request->get('campus_id'));
                return response()->json(['status'=>'success','campus'=>$campus]);
            }else{
                return response()->json(['status'=>'failed','faculties'=>$faculties]);
            }

        } else if ($request->get('unit_category_id') == 4) {

            $departments = Department::whereHas('campuses',function($query) use($request){
                $query->where('campuses.id', $request->get('campus_id'))->where('unit_category_id', 2);
            })->with('campuses')->get();
            
            if (count($departments) > 0) {
                return response()->json(['status'=>'success','departments'=>$departments]);
            } else {
                return response()->json(['status'=>'success','departments'=>$departments]);
            }

        }

        
    }

    public function getFacultyParents(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;

        // $faculty = Faculty::where('campus_id', $staff->campus_id)->get();
        $faculties = Faculty::all();

        if(count($faculties) != 0){
            return response()->json(['status'=>'success','faculties'=>$faculties]);
    	}else{
    		return response()->json(['status'=>'failed','faculties'=>$faculties]);
    	}
    }

    /**
     * Return a list of regions given country id
     */
    public function getDistricts(Request $request)
    {
    	$districts = District::where('region_id',$request->get('region_id'))->get();
    	if(count($districts) != 0){
            return response()->json(['status'=>'success','districts'=>$districts]);
    	}else{
    		return response()->json(['status'=>'failed','districts'=>$districts],404);
    	}
    }

    /**
     * Return a list of wards given district id
     */
    public function getWards(Request $request)
    {
    	$wards = Ward::where('district_id',$request->get('district_id'))->get();
    	if(count($wards) != 0){
            return response()->json(['status'=>'success','wards'=>$wards]);
    	}else{
    		return response()->json(['status'=>'failed','wards'=>$wards]);
    	}
    }

    /**
     * Return a list of modules give nta level id
     */
    public function getNTALevelModules(Request $request)
    {
    	$modules = Module::where('nta_level_id',$request->get('nta_level_id'))->get();
    	if(count($modules) != 0){
            return response()->json(['status'=>'success','modules'=>$modules]);
    	}else{
    		return response()->json(['status'=>'failed','wards'=>$modules]);
    	}
    }

    /**
     * Return fee type from id
     */
    public function getFeeType(Request $request)
    {
        $type = FeeType::with(['feeItems.feeAmounts'])->find($request->get('fee_type_id'));
        if($type){
            return response()->json(['status'=>'success','type'=>$type]);
        }else{
            return response()->json(['status'=>'failed','type'=>$type]);
        }
    }

    /**
     * Return a list of modules give nta level id
     */
    public function getProgramModules(Request $request)
    {
        $modules =  Module::whereHas('programModuleAssignments',function($query) use($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
        })->get();
        if(count($modules) != 0){
            return response()->json(['status'=>'success','modules'=>$modules]);
        }else{
            return response()->json(['status'=>'failed','modules'=>$modules]);
        }
    }

    /**
     * Return a list of modules give nta level id
     */
    public function getProgramModuleAssignments(Request $request)
    {
        $modules =  ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
        })->with('module')->get();
        if(count($modules) != 0){
            return response()->json(['status'=>'success','modules'=>$modules]);
        }else{
            return response()->json(['status'=>'failed','modules'=>$modules]);
        }
    }

    /**
     * Return NTA level
     */
    public function getNTALevel(Request $request)
    {
        $nta_level = NTALevel::find($request->get('nta_level_id'));
        if($nta_level){
            return response()->json(['status'=>'success','nta_level'=>$nta_level]);
        }else{
            return response()->json(['status'=>'failed','nta_level'=>null]);
        }
    }

    /**
     * Return NTA level by code
     */
    public function getNTALevelByCode(Request $request)
    {
        $nta_level = NTALevel::where('name','LIKE','%'.substr(str_replace(' ', '',$request->get('code')),4,1).'%')->first();
        if($nta_level){
            return response()->json(['status'=>'success','nta_level'=>$nta_level]);
        }else{
            return response()->json(['status'=>'failed','nta_level'=>null]);
        }
    }

    /**
     * Return NTA level by code
     */
    public function getModuleById(Request $request)
    {
        $module = Module::find($request->get('module_id'));
        if($module){
            return response()->json(['status'=>'success','module'=>$module]);
        }else{
            return response()->json(['status'=>'failed','module'=>null]);
        }
    }
}
