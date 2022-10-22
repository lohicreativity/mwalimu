@if($second_semester_publish_status)
                  @foreach($suppExams as $supp)
                    @if(count($special_exam_requests) != 0)
                      @foreach($special_exam_requests as $exam)
                        @foreach($exam->exams as $ex)
                          @if($ex->moduleAssignment->module->name == $supp->name)
                            <div class="col-3">
                              <div class="checkbox">
                                <label>
                                  {!! Form::checkbox('mod_assign_'.$supp->id,$supp->id, false, array('disabled')) !!}
                                  {{ $supp->name }}
                                </label>
                              </div>
                            </div> 
                          @endif
                        @endforeach  
                      @endforeach
                    @else 
                    <div class="col-3">
                      <div class="checkbox">
                        <label>
                          {!! Form::checkbox('mod_assign_'.$supp->id,$supp->id) !!}
                          {{ $supp->name }}
                        </label>
                      </div>
                    </div>
                    @endif 
                  @endforeach

                @else 

                  @foreach($module_assignments as $assign)

                      @if(count($special_exam_requests) != 0)
                        @foreach($special_exam_requests as $exam)
                          @foreach($exam->exams as $ex)
                            @if($ex->moduleAssignment->module->name == $assign->module->name)
                            @php 
                            $check_special_exam[] = $ex->moduleAssignment->module->name;
                            @endphp
                            <div class="col-3">
                              <div class="checkbox">
                                <label>
                                  {!! Form::checkbox('mod_assign_'.$assign->id,$assign->id, false, array('disabled')) !!}
                                  {{ $assign->module->name }}
                                </label>
                              </div>
                            </div> 
                            @endif                 
                          @endforeach
                        @endforeach
                      @endif 
                        
                        @if(sizeof($opted_module) == 0 && $assign->programModuleAssignment->category == 'OPTIONAL')
                        <div class="col-3">
                          <div class="checkbox">
                            <label>
                              {!! Form::checkbox('mod_assign_'.$assign->id,$assign->id) !!}
                              {{ $assign->module->name }}
                            </label>
                          </div>
                        </div>   
                        @elseif($assign->programModuleAssignment->category == 'OPTIONAL' && $opted_module[0]->module_id == $assign->module_id)
                        <div class="col-3">
                          <div class="checkbox">
                            <label>
                              {!! Form::checkbox('mod_assign_'.$assign->id,$assign->id) !!}
                              {{ $assign->module->name }}
                            </label>
                          </div>
                        </div>
                        @elseif($assign->programModuleAssignment->category == 'COMPULSORY')
                        <div class="col-3">
                          <div class="checkbox">
                            <label>
                              {!! Form::checkbox('mod_assign_'.$assign->id,$assign->id) !!}
                              {{ $assign->module->name }}
                            </label>
                          </div>
                        </div>
                      @endif 
                                                      
                  @endforeach

                @endif