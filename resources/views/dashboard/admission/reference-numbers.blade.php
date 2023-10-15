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
            <h1>{{ __('Admission Reference Numbers') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Reference Numbers') }}</li>
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
                <h3 class="card-title">Add Reference Number</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'application/admission-reference-numbers/store','class'=>'ss-form-processing','method'=>'GET']) !!}  
                  <div class="row">

                    <div class="form-group col-4">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    @php
                      $intake = [
                          'placeholder'=>$app_window->intake->name,
                          'class'=>'form-control',                      
                          'readonly'=>true,
                          'required'=>true
                      ];
                    @endphp
                    
                    {!! Form::label('','Intake') !!}
                    {!! Form::text('intake',$app_window->intake->name,$intake) !!}
                 </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Applicable Levels') !!}
                    <select name="applicable_level[]" class="form-control ss-select-tags" required multiple="multiple">
                      @foreach($awards as $award)
                        @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                        <option value="{{ $award->name }}" @if($request->get('applicable_level') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>
                  @php
                  if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator')){                 
                     $reference_no = [
                        'placeholder'=>'Reference Number',
                        'class'=>'form-control',
                        'required'=>true
                     ];
                    }else{
                    $reference_no = [
                        'placeholder'=>'Reference Number',
                        'class'=>'form-control',                      
                        'readonly'=>true,
                        'required'=>true
                     ];
                    }
                  @endphp
                  <div class="form-group col-4">
                    {!! Form::label('','Reference Number') !!}
                    {!! Form::text('reference_number',null,$reference_no) !!}
                
                  </div>
                  @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))                  
                  <div class="form-group col-4">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  @else
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
                  @endif
                   
                  </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('Add Reference Number') }}</button>
              </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
             
            @if($study_academic_year && $campus && count($references) > 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Admission Reference Numbers for {{ $campus->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                     <tr>
                       <th>Intake</th>
                       <th>Reference#</th>
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
                    @foreach($references as $reference)
                    <tr>
                      <td>{{ $reference->intake }}</a></td>
                      <td>{{ $reference->name }}</a></td>
                      <td>{{ implode(', ',unserialize($reference->applicable_levels)) }} </td>
                      @if(Auth::user()->hasRole('administrator'))
                      <td>
                        @foreach($campuses as $campus)
                          @if($campus->id == $reference->campus_id)
                            {{ $campus->name }}
                            @break
                          @endif
                        @endforeach
                      </td>
                      @endif
                      @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admission-officer'))
                      <td>
                        <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-reference-number-{{ $reference->id }}">
                          <i class="fas fa-pencil-alt">
                          </i>
                          Edit
                        </a>

                        <div class="modal fade" id="#ss-edit-reference-number-{{ $reference->id }}">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Edit Reference Number</h4>
                                <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              {!! Form::open(['url'=>'application/admission-reference-numbers/update?reference_id='.$reference->id,'class'=>'ss-form-processing']) !!}
                                  @php
                                    $academic_year = null;
                                    $ac_yr = [
                                      'class'=>'form-control',                      
                                      'readonly'=>true,
                                      'required'=>true                                      
                                    ];
                                    foreach($study_academic_years as $year){
                                      if($year->id == $reference->study_academic_year_id){ 
                                        $academic_year = $year->academicYear->year;
                                        break;
                                      }
                                    }
                                  if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator')){                 
                                    $reference_no = [
                                        'placeholder'=>'Reference Number',
                                        'class'=>'form-control',
                                        'required'=>true
                                    ];
                                  }else{
                                    $reference_no = [
                                        'placeholder'=>'Reference Number',
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
              
                                      {!! Form::label('','Intake') !!}
                                      {!! Form::text('intake',$app_window->intake->name,$intake) !!}

                                      <div class="form-group col-4">
                                        {!! Form::label('','Applicable Levels') !!}
                                        <select name="applicable_level[]" class="form-control ss-select-tags" multiple="multiple" required>
                                          @foreach($awards as $award)
                                            @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                                            <option value="{{ $award->name }}" @if(in_array($award->name,unserialize($reference->applicable_levels))) selected="selected" @endif>{{ $award->name }}</option>
                                            @endif
                                          @endforeach
                                        </select>

                                        <select name="applicable_level[]" class="form-control ss-select-tags" multiple="multiple" required>
                                          @foreach($awards as $award)
                                            @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                                            <option value="{{ $award->name }}" @if(in_array($award->name,unserialize($reference->applicable_levels))) selected="selected" @endif>{{ $award->name }}</option>
                                            @endif
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Reference Number') !!}
                                        {!! Form::text('reference_number',null,$reference_no) !!}
                                        
                                        {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}

                                      </div>
                                      @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))                  
                                      <div class="form-group col-4">
                                        {!! Form::label('','Select campus') !!}
                                        <select name="campus_id" class="form-control" required>
                                           <option value="">Select Campus</option>
                                           @foreach($campuses as $cp)
                                           <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @endif>{{ $cp->name }}</option>
                                           @endforeach
                                        </select>
                                      </div>
                                      @else
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
                                      @endif
                                       
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
