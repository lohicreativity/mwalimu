@extends('layouts.app-login')

@section('content')
<div class="login-box">
  <div class="login-logo">
    <a href="{{ url('/') }}"><img src="{{ asset('img/logo.png') }}" class="ss-site-icon"></a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Applicant Registration</p>

        @if(session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

      <form action="{{ url('application/registration/store') }}" method="POST" class="ss-form-processing">
        @csrf
        <div class="input-group mb-3">
          <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" placeholder="First name" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}" placeholder="Middle name (Optional)">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" name="surname" class="form-control" value="{{ old('surname') }}" placeholder="Surname" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <select name="program_level_id" class="form-control" id="ss-program-level" required>
             <option value="">Select Program Level</option>
             @foreach($awards as $award)
             @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Masters'))
             <option value="{{ $award->id }}" @if(old('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
             @endif
             @endforeach
          </select>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-graduation-cap"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <select name="entry_mode" class="form-control" id="ss-entry-mode" required>
             <option value="">Select Highest Qualification</option>
             <option value="DIRECT" @if(old('entry_mode') == 'DIRECT') selected="selected" @endif>Form IV or VI (Direct)</option>
             <option value="EQUIVALENT" @if(old('entry_mode') == 'EQUIVALENT') selected="selected" @endif>Certificate or Diploma (Equivalent)</option>
          </select>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-briefcase"></span>
            </div>
          </div>
        </div>
        <span style='color:red' class='ss-font-sm'>Format: S0000/0000/2023 or EQ2022000000/2023</span>
        <div class="input-group mb-3">
          <input type="text" name="index_number" class="form-control" value="{{ old('index_number') }}" placeholder="Form IV Index Number" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-key"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" value="{{ old('password') }}" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
		<div class="input-group mb-3">
          <input type="password" name="password_confirmation" class="form-control" value="{{ old('password_confirmation') }}" placeholder="Password confirmation" required>
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

<script type="text/javascript">
  window.onload = function(){
      
      $('#ss-program-level').on('change',function(e){
          console.log(e.target.value);
          $.ajax({
              url:'/application/get-award-by-id?id='+$(e.target).val(),
              method:'GET'
          }).done(function(data){
              console.log(data);
              if(data.award != null){
                var text = data.award.name;
/*                 if(text.includes('Certificate')){
                  var element = '<option value="">Select Highest Qualification</option>';
                      element += '<option value="DIRECT">Form IV or VI (Direct)</option>';
                }else{ 
                  var element = '<option value="">Select Highest Qualification</option>';
                      element += '<option value="DIRECT">Form IV or VI (Direct)</option>';
                      element += '<option value="EQUIVALENT">Certificate or Diploma (Equivalent)</option>';
                }*/
				var element = '<option value="">Select Highest Qualification</option>';
			        element += '<option value="DIRECT">Form IV or VI (Direct)</option>';
			        element += '<option value="EQUIVALENT">Certificate or Diploma (Equivalent)</option>';
                $('#ss-entry-mode').html(element);
              }

          });
      });

  };
</script>
@endsection