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
             <h1>{{ __('Streams and Groups') }}</h1>
           </div>
           <div class="col-sm-6">
             <ol class="breadcrumb float-sm-right">
               <li class="breadcrumb-item"><a href="#">Home</a></li>
               <li class="breadcrumb-item active">{{ __('Streams and Groups') }}</li>
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
                 <h3 class="card-title">{{ __('Select Study Academic Year') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'academic/streams','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                    <div class="form-group col-12">
                     <select name="study_academic_year_id" class="form-control" required>
                        <option value="">Select Study Academic Year</option>
                        @foreach($study_academic_years as $year)
                        <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
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
 
 
           @if(count($campus_programs) != 0 && $study_academic_year)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('List of Streams and Groups') }} - {{ $study_academic_year->academicYear->year }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Programme</th>
                         <th>Streams</th>
                         <th>Groups</th>
                       </tr>
                    </thead>
                    <tbody>
                     @foreach($campus_programs as $cp)
                       
 
                       @for($i = 1; $i <= $cp->program->min_duration; $i++)
                           @php
                             $stream_created = false;
                             $students_number = 0;
                           @endphp
                           @foreach($cp->students as $stud)
                             @foreach($stud->registrations as $reg)
                              @if($reg->year_of_study == $i)
                                 @php
                                   $students_number += 1;
                                 @endphp
                              @endif
                             @endforeach
                           @endforeach
                       <tr>
                        <td><a href="{{ url('academic/campus/campus-program/'.$cp->id.'/attendance?year_of_study='.$i.'&study_academic_year_id='.$study_academic_year->id) }}" target="_blank">{{ $cp->program->name }} - Year {{ $i }} ({{ $students_number }})</a>
                          @foreach($study_academic_year->streams as $stream)
                             @if($stream->campus_program_id == $cp->id && $stream->year_of_study == $i)
                             @php
                               $stream_created = true;
                             @endphp
                             @endif
                           @endforeach
                           @if($stream_created && $study_academic_year->id == session('active_academic_year_id'))

                           @can('reset-stream')
                          <p><a href="{{ url('academic/stream-reset?year_of_study='.$i.'&campus_program_id='.$cp->id.'&study_academic_year_id='.$study_academic_year->id) }}">{{ __('Reset Streams') }}</a></p>
                           @endcan

                           @endif
                         </td>
                         <td>   

                           @foreach($study_academic_year->streams as $stream)
                            @if($stream->campus_program_id == $cp->id && $stream->year_of_study == $i)
                            <p class="ss-no-margin"><a href="{{ url('academic/stream/'.$stream->id.'/attendance') }}" target="_blank">Stream_{{ $stream->name }}_({{ $stream->number_of_students }})</a></p>
                            @if($study_academic_year->id == session('active_academic_year_id'))

                            @can('delete-stream')
                            <a class="ss-font-xs ss-color-danger" href="{{ url('academic/stream/'.$stream->id.'/destroy') }}">Delete</a><br>
                            @endcan

                            @endif
                            @if(count($stream->groups) == 0 && $study_academic_year->id == session('active_academic_year_id'))

                            @can('create-group')
                            <a class="ss-font-xs" href="#" data-toggle="modal" data-target="#ss-add-group-{{ $i }}-{{ $stream->id }}">Create Groups</a>
                            @endcan

                            @endif
                             @php
                               $stream_created = true;
                             @endphp
                            @endif

                            <div class="modal fade" id="ss-add-group-{{ $i }}-{{ $stream->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Create Groups</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                {!! Form::open(['url'=>'academic/group/store','class'=>'ss-form-processing']) !!}

                                    <div class="form-group">
                                      {!! Form::label('','Number of groups') !!}
                                      <select name="number_of_groups" class="form-control" required>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                      </select>

                                      {!! Form::input('hidden','campus_program_id',$cp->id) !!}
                                      {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                      {!! Form::input('hidden','year_of_study',$i) !!}

                                      {!! Form::input('hidden','stream_id',$stream->id) !!}
                                    </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Create Groups') }}</button>
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

                           @endforeach
                             
                           @if(!$stream_created && $study_academic_year->id == session('active_academic_year_id'))

                           @can('create-stream')
                           <a href="#" data-toggle="modal" data-target="#ss-add-stream-{{ $i }}-{{ $cp->id }}">Create Streams</a></a>
                           @endcan 

                           @endif
 
                           <div class="modal fade" id="ss-add-stream-{{ $i }}-{{ $cp->id }}">
                         <div class="modal-dialog modal-lg">
                           <div class="modal-content">
                             <div class="modal-header">
                               <h4 class="modal-title">Create Streams</h4>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                 <span aria-hidden="true">&times;</span>
                               </button>
                             </div>
                             <div class="modal-body">

                                 {!! Form::open(['url'=>'academic/stream-component/store','class'=>'ss-form-processing']) !!}
 
                                     <div class="form-group">
                                       {!! Form::label('','Number of streams') !!}
                                       <select name="number_of_streams" class="form-control" required>
                                         <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                       </select>
 
                                       {!! Form::input('hidden','campus_program_id',$cp->id) !!}
                                       {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                       {!! Form::input('hidden','year_of_study',$i) !!}
                                     </div>
                                       <div class="ss-form-actions">
                                        <button type="submit" class="btn btn-primary">{{ __('Create Streams') }}</button>
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
                       
                           
                               

                               
 
                      

                        </td>
                        <td>
                          @foreach($study_academic_year->streams as $stream)
                            @if($stream->campus_program_id == $cp->id && $stream->year_of_study == $i)
                             @foreach($stream->groups as $group)
                              <p class="ss-no-margin"><a href="{{ url('academic/group/'.$group->id.'/attendance') }}" target="_blank">Group_{{ $group->name }}_Stream_{{ $stream->name }}_({{ $group->number_of_students }})</a></p>
                              @if($study_academic_year->id == session('active_academic_year_id'))

                              @can('delete-group')
                              <a class="ss-font-xs ss-color-danger" href="{{ url('academic/group/'.$group->id.'/destroy')}}">Delete</a>
                              @endcan 

                              @endif
                            @endforeach
                            @endif
                          @endforeach
                         </td>
                       </tr>
                       @endfor
                     @endforeach
                    </tbody>
                  </table>
               </div>
             </div>
             <!-- /.card -->
 
             @else
 
            
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('No Streams Created') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
               </div>
             </div>
             <!-- /.card -->
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