<!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-copy"></i>
              <p>
                {{ __('Application') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/layout/top-nav.html" class="nav-link">
                  <i class="far fa-users nav-icon"></i>
                  <p>Applicants</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                {{ __('Selection') }}
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/charts/chartjs.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Admitted Students</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-check-circle"></i>
              <p>
                {{ __('Admission') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/UI/general.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Admitted Students</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-id-card"></i>
              <p>
                {{ __('Registration') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('registration/registered-students') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Registered Students</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>
                {{ __('Human Resources') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/UI/general.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Staff Members</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book"></i>
              <p>
                {{ __('Academic') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('academic/semesters') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Semesters') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/programs') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Programs') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/modules') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Modules') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/departments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Departments') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/academic-years') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Academic Years') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/study-academic-years') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Study Academic Years') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/academic-year-programs') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Academic Year Programs') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/module-assignments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Modules Assignments') }}</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-check-circle"></i>
              <p>
                {{ __('Examinations') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('academic/examinations') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Examinations</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p>
                {{ __('Settings') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('settings/nta-levels') }}" class="nav-link">
                  <i class="far fa-users nav-icon"></i>
                  <p>NTA Levels</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('settings/intakes') }}" class="nav-link">
                  <i class="far fa-users nav-icon"></i>
                  <p>Intakes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('settings/levels') }}" class="nav-link">
                  <i class="far fa-users nav-icon"></i>
                  <p>Levels of Study</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('settings/campuses') }}" class="nav-link">
                  <i class="far fa-users nav-icon"></i>
                  <p>Campuses</p>
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->