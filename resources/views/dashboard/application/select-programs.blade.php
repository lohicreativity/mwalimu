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
            <h1 class="m-0">Select Programmes</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
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
        <div class="row">
          <div class="col-12">
            
            $if(count($applicant->selections) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Selections</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Choice</th>
                         <th>Programme</th>
                         <th>Campus</th>
                       </tr>
                    </thead>
                    <tbody>
                    @foreach($applicant->selections as $selection)
                    <tr>
                       <td>{{ $selection->order }}</td>
                       <td>{{ $selection->campusProgram->program->name }}</td>
                       <td>{{ $selection->campusProgram->campus->name }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                 </table>
              </div>
            </div>
            @endif

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Programmes</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @if(count($campus_programs) != 0)
                 <table class="table table-bordered">
                   <thead>
                     <tr>
                       <th>Programme</th>
                       <th>Campus</th>
                       <th>Action</th>
                     </tr>
                   </thead>
                   <tbody>
                      @foreach($campus_programs as $prog)
                      <tr>
                          <td>{{ $prog->program->name }}</td>
                          <td>{{ $prog->campus->name }}</td>
                          <td>
                            @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->selections,$prog))
                             <span>SELECTED</span>
                            @else
                              {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                                 {!! Form::input('hidden','campus_program_id',$prog->id) !!}

                                 {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                            <button type="submit" class="btn btn-primary">Select</button>
                              {!! Form::close() !!}
                            @endif
                          </td>
                      </tr>
                      @endforeach
                   </tbody>
                 </table>
                 @endif
              </div>
            </div>
          </div>
        </div>
        
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
