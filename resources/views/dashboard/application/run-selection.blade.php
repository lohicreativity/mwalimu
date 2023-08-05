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
            <h1 class="m-0">Run Selection</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Run Selection</a></li>
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
                 <ul class="nav nav-tabs">
                  <li class="nav-item"><a class="nav-link active" href="{{ url('application/run-selection?application_window_id='.session('active_window_id')) }}">{{ __('Run Selection By NTA Level') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('application/run-selection-program?application_window_id='.session('active_window_id')) }}">{{ __('Run Selection By Programme') }}</a></li>
                </ul>
              </div>
 
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Select Application Window') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/run-selection','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                    <div class="form-group col-12">
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} </option>
                        @endforeach
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
             
             @if($application_window)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Run Application Selection') }}</h3>
               </div>
               <!-- /.card-header -->
               {!! Form::open(['url'=>'application/run-applicants-selection','class'=>'ss-form-processing']) !!}
               <div class="card-body">
               <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Programme Level') !!}
                    <select onchange="val()" name="award_id" id="awards" class="form-control" data-target="#batches" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-batches') }}" required>
                      <option value="">Select Programme Level</option>
                      @foreach($awards as $award)
                      @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                       <option value="{{ $award->id }}">{{ $award->name }}</option>
                       @endif
                      @endforeach
                    </select>
                  </div>


                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                  </div>
               </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Run Selection') }}</button>
				  <a id="resetLink" href="{{ url('application/reset-selections?application_window_id='.$application_window->id.'&program_level_id=') }}" class="btn btn-primary">Reset Selection</a>
                </div>
              {!! Form::close() !!}
            </div>
            @endif
           </div>
          </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <script>
    function val () {
      selectValue = document.getElementById('awards').value;
      urlString = document.getElementById('resetLink').getAttribute('href');
      newStr = urlString.substring(0, urlString.indexOf('&program_level_id=') + '&program_level_id='.length);
      newUrl = newStr + selectValue;
      document.getElementById('resetLink').setAttribute("href", newUrl);
    }
  </script>
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
