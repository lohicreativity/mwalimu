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
            <h1>{{ __('Loan Allocations') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Loan Allocations') }}</li>
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

            @if(count($loan_allocations) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Loan Allocations</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                  
                <table class="table table-bordered ss-paginated-table">
                   <thead>
                     <tr>
					   <th>SN</th>
                       <th>Study Academic Year</th>
                       <th>Year of Study</th>
                       <th>Tuition Fee</th>
                       <th>Books and Stationeries</th>
                       <th>Meals and Accommodation</th>
                       <th>Field Training</th>
                       <th>Research</th>
                       <th>Total Amount (TZS)</th>
                     </tr>
                   </thead>
                   <tbody>
                     @foreach($loan_allocations as $key=>$loan)
                      <tr>
						<td>{{ ($key+1) }}</td>
                        <td>{{ $loan->studyAcademicYear->academicYear->year }}</td>
                        <td>{{ $loan->year_of_study }}</td>
                        <td>{{ $loan->tuition_fee }}</td>
                        <td>{{ $loan->books_and_stationeries }}</td>
                        <td>{{ $loan->meals_and_accomodation }}</td>
                        <td>{{ $loan->field_training }}</td>
                        <td>{{ $loan->research }}</td>
                        <td>{{ number_format($loan->loan_amount,2) }}</td>
                      </tr>
                     @endforeach
                   </tbody>
                </table>

                <div class="ss-pagination-links">
                  {!! $loan_allocations->render() !!}
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
