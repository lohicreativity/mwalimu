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
            <h1 class="m-0">Other Applicants</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Other Applicants</a></li>
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
             
            <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Other Applicants') }}</h3><br>
               </div>
               <!-- /.card-header -->
               <div class="card-body">

                    <table class="table table-bordered ss-margin-top ss-paginated-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Form IV Index No.</th>
                                <th>Form VI Index No./AVN</th>
                                <th>Programme</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applicants as $applicant)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                                <td>{{ $applicant->gender }}</td>
                                <td>{{ $applicant->index_number }}</td>
                                <td>
                                    @foreach($applicant->nectaResultDetails as $detail)
					                    @if($detail->exam_id == 2) 
                                            {{ $detail->index_number }} 
                                        @endif
						            @endforeach 
                                    
                                    @foreach($applicant->nacteResultDetails as $detail)
                                        {{ $detail->avn }}
                                    @endforeach
					            </td>
                                <td>
                                    @foreach($applicant->selections as $selection)
                                        {{ $selection->campusProgram->program->code }}
                                    @endforeach
                                </td>
                                <td>
                                    <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="">
                                        <i class="fas fa-list-alt"></i>
                                        View Documents
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                 
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
