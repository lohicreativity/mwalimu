
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          @if(Auth::user()->hasRole('applicant'))
            @if($applicant->status == null)
              <li class="nav-item">
                <a href="{{ url('application/basic-information') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Basic Information @if($applicant->basic_info_complete_status == 1) <i class="fa fa-check"></i> @endif</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/next-of-kin') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Next of Kin @if($applicant->next_of_kin_complete_status == 1) <i class="fa fa-check"></i> @endif</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/payments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Payments @if($applicant->payment_complete_status == 1) <i class="fa fa-check"></i> @endif</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/results') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Request Results @if($applicant->results_complete_status == 1) <i class="fa fa-check"></i> @endif</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/select-programs') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Select Programmes @if($applicant->programs_complete_status == 1) <i class="fa fa-check"></i> @endif</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/upload-documents') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Upload Documents @if($applicant->documents_complete_status == 1) <i class="fa fa-check"></i> @endif</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/submission') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Submit Application @if($applicant->submission_complete_status == 1) <i class="fa fa-check"></i> @endif</p>
                </a>
              </li>
              @endif
              @if($applicant->status == 'ADMITTED')
              <li class="nav-item">
                <a href="{{ url('application/admission-confirmation') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Selection Confirmation</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/other-information') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>More Information</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('admission/payments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Payments</p>
                </a>
              </li>
              @endif
          @elseif(Auth::user()->hasRole('student'))
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-list-alt"></i>
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
              <i class="nav-icon fas fa-check-circle"></i>
              <p>
                {{ __('Registration') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('student/registration') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>My Registration</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-file-pdf"></i>
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
              <li class="nav-item">
                <a href="{{ url('student/results/'.session('active_academic_year_id').'/'.$student->year_of_study.'/report/appeal?semester_id='.session('active_semester_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Appeal Results</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('student/request-transcript') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Transcript</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-coins"></i>
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
              <li class="nav-item">
                <a href="{{ url('student/request-control-number') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Request Control Number</p>
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
                <a href="{{ url('application/application-windows') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Application Windows</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/application-window-campus-programs?campus_id='.session('staff_campus_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Offered Programmes') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/application-dashboard?campus_id='.session('staff_campus_id').'&application_window_id='.session('active_window_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Application Dashboard') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/search-for-applicant') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Password Reset</p>
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
                <a href="{{ url('application/entry-requirements?application_window_id='.session('active_window_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Entry Requirements</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/entry-requirements-capacity?application_window_id='.session('active_window_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Program Max Capacity</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/run-selection?application_window_id='.session('active_window_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Run Selection</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/selected-applicants?application_window_id='.session('active_window_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Selected Applicants</p>
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
                <a href="{{ url('application/upload-attachments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Upload Attachments</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/applicants-admission') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Admit Students</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/insurance-statuses?application_window_id='.session('active_window_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Insurance Status</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/hostel-statuses?application_window_id='.session('active_window_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Hostel Request</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('application/internal-transfer') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Internal Transfer</p>
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
                <a href="{{ url('application/applicants-registration?application_window_id='.session('active_window_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Applicants Registration</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-coins"></i>
              <p>
                {{ __('Finance') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('finance/fee-types') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Fee Types') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('finance/fee-items') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Fee Items') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('finance/fee-amounts') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Fee Amounts') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('finance/program-fees') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Programme Fees') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('finance/payments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Payments') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('finance/nacte-payments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('NACTE Payments') }}</p>
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
              @can('view-assigned-modules')
              <li class="nav-item">
                <a href="{{ url('academic/staff-module-assignments?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Assigned Modules</p>
                </a>
              </li>
              @endcan
              @can('allocate-options')
              <li class="nav-item">
                <a href="{{ url('academic/options-allocations?study_academic_year_id='.session('active_academic_year_id').'&semester_id='.session('active_semester_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Allocate Options</p>
                </a>
              </li>
              @endcan
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-list-alt"></i>
              <p>
                {{ __('Results') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              @can('add-examination-irregularities')
              <li class="nav-item">
                <a href="{{ url('academic/results/examination-irregularities?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Irregularities') }}</p>
                </a>
              </li>
              @endcan
              @can('process-examination-results')
              <li class="nav-item">
                <a href="{{ url('academic/results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Results Processing') }}</p>
                </a>
              </li>
              @endcan 
               
              <li class="nav-item">
                <a href="{{ url('academic/results/appeals?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Results Appeals') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/performance-report-requests?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Statement of Results') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/results/student-mark-editing') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Marks Editing') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Best Students') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/postponements?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Postponements') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/results/global-report') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Global Report') }}</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-bookmark"></i>
              <p>
                {{ __('Transcripts') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('academic/run-graduants?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Graduants List</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/clearance?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Clearance</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/transcript-requests?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Transcript Requests</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/staff-module-assignments?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Graduation List</p>
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
              @can('add-staff')
              <!-- <li class="nav-item">
                <a href="{{ url('staff/staff/create') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add Staff</p>
                </a>
              </li> -->
              @endcan
              @can('view-staff-members')
              <li class="nav-item">
                <a href="{{ url('staff/staff-members') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Staff Members</p>
                </a>
              </li>
              @endcan
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book"></i>
              <p>
                {{ __('Academic Settings') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              @can('view-study-academic-years')
              <li class="nav-item">
                <a href="{{ url('academic/study-academic-years') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Study Academic Years') }}</p>
                </a>
              </li>
              @endcan
              @can('view-semesters')
              <li class="nav-item">
                <a href="{{ url('academic/semesters') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Semesters') }}</p>
                </a>
              </li>
              @endcan
              @can('view-intakes')
              <li class="nav-item">
                <a href="{{ url('settings/intakes') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Intakes</p>
                </a>
              </li>
              @endcan
              @can('view-level-of-study')
              <li class="nav-item">
                <a href="{{ url('settings/levels') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Levels of Study</p>
                </a>
              </li>
              @endcan
              @can('view-awards')
              <li class="nav-item">
                <a href="{{ url('academic/awards') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Awards</p>
                </a>
              </li>
              @endcan
              @can('view-nta-levels')
              <li class="nav-item">
                <a href="{{ url('settings/nta-levels') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>NTA Levels</p>
                </a>
              </li>
              @endcan
              @can('view-programmes')
              <li class="nav-item">
                <a href="{{ url('academic/programs') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Programmes') }}</p>
                </a>
              </li>
              @endcan
              
              <!-- <li class="nav-item">
                <a href="{{ url('academic/academic-years') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Academic Years') }}</p>
                </a>
              </li> -->
              
              
              @can('view-programme-modules')
              <li class="nav-item">
                <a href="{{ url('academic/program-module-assignments?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Programme Modules') }}</p>
                </a>
              </li>
              @endcan
              @can('view-modules')
              <li class="nav-item">
                <a href="{{ url('academic/module-assignments?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Modules') }}</p>
                </a>
              </li>
              @endcan
              @can('view-streams-and-groups')
              <li class="nav-item">
                <a href="{{ url('academic/streams?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Streams and Groups') }}</p>
                </a>
              </li>
              @endcan
              @can('view-elective-deadlines')
              <li class="nav-item">
                <a href="{{ url('academic/elective-module-limits?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Elective Deadlines') }}</p>
                </a>
              </li>
              @endcan
              @can('view-elective-policies')
              <li class="nav-item">
                <a href="{{ url('academic/elective-policies?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Elective Policies') }}</p>
                </a>
              </li>
              @endcan
              @can('view-grading-policies')
              <li class="nav-item">
                <a href="{{ url('academic/grading-policies?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Grading System') }}</p>
                </a>
              </li>
              @endcan
              <li class="nav-item">
                <a href="{{ url('settings/graduation-date?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Graduation Date') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('academic/enrollment-report?study_academic_year_id='.session('active_academic_year_id')) }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Enrollment Report') }}</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p>
                {{ __('General Settings') }}
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              @can('view-campuses')
              <li class="nav-item">
                <a href="{{ url('settings/campuses') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Campuses</p>
                </a>
              </li>
              @endcan
              @can('view-departments')
              <li class="nav-item">
                <a href="{{ url('academic/departments') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>{{ __('Departments') }}</p>
                </a>
              </li>
              @endcan
             @can('view-roles')
              <li class="nav-item">
                <a href="{{ url('settings/roles') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Roles</p>
                </a>
              </li>
             @endcan 
             @can('view-system-modules')
              <li class="nav-item">
                <a href="{{ url('settings/system-modules') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>System Modules</p>
                </a>
              </li>
              @endcan
            </ul>
          </li>
          @endif
        </ul>
      </nav>
      <!-- /.sidebar-menu