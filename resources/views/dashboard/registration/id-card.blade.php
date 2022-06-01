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
              <div class="card-header">
                <h3 class="card-title">Search for Student</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                     $reg_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Registration number',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'registration/print-id-card','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter student\'s registration number') !!}
                    {!! Form::text('registration_number',null,$reg_number) !!}
                  </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->


            @if($student)
              <div class="card">
              <div class="card-header">
                <h3 class="card-title">Search Results</h3><br>
                <a href="{{ url('registration/show-id-card?registration_number='.$student->registration_number) }}" class="btn btn-primary">Preview ID</a>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                   <table class="table table-bordered">
                     <tr>
                         <td>
                           <div id="crop_wrapper">
                              <img src="{{ asset('uploads/'.$student->image) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'">
                              <div id="crop_div">
                              </div>
                            </div>
                         </td>
                         <td>
                           <form method="post" action="{{ url('registration/crop-student-image') }}" onsubmit="return crop();">
                              @csrf
                              <input type="hidden" value="" id="top" name="top">
                              <input type="hidden" value="" id="left" name="left">
                              <input type="hidden" value="" id="right" name="right">
                              <input type="hidden" value="" id="bottom" name="bottom">
                              <input type="hidden" value="{{$student->image}}" name="image">
                              <input type="submit" name="crop_image" value="Crop Image">
                            </form>
                         </td>
                     </tr>
                     <tr>
                       <td>
                         <div id="ss-my-camera"></div>
                         <input type=button value="Configure" onClick="configure()">
                         <input type=button value="Take Snapshot" onClick="take_snapshot()">
                         <input type=button value="Save Snapshot" onClick="saveSnap({{$student->id}})">
                       </td>
                       <td>
                         <div id="ss-camera-results"></div>
                       </td>
                     </tr>
                     <tr>
                       <td>
                         <div id="signature" style=''>
                           <canvas id="signature-pad" class="signature-pad" width="300px" height="200px"></canvas>
                          </div><br/>
                          <input type='button' id='click' value='preview'><br/>
                          <textarea id='output'></textarea><br/>
                       </td>
                       <td>
                         <img src='' id='sign_prev' style='display: none;' />
                       </td>
                     </tr>
                     <tr>
                       <td>
                         <table border=1 cellpadding="0">
                           <tr><td>   
                             <OBJECT classid=clsid:69A40DA3-4D42-11D0-86B0-0000C025864A height=75
                                    id=SigPlus1 name=SigPlus1
                                    style="HEIGHT: 180px; WIDTH: 320px; LEFT: 0px; TOP: 0px; 
                                    VIEWASTEXT>
                          <PARAM NAME="_Version" VALUE="131095">
                          <PARAM NAME="_ExtentX" VALUE="4842">
                          <PARAM NAME="_ExtentY" VALUE="1323">
                          <PARAM NAME="_StockProps" VALUE="0">
                                    </OBJECT>
                           </td></tr>
                        </table>
                        

                        <FORM id=FORM1 method=get name=FORM1>

                        <p>
                        <INPUT id=SignBtn name=SignBtn type=button value=Sign onclick=OnSign()>&nbsp;&nbsp;&nbsp;&nbsp;

                        <INPUT id=button1 name=ClearBtn type=button value=Clear onclick=OnClear()>&nbsp;&nbsp;&nbsp;&nbsp

                        <INPUT id=button2 name=Cancel type=button value=Cancel onclick=OnCancel()>&nbsp;&nbsp;&nbsp;&nbsp;

                        <INPUT id=submit1 name=Save type=submit value=Save onclick=OnSave()>&nbsp;&nbsp;&nbsp;&nbsp;
                        </p>

                        </FORM>

                        <SCRIPT LANGUAGE=Javascript>
<!--

                          function OnClear() {
                             SigPlus1.ClearTablet(); //Clears the signature, in case of error or mistake
                          }

                          function OnCancel() {
                             SigPlus1.TabletState = 0; //Turns tablet off
                          }

                          function OnSign() {
                          SigPlus1.TabletState = 1; //Turns tablet on
                          }



                          function OnSave() {

                          SigPlus1.TabletState = 0; //Turns tablet off
                          SigPlus1.SigCompressionMode = 1; //Compresses the signature at a 2.5 to 1 ratio, making it smaller...to display the signature again later, you WILL HAVE TO set the SigCompressionMode of the new SigPlus object = 1, also

                          alert("The signature you have taken is the following data: " + SigPlus1.SigString);
                          //The signature is now taken, and you may access it using the SigString property of SigPlus. This SigString is the actual signature, in ASCII format. You may pass this string value like you would any other String. To display the signature again, you simply pass this String back to the SigString property of SigPlus (BE SURE TO SET SigCompressionMode=1 PRIOR TO REASSIGNING THE SigString)

                          }

                          //-->
                          </SCRIPT>
                       </td>
                     </tr>
                     <tr>
                         <td>First name:</td>
                         <td>{{ $student->first_name }}</td>
                     </tr>
                     <tr>
                         <td>Middle name:</td>
                         <td>{{ $student->middle_name }}</td>
                     </tr>
                     <tr>
                         <td>Surname:</td>
                         <td>{{ $student->surname }}</td>
                     </tr>
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
