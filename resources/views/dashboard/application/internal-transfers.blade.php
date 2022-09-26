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
            <h1>{{ __('Internal Transfer') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Internal Transfer') }}</li>
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

    
            @if(count($transfers) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Internal Transfers</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                   <table class="table table-bordered" id="ss-transfers">
                     <thead>
                       <tr>
                         <th>Name</th>
                         <th>Index Number</th>
                         <th>Previous Programme</th>
                         <th>Current Programme</th>
                         <th>Date Transfered</th>
                         <th>Transfered By</th>
                       </tr>
                     </thead>
                     <tbody>
                      @foreach($transfers as $transfer)
                       <tr>
                         <td>{{ $transfer->applicant->first_name }} {{ $transfer->applicant->middle_name }} {{ $transfer->applicant->surname }}</td>
                         <td>{{ $transfer->applicant->index_number }}</td>
                         <td>{{ $transfer->previousProgram->program->name }}</td>
                         <td>{{ $transfer->currentProgram->program->name }}</td>
                         <td>{{ $transfer->created_at }}</td>
                         <td>{{ $transfer->user->staff->first_name }} {{ $transfer->user->staff->surname }}</td>
                       </tr>
                       @endforeach
                     </tbody>
                   </table>

                   <div class="ss-pagination-links">
                      {!! $transfers->render() !!}
                   </div> 
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
