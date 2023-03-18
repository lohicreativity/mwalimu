			
			@if($student->continue_status == 1)
				<div class="card">
              <div class="card-header">
                <h3 class="card-title">Selections</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Programme</th>
                       </tr>
                    </thead>
                    <tbody>
                    <tr>
                       <td>@if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->selections,1)) Choice Selected @else <a href="#" data-toggle="modal" data-target="#ss-first-choice">Select Programme</a> @endif</td>
                    </tr>
                   
                  </tbody>
                 </table>
              </div>
            </div>
            

             <div class="modal fade" id="ss-first-choice">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">Choose Programme</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      @if(count($campus_programs) != 0)
                     <table class="table table-bordered">
                       <thead>
                         <tr>
                           <th>Programme</th>
                           <th>Campus</th>
                           <th>Action</th>
                         </tr>
                       </thead>
                       <tbody>
                          @foreach($campus_programs as $prog)
                          <tr>
                              <td>{{ $prog->program->name }}</td>
                              <td>{{ $prog->campus->name }}</td>
                              <td>
                                @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->selections,$prog))
                                 <span>SELECTED</span>
                                @else
                                  {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                     {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                                     {!! Form::input('hidden','campus_program_id',$prog->id) !!}

                                     {!! Form::input('hidden','campus_id',$request->get('campus_id')) !!}

                                     {!! Form::input('hidden','choice',1) !!}

                                     {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                <button type="submit" class="btn btn-primary">Select</button>
                                  {!! Form::close() !!}
                                @endif
                              </td>
                          </tr>
                          @endforeach
                       </tbody>
                     </table>
                     @endif
                    </div>
                    <div class="modal-footer justify-content-between">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

              <div class="modal fade" id="ss-second-choice">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">2nd Choice Programme</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      @if(count($campus_programs) != 0)
                     <table class="table table-bordered">
                       <thead>
                         <tr>
                           <th>Programme</th>
                           <th>Campus</th>
                           <th>Action</th>
                         </tr>
                       </thead>
                       <tbody>
                          @foreach($campus_programs as $prog)
                          <tr>
                              <td>{{ $prog->program->name }}</td>
                              <td>{{ $prog->campus->name }}</td>
                              <td>
                                @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->selections,$prog))
                                 <span>SELECTED</span>
                                @else
                                  {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                     {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                                     {!! Form::input('hidden','campus_program_id',$prog->id) !!}

                                     {!! Form::input('hidden','choice',2) !!}

                                     {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                <button type="submit" class="btn btn-primary">Select</button>
                                  {!! Form::close() !!}
                                @endif
                              </td>
                          </tr>
                          @endforeach
                       </tbody>
                     </table>
                     @endif
                    </div>
                    <div class="modal-footer justify-content-between">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

              <div class="modal fade" id="ss-third-choice">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">3rd Choice Programme</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      @if(count($campus_programs) != 0)
                     <table class="table table-bordered">
                       <thead>
                         <tr>
                           <th>Programme</th>
                           <th>Campus</th>
                           <th>Action</th>
                         </tr>
                       </thead>
                       <tbody>
                          @foreach($campus_programs as $prog)
                          <tr>
                              <td>{{ $prog->program->name }}</td>
                              <td>{{ $prog->campus->name }}</td>
                              <td>
                                @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->selections,$prog))
                                 <span>SELECTED</span>
                                @else
                                  {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                     {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                                     {!! Form::input('hidden','campus_program_id',$prog->id) !!}

                                     {!! Form::input('hidden','choice',3) !!}

                                     {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                <button type="submit" class="btn btn-primary">Select</button>
                                  {!! Form::close() !!}
                                @endif
                              </td>
                          </tr>
                          @endforeach
                       </tbody>
                     </table>
                     @endif
                    </div>
                    <div class="modal-footer justify-content-between">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

              <div class="modal fade" id="ss-forth-choice">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">4th Choice Programme</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      @if(count($campus_programs) != 0)
                     <table class="table table-bordered">
                       <thead>
                         <tr>
                           <th>Programme</th>
                           <th>Campus</th>
                           <th>Action</th>
                         </tr>
                       </thead>
                       <tbody>
                          @foreach($campus_programs as $prog)
                          <tr>
                              <td>{{ $prog->program->name }}</td>
                              <td>{{ $prog->campus->name }}</td>
                              <td>
                                @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->selections,$prog))
                                 <span>SELECTED</span>
                                @else
                                  {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                     {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                                     {!! Form::input('hidden','campus_program_id',$prog->id) !!}

                                     {!! Form::input('hidden','choice',4) !!}

                                     {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                <button type="submit" class="btn btn-primary">Select</button>
                                  {!! Form::close() !!}
                                @endif
                              </td>
                          </tr>
                          @endforeach
                       </tbody>
                     </table>
                     @endif
                    </div>
                    <div class="modal-footer justify-content-between">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

            @if(count($applicant->selections) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Selections</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Programme</th>
                         <th>Campus</th>
                         <th>Action</th>
                       </tr>
                    </thead>
                    <tbody>
                    @foreach($applicant->selections as $key=>$selection)
                    <tr>
                       <td>{{ $selection->campusProgram->program->name }}</td>
                       <td>{{ $selection->campusProgram->campus->name }}</td>
                       <td>
                         @if($key == count($applicant->selections)-1 && !$program_fee_invoice)
                        <a href="{{ url('application/reset-program-selection/'.$selection->id) }}" class="ss-italic ss-color-danger">Reset Selection</a>
                         @endif
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                 </table>
              </div>
            </div>
            @endif

            @endif
