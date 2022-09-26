<!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="{{ asset('dist/img/logo.png') }}" alt="Config::get('constants.SITE_NAME') }}" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">{{ Config::get('constants.SITE_SHORT_NAME') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          @if(isset($staff))
          <img src="{{ asset('avatars/'.$staff->image) }}" class="img-circle elevation-2" onerror="this.src='{{ asset("img/user-avatar.png") }}'">
          @endif
          @if(isset($student) && Auth::user()->hasRole('student'))
          <img src="{{ asset('avatars/'.$student->image) }}" class="img-circle elevation-2" onerror="this.src='{{ asset("img/user-avatar.png") }}'">
          @endif
        </div>
        <div class="info">
          @if(isset($staff))
          <a href="#" class="d-block">{{ $staff->first_name }} {{ $staff->surname }}</a>
          @endif
          @if(isset($student) && Auth::user()->hasRole('student'))
          <a href="#" class="d-block">{{ $student->first_name }} {{ $student->surname }}</a>
          @endif

        </div>
      </div>

      @include('layouts.auth-nav')
    </div>
    <!-- /.sidebar -->
  </aside>