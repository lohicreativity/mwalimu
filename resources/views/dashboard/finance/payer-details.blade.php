
	<div style="margin-top:20px;" class="modal fade" id="ss-progress-{{ $reg->student->id }}">
		<div class="modal-dialog modal-lg">
		  <div class="modal-content modal-lg">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			  </button>
			</div>
			<div class="modal-body">

				<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
				<!-- <div class="container bootstrap snippets bootdey"> -->
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-3 col-sm-3">
							<div class="text-center">
								<img class="profile-user-img img-fluid" src="{{ asset('uploads/'.$reg->student->image) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'" alt="Student Picture">
												 
							</div> <!-- /.thumbnail -->

						</div> <!-- /.col -->


						<div class="col-md-9 col-sm-9">
							<h2>{{ $reg->student->first_name }} {{ $reg->student->middle_name }} {{ $reg->student->surname }}</h2>
							<h6>{{ $reg->student->registration_number }} &nbsp; | &nbsp; {{ $reg->student->campusProgram->program->code}} &nbsp; | &nbsp; Year {{ $reg->student->year_of_study }} &nbsp; | &nbsp; <span style="color:red">{{ $reg->student->studentshipStatus->name }} </span></h6>
							<hr>
							<ul style="list-style-type: none; inline">
								<li><i class="icon-li fa fa-envelope"></i> &nbsp; &nbsp;{{ $reg->student->email }}</li>
								<li><i class="icon-li fa fa-phone"></i> &nbsp; &nbsp;{{ $reg->student->phone }}</li>
							</ul>
							<hr>

							<div class="accordion" id="student-accordion">
								<div class="card">
								  <div class="card-header" id="ss-address">
									  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseAddress" aria-expanded="true" aria-controls="collapseAddress">
										&nbsp; More Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
									  </button>
								  </div>

								  <div id="collapseAddress" class="collapse" aria-labelledby="ss-address" data-parent="#student-accordion">
									<div class="card-body">

									  @if($reg->student->applicant)
										  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($reg->student->applicant->gender == 'M') Male @elseif($reg->student->applicant->nextOfKin->gender == 'F') Female @endif
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Date of Birth:</span> &nbsp; {{ $reg->student->applicant->birth_date }}
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $reg->student->applicant->nationality }}											  
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Disability:</span> &nbsp; {{ $reg->student->applicant->disabilityStatus->name }}
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Entry Mode:</span> &nbsp; {{ $reg->student->applicant->entry_mode }}	 												  
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $reg->student->applicant->address }}	 	
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; {{ $reg->student->applicant->ward->name }},&nbsp; {{ $reg->student->applicant->region->name }},&nbsp; {{ $reg->student->applicant->country->name }}	 	 
									  @endif
									</div>
								  </div>
								</div>
								
								<div class="card">
								  <div class="card-header" id="ss-next-of-kin">
									  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseNextOfKin" aria-expanded="true" aria-controls="collapseNextOfKin">
										&nbsp; Next Of Kin Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
									  </button>
								  </div>

								  <div id="collapseNextOfKin" class="collapse" aria-labelledby="ss-next-of-kin" data-parent="#student-accordion">
									<div class="card-body">

									  @if($reg->student->applicant->nextOfKin)
										  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Names:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->first_name }} {{ $reg->student->applicant->nextOfKin->middle_name }} {{ $reg->student->applicant->nextOfKin->surname }}
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($reg->student->applicant->nextOfKin->gender == 'M') Male @elseif($reg->student->applicant->nextOfKin->gender == 'F') Female @endif
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Relationship:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->relationship }}
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->nationality }}											  
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Phone:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->phone }}	
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->address }}
										  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->ward->name }},&nbsp; {{ $reg->student->applicant->nextOfKin->region->name }},&nbsp; {{ $reg->student->applicant->nextOfKin->country->name }}	 	 
																						  
									   @endif
									</div>
								  </div>
								</div>
							</div>                                  
						</div>
					</div>
				</div>
			</div>
		  </div>
		  <!-- /.modal-content -->
		</div> 
	  <!-- /.modal -->
	 </div>
