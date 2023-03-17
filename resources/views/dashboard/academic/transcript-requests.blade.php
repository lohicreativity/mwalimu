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
            <h1>{{ __('Transcript Requests') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Transcript Requests') }}</li>
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

            @if(count($transcript_requests) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Transcript Requests</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Student</th>
                    <th>Payment Status</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>

                  @foreach($transcript_requests as $req)
                  <tr>
				 {!! Form::open(['url'=>'academic/transcript-requests/', 'class'=>'ss-form-processing', 'method'=>'GET']) !!}
				 {!! Form::input('hidden','academic/transcript-issuance/', $req->student->id) !!}
                    <td>{{ $req->student->first_name }} {{ $req->student->middle_name }} {{ $req->student->surname }}</td>
                    <td>@if($req->payment_status == 'PAID') 
                           <span class="badge badge-success">Complete</span>
                        @else 
                           <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td>@if($req->status != null)
							<span class="badge badge-success">Issued</span>
						@else
							<span class="badge badge-warning">Pending</span>
						@endif
					</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="{{ url('academic/transcript/'.$req->student->id) }}" target="_blank">
                              <i class="fas fa-eye-open">
                              </i>
                              Preview
                       </a>
					   
						
						   <button class="btn btn-info btn-sm" type="submit"> {{ __('Issue Transcript') }}</button>
						 
                   </td>
                  </tr>
				  {!! Form::close() !!}
                  @endforeach
                  
                  </tbody>
                </table>

                <div class="ss-pagination-links">
                  {!! $transcript_requests->render() !!}
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Transcript Request Created') }}</h3>
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
