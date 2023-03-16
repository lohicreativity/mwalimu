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
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Clearance List</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        
        <!-- Main row -->
        <div class="row">
          <div class="col-12">

             <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Select Study Academic Year') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/clearance','class'=>'ss-form-processing','method'=>'GET']) !!}
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                    </div>
                    <div class="form-group col-6">
                    {!! Form::label('','Programme level') !!}
                    <select name="award_id" class="form-control" required>
                      <option value="">Select Programme Level</option>
                      @foreach($awards as $award)
                      @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                       <option value="{{ $award->id }}">{{ $award->name }}</option>
                       @endif
                      @endforeach
                    </select>
                  </div>
                   </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
            
            @if($study_academic_year)
            <div class="card">
              <div class="card-header">
                 <h3 class="card-title">Clearance List</h3>
              </div>
              <div class="card-body">
                
                  {!! Form::open(['url'=>'academic/clearance','method'=>'GET']) !!}

                  {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for student name or registration number">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!} 

                {!! Form::open(['url'=>'academic/bulk-clearance','class'=>'ss-form-processing']) !!}

                {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                 <table class="table table-bordered ss-paginated-table">
                   <thead>
                    <tr>
                      <th>Student</th>
                      <th>Reg. No.</th>
                      <th>Mobile Phone </th>
                      <th>Programme</th>
                      @if(Auth::user()->hasRole('examination-officer'))
                      <th>Status</th>
                      @endif
                      @if(Auth::user()->hasRole('finance-officer'))
                      <th>Clearance</th>
                      @endif
                      @if(Auth::user()->hasRole('librarian'))
                      <th>Clearance</th>
                      @endif
                      @if(Auth::user()->hasRole('dean-of-students'))
                      <th>Clearance</th>
                      @endif
                      @if(Auth::user()->hasRole('hod'))
                      <th colspan='2'>Clearance</th>
                      @endif
                    </tr>
                   </thead>
                   <tbody>
                    @foreach($clearances as $clearance)
                     <tr>
                       <td>{{ $clearance->student->first_name }} {{ $clearance->student->middle_name }} {{ $clearance->student->surname }}</td>
                       <td>{{ $clearance->student->registration_number }}</td>
					   <td>{{ $clearance->student->phone }}</td>
                       <td>{{ $clearance->student->campusProgram->program->code }}</td>

                       @if(Auth::user()->hasRole('examination-officer'))
                       <td>@if($clearance->library_status === 1 && $clearance->hostel_status === 1 && $clearance->finance_status === 1 && $clearance->hod_status === 1 ) 
						   	<span class="badge badge-success">
								Cleared
							</span>
							@else
													   	<span class="badge badge-danger">
								Pending
							</span>
                       
                       @endif </td>
					   @endif
					   
                       @if(Auth::user()->hasRole('finance-officer'))
                       <td>@if($clearance->finance_status === 0) <i class="fa fa-ban"></i> @else <i class="fa fa-check"></i> @endif</td>
                      {{--<td><a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-stage-finance-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-finance-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="1">Cleared</option>
                                     <option value="0">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','finance') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                                  </div>
                              {!! Form::close() !!}
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
                       </td> --}}
                       <td>
                        {!! Form::checkbox('clearance_'.$clearance->id,$clearance->id,true) !!}
                        {!! Form::input('hidden','clear_'.$clearance->id,$clearance->id) !!}
                        {!! Form::input('hidden','group','finance-officer') !!}
                       </td>
                       @endif
                       @if(Auth::user()->hasRole('librarian'))
                       <td>@if($clearance->library_status === 0) <i class="fa fa-ban"></i> @else <i class="fa fa-check"></i> @endif</td>
                      {{-- <td>
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-stage-library-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-library-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="1">Cleared</option>
                                     <option value="0">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','library') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                                  </div>
                              {!! Form::close() !!}
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
                       </td> --}}
                       <td>
                        {!! Form::checkbox('clearance_'.$clearance->id,$clearance->id,true) !!}
                        {!! Form::input('hidden','clear_'.$clearance->id,$clearance->id) !!}
                        {!! Form::input('hidden','group','librarian') !!}
                       </td>
                       @endif
                       @if(Auth::user()->hasRole('dean-of-students'))

                       <td>@if($clearance->hostel_status === 0) <i class="fa fa-ban"></i> @else <i class="fa fa-check"></i> @endif</td>
                       {{--<td>
                         
                         <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-stage-hostel-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-hostel-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="1">Cleared</option>
                                     <option value="0">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','hostel') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                                  </div>
                              {!! Form::close() !!}
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
                       </td> --}}
                       <td>
                        {!! Form::checkbox('clearance_'.$clearance->id,$clearance->id,true) !!}
                        {!! Form::input('hidden','clear_'.$clearance->id,$clearance->id) !!}
                        {!! Form::input('hidden','group','dean-of-students') !!}
                       </td>
                       @endif
                      @if(Auth::user()->hasRole('hod'))

                       <td>@if($clearance->hod_status === 0) <i class="fa fa-ban"></i> @else <i class="fa fa-check"></i> @endif</td>
                       {{--<td>
                           
                           <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-stage-hod-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-hod-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="1">Cleared</option>
                                     <option value="0">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','hod') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                                  </div>
                              {!! Form::close() !!}
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
                       </td>--}}
                       <td>
                        {!! Form::checkbox('clearance_'.$clearance->id,$clearance->id,true) !!}
                        {!! Form::input('hidden','clear_'.$clearance->id,$clearance->id) !!}
                        {!! Form::input('hidden','group','hod') !!}
                       </td>
                       @endif
                       
                     </tr>
                    @endforeach
                      <tr>
                      <td colspan="8">
					  @if(!Auth::user()->hasRole('examination-officer'))
                        <button type="submit" class="btn btn-primary">Clear All Selected</button>
                      </td>
					  @endif
                    </tr> 
                   </tbody>
                 </table>
                {!! Form::close() !!}
              </div>
              
            </div>
          </div>
           @endif
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
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
