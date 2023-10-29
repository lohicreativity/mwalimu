@extends('layouts.app')

@section('content')



<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('dist/img/logo.png') }}" alt="{{ Config::get('constants.SITE_NAME') }}" height="60" width="60">
  </div>

  @include('layouts.auth-header')

  @include('layouts.sidebar')

  <!-- Content Wrapper. Contains page content -->
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{ __('Student Search') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Student Search') }}</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <!-- /.card-header -->

                <div class="card-body">
                 {!! Form::open(['url'=>'registration/print-id-card','class'=>'ss-form-processing','method'=>'GET']) !!}

                  @if(Auth::user()->hasRole('administrator'))
                   <div class="row">
                    <div class="form-group col-4">
                    {!! Form::label('','Study academic year') !!}
						<select name="study_academic_year_id" class="form-control" required>
						   <option value="">Select Academic Year</option>
						   @foreach($study_academic_years as $year)
						   <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
						   @endforeach
						</select>
                    </div>
                    <div class="form-group col-4">
                    {!! Form::label('','Programme level') !!}
						<select name="program_level_id" class="form-control" required>
						  <option value="">Select Programme Level</option>
						  @foreach($awards as $award)
						  @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
						  <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
						  @endif
						  @endforeach
						</select>
                    </div>
                    <div class="form-group col-4">
                    {!! Form::label('','Select campus') !!}
						<select name="campus_id" class="form-control" required>
						   <option value="">Select Campus</option>
						   @foreach($campuses as $cp)
						   <option value="{{ $cp->id }}" @if($request->get('campus_id') == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
						   @endforeach
						</select>
                    </div>
                   </div>
                   @else
                   <div class="row">
					   <div class="form-group col-6">
						{!! Form::input('hidden','campus_id',$staff->campus_id) !!}
						{!! Form::label('','Study academic year') !!}
						<select name="study_academic_year_id" class="form-control" required>
						   <option value="">Select Academic Year</option>
						   @foreach($study_academic_years as $year)
						   <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
						   @endforeach
						</select>
					  </div>
					  <div class="form-group col-6">
						{!! Form::label('','Programme level') !!}
						<select name="program_level_id" class="form-control" required>
						  <option value="">Select Programme Level</option>
						  @foreach($awards as $award)
						  @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
						  <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
						  @endif
						  @endforeach
						</select>
					  </div>
                    </div>
                    @endif
					<div class="ss-form-actions">
					   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
					</div>

                 {!! Form::close() !!}
				</div>
            </div>
            <!-- /.card -->

            @if(count($students) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Students Awaiting IDs - {{ $study_academic_year->academicYear->year }}</h3><br>

              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover ss-margin-top ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Reg. No.</th>
                    <th>Names</th>
                    <th>Sex</th>
                    <th>Phone</th>
                    <th>Programme</th>
                    @if(Auth::user()->hasRole('admission-officer'))
                    <th>Action</th>
                    @endif
                  </tr>
                  </thead>
                  <tbody>

					@foreach($students as $key=>$student)
						<tr>
						  <td>{{ ($key+1) }}</td>
						  <td>{{ $student->registration_number }}</td>
						  <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</td>
						  <td>{{ $student->gender }}</td>
						  <td>{{ $student->phone }}</td>
						  <td>{{ $student->campusProgram->code }}</td>
						  @if(Auth::user()->hasRole('admission-officer'))
							<td>
							  <a class="btn btn-success btn-sm" href="#" data-toggle="modal" data-target="#ss-student-id-{{ $student->id }}" onclick=getDraggableDiv("{{ $student->id }}")>
									  <i class="fas fa-check">
									  </i>
									  Compose ID
							  </a>

                              <a class="btn btn-success btn-sm" href="show-id-card/?registration_number={{ str_replace('/', '-',$student->registration_number )}}">

									  <i class="fas fa-check">
									  </i>
									  Print Card
							  </a>



			<div class="modal fade" id="ss-student-id-{{ $student->id }}">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">

						<div class="card-body">
						    <table class="table table-bordered">

                            <tr>
							   <td>
								 <div id="ss-my-camera-{{ $student->id }}" class="ss-margin-bottom"></div>
								 <input type=button value="Configure" onClick="configure('{{$student->id}}')" class="btn btn-primary">
								 <input type=button value="Take Snapshot" onClick="take_snapshot('{{$student->id}}')" class="btn btn-primary">

                                 <br>
							   </td>
							 </tr>
                             <tr>
                                <td>
                                    <div id="ss-camera-results-{{ $student->id }}">
                                        @if(file_exists(public_path().'/uploads/'.$student->image))
                                        <img id="ss-camera-prev" src="{{ public_path().'/uploads/'.$student->image }}"/>
                                        @endif
                                        <br>
                                    </div>
                                    <br>
                                    <br>
                                    <input type=button value="Save Snapshot" onClick="saveSnap('{{$student->id}}')" class="btn btn-primary">
                                </td>
                             </tr>

                             <tr>
                                <td>
								   <div id="crop_wrapper">
									  <img src="{{ asset('uploads/'.$student->image) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'">
									  <div id="cropped_{{ $student->id }}" class="crop_div">
									  </div>
									</div>
								 </td>
                             </tr>
							 <tr>
								 <td>
								   <form method="post" action="{{ url('registration/crop-student-image') }}" onsubmit="return crop('{{$student->id}}');">
									  @csrf
									  <input type="hidden" value="" id="top_{{ $student->id }}" name="top">
									  <input type="hidden" value="" id="left_{{ $student->id }}" name="left">
									  <input type="hidden" value="" id="right_{{ $student->id }}" name="right">
									  <input type="hidden" value="" id="bottom_{{ $student->id }}" name="bottom">
									  <input type="hidden" value="{{$student->image}}" name="image">
									  <input type="submit" name="crop_image" value="Crop Image" class="btn btn-primary">
									</form>
									<div id="crop_result"  class="ss-margin-top">
									  <img src="{{ asset('avatars/'.$student->image) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'">
									</div>
								 </td>
							 </tr>

							 <tr>
							    <td>
								 <table border="1" cellpadding="0" width="10%">
									<tbody><tr>
										<td height="100" width="auto">
											<canvas id="cnv_{{ $student->id }}" name="cnv" width="auto" height="100"></canvas>
										</td>
									</tbody></tr>
								 </table>

								<br> <br> <br>
                                <!-- Signature -->
                                    <img src="{{ asset('signatures/'.$student->signature) }}" id="sign_prev">
                                <br>
							    <canvas name="SigImg" id="SigImg" width="auto" height="100"></canvas>
							    <p id="sigWebVrsnNote" style="font-family: Arial;">SigWeb 1.7.0 installed</p>


							  <form action="https://www.sigplusweb.com/sigwebtablet_demo.html#" name="FORM1">
								<p>
								  <input id="SignBtn" name="SignBtn" type="button" value="Sign" onclick="javascript:onSign('{{$student->id}}')" class="btn btn-primary">&nbsp;&nbsp;&nbsp;&nbsp;
								  <input id="button1" name="ClearBtn" type="button" value="Clear" onclick="javascript:onClear()" class="btn btn-primary">&nbsp;&nbsp;&nbsp;&nbsp;

								  <input id="button2" name="DoneBtn" type="button" value="Done" onclick="javascript:onDone('{{$student->id}}')" class="btn btn-primary">&nbsp;&nbsp;&nbsp;&nbsp;

								  <input type="HIDDEN" name="bioSigData" id="bioSigData_{{ $student->id }}">
									<input type="HIDDEN" name="sigImgData">
									  <br>
										<br>
										  <input name="sigStringData" id="sigStringData_{{ $student->id }}" type="hidden">
										  <input name="sigImageData" id="sigImageData_{{ $student->id }}" type="hidden">
										  <input name="sign_data" type="hidden" id="sign_data_{{ $student->id }}">
										</p>
							  </form>

									  <br><br>
								<p id="SigWebVersion"></p>
								<p id="SigWebTabletJSVersion"></p>
								<p id="CertificateExpirationDate"></p>

								<script type="text/javascript">
								  var tmr;
                                  var print_student_id;

								  var resetIsSupported = false;
								  var SigWeb_1_6_4_0_IsInstalled = false; //SigWeb 1.6.4.0 and above add the Reset() and GetSigWebVersion functions
								  var SigWeb_1_7_0_0_IsInstalled = false; //SigWeb 1.7.0.0 and above add the GetDaysUntilCertificateExpires() function

								  window.onload = function(){
									if(IsSigWebInstalled()){
									  var sigWebVer = "";
									  try{
										sigWebVer = GetSigWebVersion();
									  } catch(err){console.log("Unable to get SigWeb Version: "+err.message)}

									  if(sigWebVer != ""){
										try {
										  SigWeb_1_7_0_0_IsInstalled = isSigWeb_1_7_0_0_Installed(sigWebVer);
										} catch( err ){console.log(err.message)};
										//if SigWeb 1.7.0.0 is installed, then enable corresponding functionality
										if(SigWeb_1_7_0_0_IsInstalled){

										  resetIsSupported = true;
										  try{
											var daysUntilCertExpires = GetDaysUntilCertificateExpires();
											document.getElementById("daysUntilExpElement").innerHTML = "SigWeb Certificate expires in " + daysUntilCertExpires + " days.";
										  } catch( err ){console.log(err.message)};
										  var note = document.getElementById("sigWebVrsnNote");
										  note.innerHTML = "SigWeb 1.7.0 installed";
										} else {
										  try{
											SigWeb_1_6_4_0_IsInstalled = isSigWeb_1_6_4_0_Installed(sigWebVer);
											//if SigWeb 1.6.4.0 is installed, then enable corresponding functionality
										  } catch( err ){console.log(err.message)};
										  if(SigWeb_1_6_4_0_IsInstalled){
											resetIsSupported = true;
											var sigweb_link = document.createElement("a");
											sigweb_link.href = "https://www.topazsystems.com/software/sigweb.exe";
											sigweb_link.innerHTML = "https://www.topazsystems.com/software/sigweb.exe";

											var note = document.getElementById("sigWebVrsnNote");
											note.innerHTML = "SigWeb 1.6.4 is installed. Install the newer version of SigWeb from the following link: ";
											note.appendChild(sigweb_link);
										  } else{
											var sigweb_link = document.createElement("a");
											sigweb_link.href = "https://www.topazsystems.com/software/sigweb.exe";
											sigweb_link.innerHTML = "https://www.topazsystems.com/software/sigweb.exe";

											var note = document.getElementById("sigWebVrsnNote");
											note.innerHTML = "A newer version of SigWeb is available. Please uninstall the currently installed version of SigWeb and then install the new version of SigWeb from the following link: ";
											note.appendChild(sigweb_link);
										  }
										}
									  } else{
										//Older version of SigWeb installed that does not support retrieving the version of SigWeb (Version 1.6.0.2 and older)
										var sigweb_link = document.createElement("a");
										sigweb_link.href = "https://www.topazsystems.com/software/sigweb.exe";
										sigweb_link.innerHTML = "https://www.topazsystems.com/software/sigweb.exe";

										var note = document.getElementById("sigWebVrsnNote");
										note.innerHTML = "A newer version of SigWeb is available. Please uninstall the currently installed version of SigWeb and then install the new version of SigWeb from the following link: ";
										note.appendChild(sigweb_link);
									  }
									}
									else{
									  alert("Unable to communicate with SigWeb. Please confirm that SigWeb is installed and running on this PC.");
									}
									}

								  function isSigWeb_1_6_4_0_Installed(sigWebVer){
									var minSigWebVersionResetSupport = "1.6.4.0";

									if(isOlderSigWebVersionInstalled(minSigWebVersionResetSupport, sigWebVer)){
									  console.log("SigWeb version 1.6.4.0 or higher not installed.");
									  return false;
									}
									return true;
								  }

								  function isSigWeb_1_7_0_0_Installed(sigWebVer) {
								  var minSigWebVersionGetDaysUntilCertificateExpiresSupport = "1.7.0.0";

								  if(isOlderSigWebVersionInstalled(minSigWebVersionGetDaysUntilCertificateExpiresSupport, sigWebVer)){
									  console.log("SigWeb version 1.7.0.0 or higher not installed.");
									  return false;
									}
									return true;
								  }

								  function isOlderSigWebVersionInstalled(cmprVer, sigWebVer){
									  return isOlderVersion(cmprVer, sigWebVer);
								  }

								  function isOlderVersion (oldVer, newVer) {
									const oldParts = oldVer.split('.')
									const newParts = newVer.split('.')
									for (var i = 0; i < newParts.length; i++) {
									  const a = parseInt(newParts[i]) || 0
									  const b = parseInt(oldParts[i]) || 0
									  if (a < b) return true
									  if (a > b) return false
									}
									return false;
								  }

								  function onSign(id)
								  {
									if(IsSigWebInstalled()){
									  var canvas = document.getElementById('cnv_'+id);
									  var ctx = canvas.getContext('2d');
									  ctx.clearRect(0, 0, canvas.width, canvas.height);
									  SetDisplayXSize( 500 );
									  SetDisplayYSize( 100 );
									  SetTabletState(0, tmr);
									  SetJustifyMode(0);
									  ClearTablet();
									  if(tmr == null)
									  {
										tmr = SetTabletState(1, ctx, 50);
									  }
									  else
									  {
										SetTabletState(0, tmr);
										tmr = null;
										tmr = SetTabletState(1, ctx, 50);
									  }
									} else{
									  alert("Unable to communicate with SigWeb. Please confirm that SigWeb is installed and running on this PC.");
									}
								  }

								  function onClear()
								  {
									ClearTablet();
								  }

								  function onDone(id)
								  {
									if(NumberOfTabletPoints() == 0)
									{
									  alert("Please sign before continuing");
									}
									else
									{
									  SetTabletState(0, tmr);
									  //RETURN TOPAZ-FORMAT SIGSTRING
									  SetSigCompressionMode(1);
									//   document.FORM1.bioSigData.value=GetSigString();
                                    document.getElementById('bioSigData_'+id).value = GetSigString();
                                    document.getElementById('sigStringData_'+id).value = GetSigString();
                                    console.log(document.getElementById('sigStringData_'+id).value = GetSigString());
									//   document.FORM1.sigStringData.value = GetSigString();
									  // document.getElementById('sign_prev').src = GetSigString();
									  //this returns the signature in Topaz's own format, with biometric information

                                      print_student_id = id;
									  //RETURN BMP BYTE ARRAY CONVERTED TO BASE64 STRING
									  SetImageXSize(500);
									  SetImageYSize(100);
									  SetImagePenWidth(5);
									  GetSigImageB64(SigImageCallback);

									  // var ctx = document.getElementById('cnv').getContext('2d');
									  // ctx.clearRect(0, 0, ctx.width, ctx.height);

									  var canvas = document.getElementById("cnv_"+id);
									  var ctx = canvas.getContext('2d');
									  // ctx.clearRect(0, 0, canvas.width, canvas.height);
									  document.getElementById('cnv_'+id).style.backgroundColor = "transparent";
									  document.getElementById('cnv_'+id).style.opacity = "1";
									  var dataURL = canvas.toDataURL("image/png");
									  document.getElementById('sign_data_'+id).value = dataURL;

									  var xhttp = new XMLHttpRequest();
									  xhttp.onreadystatechange = function() {
										if (this.readyState == 4 && this.status == 200) {
										  // document.getElementById("demo").innerHTML = this.responseText;
										  // alert(this.responseText);
										  window.location.reload();
										}
									  };
									  xhttp.open("POST", "/application/upload-signature", true);
									  xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
									  xhttp.send('sign_image='+dataURL+'&student_id='+id);
									}
								  }

								  function SigImageCallback( str )
								  {
                                    document.getElementById('sigImageData_'+print_student_id).value = str;
									// document.FORM1.sigImageData.value = str;
								  }

								  function endDemo()
								  {
									ClearTablet();
									SetTabletState(0, tmr);
								  }

								  function close(){
									if(resetIsSupported){
									  Reset();
									} else{
									  endDemo();
									}
								  }

								  //Perform the following actions on
								  //  1. Browser Closure
								  //  2. Tab Closure
								  //  3. Tab Refresh
								  window.onbeforeunload = function(evt){
									close();
									clearInterval(tmr);
									evt.preventDefault(); //For Firefox, needed for browser closure
								  };
								</script>
							    </td>
							    <!-- <td>
								   <img src="{{ asset('signatures/'.$student->signature) }}" id="sign_prev">
							    </td> -->
							 </tr>
							 <tr>
								 <td>First name:   &nbsp  &nbsp  &nbsp  &nbsp  &nbsp {{ $student->first_name }}</td>
								 <!-- <td>{{ $student->first_name }}</td> -->
							 </tr>
							 <tr>
								 <td>Middle name:  &nbsp  &nbsp  &nbsp  &nbsp  &nbsp {{ $student->middle_name }}</td>
								 <!-- <td>{{ $student->middle_name }}</td> -->
							 </tr>
							 <tr>
								 <td>Surname: &nbsp  &nbsp  &nbsp  &nbsp  &nbsp {{ $student->surname }}</td>
								 <!-- <td>{{ $student->surname }}</td> -->
							 </tr>
						    </table>
						</div>
					</div>
				</div>
			</div>




						  </td>
						  @endif

						</tr>
					@endforeach
                  </tbody>
                </table>

			  </div>
			</div>
			@endif
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->

  </div>
  <!-- /.content-wrapper -->
  @include('layouts.footer')

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

@endsection
