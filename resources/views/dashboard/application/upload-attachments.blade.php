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
            <h1 class="m-0">Upload Admission Attachments</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Upload Admission Attachments</a></li>
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
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Upload Attachments') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'application/upload-attachment-file','files'=>true,'class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                    @if(Auth::user()->hasRole('administrator'))
                    <div class="form-group col-3">
                      {!! Form::label('','Attachment name') !!}
                      {!! Form::text('name',null,['class'=>'form-control','placeholder'=>'Attachment name','required'=>true]) !!}
                    </div>
                    <div class="form-group col-3">
                      {!! Form::label('','Upload attachment') !!}
                      {!! Form::file('attachment',['class'=>'form-control','required'=>true]) !!}
                    </div>
                    <div class="form-group col-3">
                      {!! Form::label('','Applicable Levels') !!}
                      <select name="applicable_level[]" class="form-control ss-select-tags" required multiple="multiple">
                        @foreach($awards as $award)
                          @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                          <option value="{{ $award->name }}" @if($request->get('applicable_level') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                          @endif
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group col-3">
                     {!! Form::label('','Campus') !!}
                     <select name="campus_id" class="form-control" required>
                        <option value="">Select campus</option>
                        @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                        @endforeach
                     </select>
                    </div>
                    @elseif(Auth::user()->hasRole('admission-officer'))
                    <div class="form-group col-4">
                      {!! Form::label('','Attachment name') !!}
                      {!! Form::text('name',null,['class'=>'form-control','placeholder'=>'Attachment name','required'=>true]) !!}
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Upload attachment') !!}
                      {!! Form::file('attachment',['class'=>'form-control','required'=>true]) !!}
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Applicable Levels') !!}
                      <select name="applicable_level[]" class="form-control ss-select-tags" required multiple="multiple">
                        @foreach($awards as $award)
                          @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                          <option value="{{ $award->name }}" @if($request->get('applicable_level') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                          @endif
                        @endforeach
                      </select>
                    </div>
                    <input type="hidden" name="campus_id" value="{{ $campus_id }}">
                    @endif
                    
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Upload Attachment') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            
            @if(count($attachments) > 0)
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Uploaded Attachments') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
                <div class="card-body">
                  <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Document</th>
                         <th>Applicable Levels</th>
                         @if(Auth::user()->hasRole('administrator'))
                         <th>Campus</th>
                         @endif
                         <th>Action</th>
                       </tr>
                    </thead>
                    <tbody>
                      @foreach($attachments as $attachment)
                      <tr>
                        <td><a href="{{ url('application/download-attachment?id='.$attachment->id) }}">{{ $attachment->name }}</a></td>
                        <td>{{ implode(', ',unserialize($attachment->applicable_levels)) }} </td>
                        @if(Auth::user()->hasRole('administrator'))
                        <td>
                          {{ $attachment->campus->name }}
                        </td>
                        @endif
                        <td><a href="{{ url('application/delete-attachment?id='.$attachment->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a></td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                  <div class="ss-pagination-links">
                     {!! $attachments->render() !!}
                  </div>
                </div>
              </div>
          </div>
          @endif
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
