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

                    {!! Form::open(['url'=>'academic/accept-postponements','class'=>'ss-form-processing']) !!}


                    <table class="table table-bordered ss-margin-top ss-paginated-table">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Name</th>
                                <th>Sex</th>
                                <th>Form IV Index#</th>
                                <th>Form VI Index#/AVN</th>
                                <th>Award</th>
                                <th>Programme</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($applicants != null)
                              @foreach($applicants as $applicant)
                              <tr>
                                  <td>{{ $loop->iteration }}</td>
                                  <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                                  <td>@if($applicant->gender == 'F') Female @elseif($applicant->gender == 'M') Male @endif</td>
                                  <td>{{ $applicant->index_number }}</td>
                                  <td>
                                    @foreach($applicant->nectaResultDetails as $detail)
                                      @if($detail->exam_id == 2 && $detail->verified == 1) 
                                        {{ $detail->index_number }} 
                                      @endif
                                    @endforeach 
                                      
                                    @foreach($applicant->nacteResultDetails as $detail)
                                      @if($detail->verified == 1) 
                                        {{ $detail->avn }}
                                      @endif
                                    @endforeach
                                  </td>
                                  <td>{{ $applicant->programLevel->code }}</td>
                                  <td>

                                    @if(count($applicant->selections) > 0)
                                    @php $total_selections = $batch_id = 0;
                                        foreach($applicant->selections as $y){
                                          foreach($batches as $batch){
                                            if($batch->program_level_id == $applicant->program_level_id){
                                              if($batch->id > $batch_id){
                                                $batch_id = $batch->id;
                                              }
                                            }
                                          }
                                          if($y->batch_id == $batch_id){
                                            $total_selections++;
                                          }
                                        }
                                        $x = $total_selections; 
                                    @endphp
                                      @foreach($applicant->selections as $select)
                                              @if($select->batch_id == $batch_id)
                                                @if($total_selections > 1)
                                                  @php --$x ; @endphp
                                                  {{ $select->campusProgram->code }}@if($x != 0), @endif
                                                @else
                                                  {{ $select->campusProgram->code }}
                                                @endif
                                              @endif
                                      @endforeach
                                  @endif

                                  </td>
                                  <td>@if($applicant->status == 'SELECTED')  <span class="badge badge-success"> Selected </span> 
                                      @elseif($applicant->status == null) <span class="badge badge-warning"> Pending </span> 
                                      @elseif($applicant->status == 'NOT SELECTED') <span class="badge badge-danger"> Rejected </span> @endif</td>
                                  <td>
                                    <a target="_blank" class="btn btn-primary" href="{{ url('application/view-applicant-documents?applicant_id='.$applicant->id.'&application_window_id='.session('active_window_id')) }}">
                                      <i class="fas fa-list"></i>
                                      View Certificates
                                    </a>
                                  </td>
                              </tr>
                              @endforeach
                            @endif
                        </tbody>
                    </table>

                    {!! Form::close() !!}

                 
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
