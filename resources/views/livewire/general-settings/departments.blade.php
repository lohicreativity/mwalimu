<div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('List of Departments') }}</h3>
        </div>

        <!-- /.card-header -->
        <div class="card-body">
            @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admission-officer'))
                <table class="table table-bordered table-hover ss-admission-officer-table ss-paginated-table">
                    <thead>
                    <tr>
                        <th>SN</th>
                        <th>Name</th>
                        <th>Abbreviation</th>
                        <th>Type</th>
                        <th>Parent</th>
                        @if(Auth::user()->hasRole('administrator'))
                            <th>Campus</th>
                        @endif
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>


                    @foreach($campusDepartments as $key => $campusDepartment)
                        @php
                            $current_parent_id = $campusDepartment->department->id;
                            $dept_name = str_replace(' Of ',' of ',$campusDepartment->department->name);
                            $dept_name = str_replace(' And ',' and ',$dept_name);
                        @endphp
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $dept_name }}</td>
                            <td>{{ $campusDepartment->department->abbreviation }}</td>
                            <td>{{ $campusDepartment->department->unitCategory->name }}</td>
                            <td>{{ $campusDepartment->department->parent?->name }}</td>
                            <td>{{ $campusDepartment->campus->name }}</td>
                            <td>
                                @can('edit-department')
                                    <a class="btn btn-info btn-sm" href="#"
                                       data-toggle="modal"
                                       wire:click="setSelectedDepartment({{$campusDepartment}})"
                                       data-target="#edit-department-modal">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            @endif



        </div>
        <!-- /.card-body -->
    <div wire:ignore.self class="modal fade" id="edit-department-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Department</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @php
                                                    $current_campus_id = $prev_campus_id;
                    @endphp
                    {{ $prev_campus_id }}
                    @if(filled($selectedDepartment))
                        @php
                            $name = [
                              'placeholder'=>'Name',
                              'class'=>'form-control',
                              'required'=>true
                            ];

                            $abbreviation = [
                              'placeholder'=>'Abbreviation',
                              'class'=>'form-control',
                              'required'=>true
                            ];

                            $description = [
                              'placeholder'=>'Description',
                              'class'=>'form-control',
                              'rows'=>2
                            ];
                            $current_edited_parent_id = $selectedDepartment->id;

                        @endphp

                        {!! Form::open(['url'=>'academic/department/update','class'=>'ss-form-processing']) !!}

                        @if(Auth::user()->hasRole('admission-officer'))
                            <input type="hidden" name="staff_campus" value="{{ $staff->campus_id }}">
                        @endif

                        <div class="row">
                            <div class="form-group col-8">
                                {!! Form::label('','Name') !!}
                                {!! Form::text('name',$selectedDepartment->name,$name) !!}

                                {!! Form::input('hidden','department_id',$selectedDepartment->id) !!}
                            </div>
                            <div class="form-group col-4">
                                {!! Form::label('','Abbreviation') !!}
                                {!! Form::text('abbreviation',$selectedDepartment->abbreviation,$abbreviation) !!}
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-12">
                                {!! Form::label('','Description') !!}
                                {!! Form::textarea('description',$selectedDepartment->description,$description) !!}
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-4">
                                {!! Form::label('','Campus') !!}
                                <select wire:model="campus_id" name="campus_id" class="form-control" required>
                                    <option value="">Select Campus</option>
                                    @foreach($campuses as $cp)
                                        <option value="{{ $cp->id }}">{{ $cp->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-4">
                                {!! Form::label('','Type') !!}
                                <select wire:model="unit_category_id" name="unit_category_id" class="form-control" required>
                                    <option value="">Select Type</option>
                                    @foreach($unit_categories as $category)
                                        <option value="{{ $category->id }}" >{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-4">
                                {!! Form::label('','Parent',array('id' => 'parent-label-edit')) !!}

                                <select wire:model="parent_id" name="parent_id" class="form-control">
                                    <option value="">Select Parent</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}" >{{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {!! Form::input('hidden','current_parent_id',$current_edited_parent_id) !!}
                            {!! Form::input('hidden','current_campus_id',$current_campus_id) !!}
                        </div>
                        <div class="ss-form-actions">
                            <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                        </div>

                        {!! Form::close() !!}
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

    </div>

