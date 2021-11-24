
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

          @if(Auth::user()->hasRole('student'))
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-building"></i>
              <p>
                {{ __('Modules') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('student/modules') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>My Modules</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-building"></i>
              <p>
                {{ __('Results') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('student/results') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>My Results</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-building"></i>
              <p>
                {{ __('Payments') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('student/payments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>My Payments</p>
                </a>
              </li>
            </ul>
          </li>
          @else
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
              <i class="nav-icon fas fa-building"></i>
              <p>
                {{ __('Organization') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('settings/campuses') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Campuses</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/departments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Departments') }}</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>
                {{ __('Human Resource') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('staff/staff/create') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add Staff</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('staff/staff-members') }}" class="nav-link">
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
                  <p>{{ __('Programmes') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/modules') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Modules') }}</p>
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
              <!-- <li class="nav-item">
                <a href="{{ url('academic/study-academic-year-campus-programs') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Academic Year Programs') }}</p>
                </a>
              </li> -->
              <li class="nav-item">
                <a href="{{ url('academic/program-module-assignments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Programme Modules') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/streams') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Streams and Groups') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/elective-module-limits') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Elective Deadlines') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/elective-policies') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Elective Policies') }}</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-folder-open"></i>
              <p>
                {{ __('Teaching') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('academic/module-assignments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Modules Assignment</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/staff-module-assignments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Staff Modules</p>
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
                <a href="{{ url('academic/awards') }}" class="nav-link">
                  <i class="far fa-users nav-icon"></i>
                  <p>Awards</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('settings/roles') }}" class="nav-link">
                  <i class="far fa-users nav-icon"></i>
                  <p>Roles</p>
                </a>
              </li>
            </ul>
          </li>
          @endif
        </ul>
      </nav>
      <!-- /.sidebar-menu