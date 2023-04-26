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
            <h1>{{ __('Resumptions') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Resumptions') }}</li>
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
                <h3 class="card-title">Select Academic Year</h3>
              </div>
              <!-- /.card-header -->
                 <div class="card-body">
                 {!! Form::open(['url'=>'academic/resumptions','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->

            @if(count($postponements) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Resumptions</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'academic/accept-resumptions','class'=>'ss-form-processing']) !!}

                {!! Form::input('hidden','study_academic_year_id',$request->get('study_academic_year_id')) !!}
              <div class="card-body">

                
                 
                <table id="example2" class="table table-bordered table-hover ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Student</th>
                    <th>Reg Number</th>
                    <th>Semester</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date Resumed</th>
                    @if(Auth::user()->hasRole('hod') || Auth::user()->hasRole('arc') || Auth::user()->hasRole('administrator'))                     
                      @if(!Auth::user()->hasRole('hod'))
                      <th>Recommendation</th>
                      @endif
                      <th>Actions</th>
                      @if(!Auth::user()->hasRole('hod'))
                      <th>Accept</th>
                      @endif
                    @endif
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($postponements as $key=>$post)
                  <tr>
                    <td>{{ ($key+1) }} </td>
                    <td>{{ $post->student->first_name }} {{ $post->student->middle_name }} {{ $post->student->surname }}</td>
                    <td>{{ $post->student->registration_number }}</td>
                    <td>@if($post->semester) {{ $post->semester->name }} @endif</td>
                    <td>{{ $post->category }}</td>
                    <td>{{ $post->status }}</td>
                    <td>{{ Carbon\Carbon::parse($post->resumed_at)->format('Y-m-d') }} @if($post->is_renewal == 1) * @endif</td>
                    @if(Auth::user()->hasRole('hod') || Auth::user()->hasRole('arc') || Auth::user()->hasRole('administrator'))                      
                    @if(!Auth::user()->hasRole('hod'))
                    <td>@if($post->resume_recommended == 1) <a href="{{ url('academic/postponement/'.$post->id.'/resume/recommend') }}">Recommended</a> @else <a href="{{ url('academic/postponement/'.$post->id.'/resume/recommend') }}">Not Recommended</a> @endif</td>
                    @endif
                    <td>
                      @if(Auth::user()->hasRole('hod'))
                      @if($post->status == 'PENDING' || $post->status == 'POSTPONED')
                      <a class="btn btn-info btn-sm" href="{{ url('academic/postponement/'.$post->id.'/resume/recommend') }}">
                              <i class="fas fa-eye-open">
                              </i>
                              @if($post->resumption_recommendation) Edit Recommendation @else Recommend @endif
                       </a>
                       @endif
                       @else
                       
                      @if($post->status == 'POSTPONED')
                      <a class="btn btn-success btn-sm" href="#" data-toggle="modal" data-target="#ss-accept-post-{{ $post->id }}">
                              <i class="fas fa-check">
                              </i>
                              Resume
                       </a>
                       @endif

                       <div class="modal fade" id="ss-accept-post-{{ $post->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-12">
                                    <div id="ss-confirmation-container">
                                       <p id="ss-confirmation-text">Are you sure you want to resume this postponement?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/postponement/'.$post->id.'/resume') }}" class="btn btn-success">Resume</a>
                                         </div><!-- end of ss-form-controls -->
                                      </div><!-- end of ss-confirmation-container -->
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
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

                      
                       @endif
                    </td>
                    @if(!Auth::user()->hasRole('hod'))
                      <td>
                        @if($post->status == 'POSTPONED')
                        {!! Form::checkbox('post_'.$post->id,$post->id,true) !!}
                        @endif
                      </td>
                    @endif
                    @endif
                  </tr>
                  @endforeach
                  @if(Auth::user()->hasRole('hod') || Auth::user()->hasRole('arc') || Auth::user()->hasRole('administrator'))                 
                    @if(!Auth::user()->hasRole('hod'))
                    <tr>
                      <td colspan="9">
                        
                        <input type="submit" class="btn btn-primary" name="action" value="Accept Selected"> <input type="submit" class="btn btn-primary" name="action" value="Decline Selected">
                        
                      </td>
                    </tr>
                    @endif
                  @endif
                  </tbody>
                </table>
                
              </div>
              <!-- /.card-body -->
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Postponement Created') }}</h3>
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
