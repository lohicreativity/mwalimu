@extends('layouts.app')

@section('content')

<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('dist/img/logo.png') }}" alt="{{ Config::get('constants.SITE_NAME') }}" height="60" width="60">
  </div>

  @include('layouts.auth-header')

  @include('layouts.sidebar')

  <!-- Content Wrapper. Contains page content -->
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{ __('Registration Deadline') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Registration Deadline') }}</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <div class="card">
              <div class="card-header">
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'registration/registration-deadline','class'=>'ss-form-processing','method'=>'GET']) !!}
                  @php                
                   $campus_id = [
                      'class'=>'form-control',
                      'placeholder'=>'Campus name',
                      'readonly'=>true
                   ];
                  @endphp                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>
                          {{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))                  
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  @else
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @else disabled='disabled' @endif>
                       {{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  @endif
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
             
            @if($study_academic_year && $campus)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Registration deadline for {{ $campus->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              @if(count($registration_dates) == 0)
                  {!! Form::open(['url'=>'registration/store-registration-deadline','class'=>'ss-form-processing']) !!}
                      <div class="card-body">
                          @php
                            if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator')){                 
                              $date = [
                                  'placeholder'=>'Registration deadline',
                                  'class'=>'form-control ss-datepicker',
                                  'required'=>true
                              ];
                            }else{
                              $date = [
                                  'placeholder'=>'Registration deadline',
                                  'class'=>'form-control',
                                  'readonly'=>true,
                                  'required'=>true
                              ];
                            }
                          @endphp

                          <div class="row">
                            <div class="form-group col-3">
                              {!! Form::label('','New registration deadline') !!}
                              {!! Form::text('registration_date',null,$date) !!}

                              {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                              {!! Form::input('hidden','campus_id',$campus->id) !!}
                              {!! Form::input('hidden','name','New Registration Period') !!}
                            </div>
                          </div>
                      </div>
                      <div class="card-footer">
                          <button type="submit" class="btn btn-primary">{{ __('Create Registration Deadline') }}</button>
                      </div>
                  {!! Form::close() !!}

            @else
                <!-- /.card-header -->
                <ul class="nav nav-tabs" id="myList" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-toggle="list" href="#ss-new-students" role="tab">New Students</a></li>
                  <li class="nav-item"><a class="nav-link" data-toggle="list" href="#ss-continuing-students" role="tab">Continuing Students</a></li>
                </ul>

               <div class="tab-content">
                 <div class="tab-pane active" id="ss-new-students" role="tabpanel">
                    {!! Form::open(['url'=>'registration/update-registration-deadline','class'=>'ss-form-processing']) !!}
                    <div class="card-body">
                      @php
                        if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator')){                 
                        $date = [
                            'placeholder'=>'Registration deadline',
                            'class'=>'form-control ss-datepicker',
                            'required'=>true
                        ];
                        }else{
                        $date = [
                            'placeholder'=>'Registration deadline',
                            'class'=>'form-control',
                            'readonly'=>true,
                            'required'=>true
                        ];
                        }
                      @endphp
                      
                      <div class="row">
                        <div class="form-group col-12">
                          <table class="table table-bordered">
                            <thead>
                               <tr>
                                 <th>Date</th>
                                 <th>Intake</th>
                                 <th>Applicable Levels</th>
                                 @if(Auth::user()->hasRole('administrator'))
                                 <th>Campus</th>
                                 @endif
                                 @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admission-officer'))
                                 <th>Action</th>
                                 @endif
                               </tr>
                            </thead>
                            <tbody>
                              @foreach($registration_dates as $registration_date)
                              <tr>
                                <td>{{ App\Utils\DateMaker::toStandardDate($registration_date->date) }}</td>
                                <td>{{ $registration_date->intake }}</td>
                                <td>{{ implode(', ',unserialize($registration_date->applicable_levels)) }}</td>
                                @if(Auth::user()->hasRole('administrator'))
                                  <td>
                                    @foreach($campuses as $campus)
                                      @if($campus->id == $registration_date->campus_id)
                                        {{ $campus->name }}
                                        @break
                                      @endif
                                    @endforeach
                                  </td>
                                @endif
                                @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admission-officer'))
                                <td>
                                  <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-new-registration-date-{{ $registration_date->id }}">
                                    <i class="fas fa-pencil-alt"></i>
                                    Edit
                                  </a>
                                  <div class="modal fade" id="ss-edit-new-registration-date-{{ $registration_date->id }}">
                                    <div class="modal-dialog modal-lg">
                                      <div class="modal-content">
                                        <div class="modal-header">
                                          <h4 class="modal-title">Edit Registration Date</h4>
                                          <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                          </button>
                                        </div>
                                        {!! Form::open(['url'=>'registration/update-registration-deadline?special_date_id='.$registration_date->id,'class'=>'ss-form-processing']) !!}
                                            @php
                                              $academic_year = null;
                                              $ac_yr = [
                                                'class'=>'form-control',                      
                                                'readonly'=>true,
                                                'required'=>true                                      
                                              ];
                                              $intak = '';
                                              foreach($intakes as $intake){
                                                if($intake->name == $registration_date->intake){
                                                  $intak = $intake->name;
                                                  break;
                                                }
                                              }
                                              $intake = [
                                                'placeholder'=>$intak, 
                                                'class'=>'form-control',                      
                                                'readonly'=>true,
                                                'required'=>true                                      
                                              ];
                                              foreach($study_academic_years as $year){
                                                if($year->id == $registration_date->study_academic_year_id){ 
                                                  $academic_year = $year->academicYear->year;
                                                  break;
                                                }
                                              }

                                              if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator')){                 
                                                $date = [
                                                    'placeholder'=>'Registration date',
                                                    'class'=>'form-control ss-datepicker',
                                                    'required'=>true
                                                ];
                                              }else{
                                                $date = [
                                                    'placeholder'=>'Registration date',
                                                    'class'=>'form-control',                      
                                                    'readonly'=>true,
                                                    'required'=>true
                                                ];
                                              }
                                            @endphp
                                             <div class="card-body">
                                               <div class="row">
                                                <div class="form-group col-4">
                                                  {!! Form::label('','Study Academic Year') !!}
                                                  {!! Form::text('study_academic_year',$academic_year,$ac_yr) !!}
                                                </div>
                        
                                                <div class="form-group col-4">
                                                  {!! Form::label('','Intake') !!}
                                                  {!! Form::text('intake',$intak,$intake) !!}
                                               </div>
          
                                                <div class="form-group col-4">
                                                  {!! Form::label('','Applicable Levels') !!}
                                                  <select name="applicable_level[]" class="form-control ss-select-tags" multiple="multiple" required disabled="disabled">
                                                    @foreach($awards as $award)
                                                      @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                                                      <option value="{{ $award->name }}" @if(in_array($award->name,unserialize($registration_date->applicable_levels))) selected="selected" @endif>{{ $award->name }}</option>
                                                      @endif
                                                    @endforeach
                                                  </select>
                                                </div>
                                                <div class="form-group col-4">
                                                  {!! Form::label('','Registration date') !!}
                                                  {!! Form::text('registration_date',App\Utils\DateMaker::toStandardDate($registration_date->date),$date) !!}
                                                  
                                                  {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                                  {!! Form::input('hidden','name','registration') !!}
                                                </div>
                                                <div class="form-group col-4">
                                                  {!! Form::label('','Select campus') !!}
                                                  <select name="campus_id" class="form-control" required>
                                                     <option value="">Select Campus</option>
                                                     @foreach($campuses as $cp)
                                                     <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @else disabled='disabled' @endif>
                                                     {{ $cp->name }}</option>
                                                     @endforeach
                                                  </select>
                                                </div>
                                                {!! Form::input('hidden','campus_id',$campus->id) !!}
                                                 
                                                </div>
                                              </div> 
                                  @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admission-officer'))
                                  <div class="card-footer">
                                     <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                   </div>
                                 @endif  
                                 {!! Form::close() !!}
                                  </div>
                                </td>
                                @endif
                              </tr>
                              @endforeach
                            </tbody>
                          </table> 
                        </div>
                      </div>
                    </div>
                    @if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator'))              
                      <div class="card-footer">
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                      </div>
                    @endif  
                    {!! Form::close() !!}
                  </div><!-- /tabpane -->

                  <div class="tab-pane" id="ss-continuing-students" role="tabpanel">

                  </div>
              @endif
             </div>
            @endif


          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  @include('layouts.footer')

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

@endsection
