<!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        @if(Auth::check() && Auth::user()->hasRole('student'))
        <a href="{{ url('student/dashboard') }}" class="nav-link">Home</a>
        @elseif(Auth::check() && Auth::user()->hasRole('applicant'))
        <a href="{{ url('application/dashboard') }}" class="nav-link">Home</a>
        @else
        <a href="{{ url('dashboard') }}" class="nav-link">Home</a>
        @endif
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- User Account Dropdown Menu -->
	  @if(Auth::user()->hasRole('student'))
		<span class="nav-link">{{ $student->first_name}} | BTC.COD | 2022/2023 | March &nbsp; </span>
      @endif
	<li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          @if(!Auth::user()->hasRole('student') && isset($staff))
          <a href="{{ url('staff/staff/'.$staff->id.'/show') }}" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile
          </a>
          @endif
          <a href="{{ url('change-password') }}" class="dropdown-item">
            <i class="fas fa-lock mr-2"></i> Change Password
          </a>
          <div class="dropdown-divider"></div>
          <!-- Authentication -->
          @if(Auth::check() && Auth::user()->hasRole('student'))
            <a href="{{ url('student/profile') }}" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile
            </a>
            <a href="{{ url('student/logout') }}" class="dropdown-item">
            <i class="fas fa-logout mr-2"></i> Logout
            </a>
          @elseif(Auth::check() && Auth::user()->hasRole('applicant'))
            <a href="{{ url('application/logout') }}" class="dropdown-item">
            <i class="fas fa-logout mr-2"></i> Logout
            </a>
          @else

           <form method="POST" action="{{ route('logout') }}">
                  @csrf
                            
                        <a href="{{ route('logout') }}"
                                         onclick="event.preventDefault();
                                                this.closest('form').submit();" class="dropdown-item">
            <i class="fas fa-sign-out mr-2"></i> Logout
          </a>
          </form>
         @endif
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <!-- <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li> -->
    </ul>
  </nav>
  <!-- /.navbar -->