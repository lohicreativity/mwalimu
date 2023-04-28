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
            <h1>{{ __('Loan Beneficiaries') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Loan Beneficiaries') }}</li>
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
                <h3 class="card-title">Search for Loan Beneficiaries</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'finance/loan-beneficiaries','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Select year of study') !!}
                    <select name="year_of_study" class="form-control">
                       <option value="">Select Year of Study</option>
                       <option value="1">1</option>
                       <option value="2">2</option>
                       <option value="3">3</option>
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

            @if(count($beneficiaries) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Loan Beneficiaries') }}</h3><br>
                @if(Auth::user()->hasRole('loan-officer'))
                <a href="{{ url('finance/download-loan-beneficiaries?study_academic_year_id='.$request->get('study_academic_year_id').'&year_of_study='.$request->get('year_of_study')) }}" class="btn btn-primary">Download Loan Beneficiaries</a>
                @endif
              </div>
              <!-- /.card-header -->
              <div class="card-body">                  
                <table class="table table-bordered ss-paginated-table">
                   <thead>
                     <tr>
					   <th>SN</th>
                       <th>Index#</th>
                       <th>Name</th>
                       <th>Sex</th>
					   <th>Phone</th>
					   @if($request->get('loan_status') == 1)
                       <th>Total (TZS)</th>
                       <th>Status</th>
                       <th>Action</th> 					   
					   @else
                       <th>Tuition Fee</th>
                       <th>Books & Stationaries</th>
                       <th>Meals & Accomodation</th>
                       <th>Field</th>
                       <th>Research</th>
                       <th>Total (TZS)</th>
					   @endif
                     </tr>
                   </thead>
                   <tbody>
                     @foreach($beneficiaries as $key=>$stud)
                      <tr>
						<td>{{ ($key+1) }}</td>
                        <td>{{ $stud->index_number }}</td>
                        <td>{{ $stud->name }}</td>					
                        <td>{{ $stud->sex }}</td>
                        <td>{{ $stud->phone }}</td>
						@if($request->get('loan_status') == 1)
                        <td>{{ number_format($stud->loan_amount,2) }}</td>
                        <td>
							@if($postponements)
								@foreach($postponements as $post_stud)
									@if($post_stud->student_id == $stud->student_id)
										Postponed({{ $post_stud->category}})
										@break
									@endif
								@endforeach
							@endif
							@if($deceased)
								@foreach($deceased as $dic_stud)
									@if($dic_stud->student_id == $stud->student_id)
										Deceased
										@break
									@endif
								@endforeach
							@endif
							@if($transfers)
								@foreach($transfers as $trans_stud)
									@if($trans_stud->student_id == $stud->student_id)
										Transfered ({{ $trans_stud->previousProgram->program->code}} to {{ $trans_stud->currentProgram->program->code}})				
										@break
									@endif
								@endforeach
							@endif
						</td>
						<td>
							@if($postponements)
								@foreach($postponements as $post_stud)
									@if($post_stud->student_id == $stud->student_id)																	
										<a class="btn btn-info btn-sm" href="{{ url('finance/update-loan-beneficiaries?student_id='.$stud->student_id.'&postponement_status=1&loan_status=1') }}">
										  <i class="fas fa-eye-open"></i>
										  Remove
										</a>	
										@break
									@endif
								@endforeach
							@endif
							@if($deceased)
								@foreach($deceased as $dic_stud)
									@if($dic_stud->student_id == $stud->student_id)
										<a class="btn btn-info btn-sm" href="{{ url('finance/update-loan-beneficiaries?student_id='.$stud->student_id.'&deceased_status=1&loan_status=1') }}">
										  <i class="fas fa-eye-open"></i>
										  Remove
										</a>	
										@break
									@endif
								@endforeach
							@endif
							@if($transfers)
								@foreach($transfers as $trans_stud)
									@if($trans_stud->student_id == $stud->student_id)
										<a class="btn btn-info btn-sm" href="{{ url('finance/update-loan-beneficiaries?student_id='.$stud->student_id.'&transfer_status=1&loan_status=1') }}">
										  <i class="fas fa-eye-open"></i>
										  Change
										</a>	
										@break
									@endif
								@endforeach
							@endif
					
						</td>		
						@else		
                        <td>{{ $stud->tuition_fee }}</td>
                        <td>{{ $stud->books_and_stationeries }}</td>
                        <td>{{ $stud->meals_and_accomodation }}</td>
                        <td>{{ $stud->field_training }}</td>
                        <td>{{ $stud->research }}</td>
                        <td>{{ number_format($stud->loan_amount,2) }}</td>
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
