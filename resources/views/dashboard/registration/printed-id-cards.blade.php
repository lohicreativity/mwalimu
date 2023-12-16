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
            <h1>{{ __('Student Search') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Printed ID Cards') }}</li>
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
              <!-- /.card-header -->

                <div class="card-body">
                 {!! Form::open(['url'=>'registration/printed-id-cards','class'=>'ss-form-processing','method'=>'GET']) !!}

                  @if(Auth::user()->hasRole('administrator'))
                   <div class="row">
                    <div class="form-group col-4">
                    {!! Form::label('','Study academic year') !!}
						<select name="study_academic_year_id" class="form-control" required>
						   <option value="">Select Academic Year</option>
						   @foreach($study_academic_years as $year)
						   <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
						   @endforeach
						</select>
                    </div>
                    <div class="form-group col-4">
                    {!! Form::label('','Programme level') !!}
						<select name="program_level_id" class="form-control" required>
						  <option value="">Select Programme Level</option>
						  @foreach($awards as $award)
						  @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
						  <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
						  @endif
						  @endforeach
						</select>
                    </div>
                    <div class="form-group col-4">
                    {!! Form::label('','Select campus') !!}
						<select name="campus_id" class="form-control" required>
						   <option value="">Select Campus</option>
						   @foreach($campuses as $cp)
						   <option value="{{ $cp->id }}" @if($request->get('campus_id') == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
						   @endforeach
						</select>
                    </div>
                   </div>
                   @else
                   <div class="row">
					   <div class="form-group col-6">
						{!! Form::input('hidden','campus_id',$staff->campus_id) !!}
						{!! Form::label('','Study academic year') !!}
						<select name="study_academic_year_id" class="form-control" required>
						   <option value="">Select Academic Year</option>
						   @foreach($study_academic_years as $year)
						   <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
						   @endforeach
						</select>
					  </div>
					  <div class="form-group col-6">
						{!! Form::label('','Programme level') !!}
						<select name="program_level_id" class="form-control" required>
						  <option value="">Select Programme Level</option>
						  @foreach($awards as $award)
						  @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
						  <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
						  @endif
						  @endforeach
						</select>
					  </div>
                    </div>
                    @endif
					<div class="ss-form-actions">
					   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
					</div>

                 {!! Form::close() !!}
				</div>
            </div>
            <!-- /.card -->

            @if(count($cards) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Printed ID Cards - {{ $study_academic_year->academicYear->year }}</h3><br>

              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover ss-margin-top ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Reg#</th>
                    <th>Names</th>
                    <th>Sex</th>
                    <th>Phone#</th>
                    <th>Programme</th>
                    <th>Card Serial#</th>
					<th>Printed By</th>
					<th>Printed On</th>					
                  </tr>
                  </thead>
                  <tbody>

					@foreach($cards as $key=>$card)
						<tr>
						  <td>{{ ($key+1) }}</td>
						  <td>{{ $card->student->registration_number }}</td>
						  <td>{{ $card->student->first_name }} {{ $card->student->middle_name }} {{ $card->student->surname }}</td>
						  <td>{{ $card->student->gender }}</td>
						  <td>{{ $card->student->phone }}</td>
						  <td>{{ $card->student->campusProgram->code }}</td>
						  <td>{{ $card->id_sn_no }} </td>
						  <td>@if($card->user){{ $card->user->staff->first_name }} {{ $card->user->staff->surname }} @endif</td>
						  <td>{{ $card->id_print_date }} </td>
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
