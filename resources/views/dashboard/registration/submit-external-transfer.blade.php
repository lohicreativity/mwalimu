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
            <h1>{{ __('External Transfer') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('External Transfer') }}</li>
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
                <h3 class="card-title">Search for Applicant</h3>
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
					 
                     $index_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Form IV index number',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'application/register-external-transfer','class'=>'ss-form-processing']) !!}

                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter applicant index number') !!}
                    {!! Form::text('index_number',null,$index_number) !!}
                  </div>
				  <div class="form-group col-6">
				     {!! Form::label('','Entry mode') !!}
				     <select name="entry_mode" class="form-control" required>
                       <option value="">Select Highest Qualification</option>
                       <option value="DIRECT">Form IV or VI (Direct)</option>
                       <option value="EQUIVALENT">Certificate or Diploma (Equivalent)</option>
                     </select>
					</div>
                  </div>
				  <div class="row">
				   <div class="form-group col-6">
                   {!! Form::label('','Enter previous programme code') !!}
                   {!! Form::text('program_code',null,['class'=>'form-control','placeholder'=>'Programme code','required'=>true]) !!}
                 </div>  
				 <div class="form-group col-6">
                   {!! Form::label('','Select new programme') !!}
                   <select name="campus_program_id" class="form-control" required>
                      <option value="">Select New Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                 </div> 
              </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Register Applicant') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->



            @if(count($transfers) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">External Transfers</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
			  {!! Form::open(['url'=>'registration/submit-external-transfers','class'=>'ss-form-processing']) !!}
                   <table class="table table-bordered" id="ss-transfers">
                     <thead>
                       <tr>
					     <th>SN</th>
                         <th>Name</th>
                         <th>Index Number</th>
						 <th>Previous Programme</th>
                         <th>New Programme</th>
                         <th>Date Transfered</th>
                         <th>Transfered By</th>
						 <th>Status</th>
                       </tr>
                     </thead>
                     <tbody>
                      @foreach($transfers as $key=>$transfer)
                       <tr>
					     <td>{{ ($key+1) }} </td>
                         <td>@if($transfer->status == 'SUBMITTED') {{ $transfer->applicant->first_name }} {{ $transfer->applicant->middle_name }} {{ $transfer->applicant->surname }} @else <a href="{{ url('application/external-transfer/'.$transfer->id.'/edit') }}">{{ $transfer->applicant->first_name }} {{ $transfer->applicant->middle_name }} {{ $transfer->applicant->surname }}</a>@endif</td>
                         <td>{{ $transfer->applicant->index_number }}</td>
						 <td>{{ $transfer->previous_program }}</td>
                         <td>{{ $transfer->newProgram->program->code }}</td>
                         <td>{{ $transfer->created_at }}</td>
                         <td>{{ $transfer->user->staff->first_name }} {{ $transfer->user->staff->surname }}</td>
                         <td>{{ $transfer->status }} {!! Form::input('hidden','transfer_'.$transfer->id,$transfer->id) !!}</td>
                       </tr>

                      <div class="modal fade" id="ss-edit-external-{{ $transfer->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Edit External Transfer</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
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
					
					{!! Form::input('hidden','transfer_id',$transfer->id) !!}
                  </div>
				  <div class="form-group col-6">
				     {!! Form::label('','Entry mode') !!}
				     <select name="entry_mode" class="form-control" required>
                       <option value="">Select Highest Qualification</option>
                       <option value="DIRECT" @if($transfer->applicant->entry_mode == 'DIRECT') selected="selected" @endif>Form IV or VI (Direct)</option>
                       <option value="EQUIVALENT" @if($transfer->applicant->entry_mode == 'EQUIVALENT') selected="selected" @endif>Certificate or Diploma (Equivalent)</option>
                     </select>
					</div>
                  </div>
				  <div class="row">
				   <div class="form-group col-6">
                   {!! Form::label('','Enter previous programme code') !!}
                   {!! Form::text('program_code',$transfer->previous_program,['class'=>'form-control','placeholder'=>'Programme code','required'=>true]) !!}
                 </div>  
				 <div class="form-group col-6">
                   {!! Form::label('','Select new programme') !!}
                   <select name="campus_program_id" class="form-control" required>
                      <option value="">Select New Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}" @if($transfer->new_campus_program_id == $program->id) selected="selected" @endif>{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                 </div> 
              </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
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
					   <tr>
					     <td colspan="7"><button type="submit" class="btn btn-primary">Submit Transfers</button></td>
					   </tr>
					   {!! Form::close() !!}
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
