@extends('layouts.app-login')

@section('content')
<div class="login-box">
  <div class="login-logo">
    <a href="{{ url('/') }}"><img src="{{ asset('img/logo.png') }}" class="ss-site-icon"></a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Staffs Login</p>

        @if(session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

      <form action="{{ url('application/registration/store') }}" method="POST" class="ss-form-processing">
        @csrf
        <div class="input-group mb-3">
          <input type="text" name="first_name" class="form-control" placeholder="First name" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" name="middle_name" class="form-control" placeholder="Middle name">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" name="surname" class="form-control" placeholder="Surname" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <select name="program_level_id" class="form-control" required>
             <option value="">Select Program Level</option>
             @foreach($awards as $award)
             <option value="{{ $award->id }}">{{ $award->name }}</option>
             @endforeach
          </select>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-graduation-cap"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <select name="entry_mode" class="form-control" required>
             <option value="">Select Entry Mode</option>
             <option value="DIRECT">Direct Entry</option>
             <option value="EQUIVALENT">Equivalent Entry</option>
          </select>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-briefcase"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <select name="intake_id" class="form-control" required>
             <option value="">Select Intake</option>
             @foreach($intakes as $intake)
             <option value="{{ $intake->id }}">{{ $intake->name }}</option>
             @endforeach
          </select>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-clock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" name="index_number" class="form-control" placeholder="Form IV Index Number (S1002/0213/2015)" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-key"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <!-- <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
                Remember Me
              </label>
            </div> -->
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
      <!-- <p class="mb-0">
        <a href="register.html" class="text-center">Register a new membership</a>
      </p> -->
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->
@endsection