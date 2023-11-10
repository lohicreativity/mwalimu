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
            <h1>{{ __('Edit External Transfer') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Edit External Transfer') }}</li>
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
                <h3 class="card-title">Edit External Transfer</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                                   @php
				     $first_name = [
                         'class'=>'form-control',
                         'placeholder'=>'First name',
                         'required'=>true
                     ];
					 
					 $middle_name = [
                         'class'=>'form-control',
                         'placeholder'=>'Middle name',
                     ];
					 
					 $surname = [
                         'class'=>'form-control',
                         'placeholder'=>'Surname',
                         'required'=>true
                     ];
					 
                     if($transfer->status == 'PENDING'){
                      $prev_prog = [
                         'class'=>'form-control',
                         'placeholder'=>'Regulator Code',
                         'required'=>true
                     ];
                     }else{
                      $prev_prog = [
                         'class'=>'form-control',
                         'placeholder'=>'Regulator Code',
						             'readonly'=>true,
                         'required'=>true
                     ];
                     }
                     $index_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Index number',
						             'readonly'=>true,
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'application/update-external-transfer','class'=>'ss-form-processing']) !!}

                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter applicant index number') !!}
                    {!! Form::text('index_number',$transfer->applicant->index_number,$index_number) !!} 
                        @if($transfer->applicant->avn_no_results == 1)<span style="color:red; float: left"> Missing Diploma Results </span> @endif
					
					{!! Form::input('hidden','transfer_id',$transfer->id) !!}
                  </div>
				  <div class="form-group col-6">
				     {!! Form::label('','Entry mode') !!}
				     <select name="entry_mode" class="form-control" required>
                       <option value="">Select Highest Qualification</option>
                       <option value="DIRECT" @if($transfer->applicant->entry_mode == 'DIRECT') selected="selected" @endif
                        @if($transfer->status != 'PENDING') disabled="disabled" @endif>Form IV or VI (Direct)</option>
                       <option value="EQUIVALENT" @if($transfer->applicant->entry_mode == 'EQUIVALENT') selected="selected" @endif
                        @if($transfer->status != 'PENDING') disabled="disabled" @endif>Certificate or Diploma (Equivalent)</option>
                     </select>
					</div>
                  </div>
				  <div class="row">
				   <div class="form-group col-6">
                   {!! Form::label('','Enter previous programme code') !!}
                   {!! Form::text('program_code',$transfer->previous_program,$prev_prog) !!}
                 </div> 
                 @if($transfer->status == 'NOT ELIGIBLE') 
				 <div class="form-group col-6">
                   {!! Form::label('','Select new programme') !!}
                   <select name="campus_program_id" class="form-control" required>
                      <option value="">Select New Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}" @if($transfer->new_campus_program_id == $program->id) selected="selected" @endif>{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                 </div> 
                 @endif
              </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->



            

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
