<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@if(isset($title)) {{ $title }} | @endif {{ Config::get('constants.SITE_NAME') }}</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- JQVMap -->
  <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
  <!-- summernote -->
  <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">

  <link rel="stylesheet" type="text/css" 
     href="{{ asset('css/toastr.min.css') }}">
  <link rel="stylesheet" type="text/css" 
     href="{{ asset('css/foundation-datepicker.min.css') }}">
  <link rel="stylesheet" type="text/css" 
     href="{{ asset('css/select2.min.css') }}">
  <link rel="stylesheet" type="text/css" 
     href="{{ asset('css/datatables.min.css') }}">
  <link href="https://unpkg.com/cropperjs/dist/cropper.css" rel="stylesheet"/>
  <!-- Custom style -->
  <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css?version='.config('constants.VERSION')) }}">
</head>
<body class="hold-transition sidebar-mini layout-fixed">


@yield('content')

<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- ChartJS -->
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<!-- Sparkline -->
<script src="{{ asset('plugins/sparklines/sparkline.js') }}"></script>
<!-- JQVMap -->
<script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('dist/js/demo.js') }}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="{{ asset('dist/js/pages/dashboard.js') }}"></script>
<script src="{{ asset('js/toastr.min.js') }}"></script>
<script src="{{ asset('js/foundation-datepicker.min.js') }}"></script>
<script src="{{ asset('js/datatables.min.js') }}"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/webcam.min.js') }}"></script>
<script src="{{ asset('js/signature_pad.umd.js') }}"></script>
<script src="{{ asset('js/SigWebTablet.js') }}"></script>
<script src="https://unpkg.com/dropzone"></script>
<script src="https://unpkg.com/cropperjs"></script>
<!-- Custom script -->
<script src="{{ asset('js/script.js?version='.config('constants.VERSION')) }}"></script>

<script>
$('.assign-table').DataTable({
  "paging": false
});
</script>

<script>
$("#check-all-btn").click(function(){
  $('.assign-checkbox').each(function() { 
		this.checked = true; 
	});
});

$("#uncheck-all-btn").click(function(){
  $('.assign-checkbox').each(function() { 
		this.checked = false; 
	});
});
</script>

<script>
// Nacte Registration Number
$('.ss-form-processing-nacte-reg-number').submit(function(e){

    e.preventDefault();
    var resultsContainer = $(e.target).data('results-container');
    var submitText = $(e.target).find('button[type=submit]').text();
    $(e.target).find('button[type=submit]').text('Processing...');
    $(e.target).find('button[type=submit]').addClass('disabled');

    $.ajax({
        url:'/application/update-nacte-reg-no/'+$(e.target).find('input[name=nacte_reg_no]').val().replace(/\//g,'-'),
        method:'GET',
     }).done(function(data,success){

      console.log(data);
         
     });
});
</script>

<!-- <script>
  $('#ss-reset-control-number').on('click',function(e){
    $.ajax({
      url:'/application/delete-applicant-invoice',
      method:'POST',
      data:{
         _token:$(e.target).data('token'),
         applicant_id:$(e.target).data('applicant-id')
      }
    }).done(function(data,success){
        window.location.reload();
        toastr.options =
              {
                "closeButton" : true,
                "progressBar" : true
              }
              toastr.success("Control number reset successfully");
    });

});
</script> -->

<script>
  $('#unit-categories').on('change',function(e){

    if ($(e.target).val() == 2 || $(e.target).val() == 1) {

      $("#parent_input").empty();

      $.ajax({
      method:'POST',
      url:$(e.target).data('source-url'),
      data:{
        _token:$(e.target).data('token'),
        campus_id: $('#campus_id').val(),
        unit_category_id:$(e.target).val()
      }      
      }).done(function(data, status){
          if (status == "success") {

            if ('campus' in data) {

              $("#parents").remove();
              $("#parent_input").append('<input type="text" class="form-control" readonly value="'+data.campus.name+'"></input>');
              $("#parent_input").append('<input type="hidden" name="parent_id" value="'+data.campus.id+'"></input>');
              
            } else if('faculties' in data) {

              var element = '<option value="">Select Faculty</option>';
              for(var i=0; i<data.faculties.length; i++){
                element += '<option value="'+data.faculties[i].id+'">'+data.faculties[i].name+'</option>';
              }
              $($(e.target).data('target')).html(element);

            }
      
          }
      });

    }  else if ($(e.target).val() == 4) {

      $.ajax({
      method:'POST',
      url:$(e.target).data('source-url'),
      data:{
        _token:$(e.target).data('token'),
        campus_id: $('#campus_id').val(),
        unit_category_id:$(e.target).val()
      }      
      }).done(function(data, status){
          if (status == "success") {

            var element = '<option value="">Select Department</option>';
            for(var i=0; i<data.departments.length; i++){
              element += '<option value="'+data.departments[i].id+'">'+data.departments[i].name+'</option>';
            }
            $($(e.target).data('target')).html(element);
      
          }
      });


    } 


    // $.ajax({
    // method:'POST',
    // url:$(e.target).data('source-url'),
    // data:{
    //   _token:$(e.target).data('token'),
    //   unit_category_id:$(e.target).val()
    // }      
    // }).done(function(data, status){
    //     if(status == 'success'){


    //       if ($(e.target).val() == 1) {

    //         var element = '<option value="">Select Office</option>';
    //         for(var i=0; i<data.all_departments.length; i++){
    //           element += '<option value="'+data.all_departments[i].id+'">'+data.all_departments[i].name+'</option>';
    //         }
    //         $($(e.target).data('target')).html(element);

    //       } else if ($(e.target).val() == 2) {

    //         var element = '<option value="">Select Department</option>';
    //         for(var i=0; i<data.all_departments.length; i++){
    //           element += '<option value="'+data.all_departments[i].id+'">'+data.all_departments[i].name+'</option>';
    //         }
    //         $($(e.target).data('target')).html(element);

    //       } else if ($(e.target).val() == 3) {

    //         var element = '<option value="">Select Faculty</option>';
    //         for(var i=0; i<data.all_departments.length; i++){
    //           element += '<option value="'+data.all_departments[i].id+'">'+data.all_departments[i].name+'</option>';
    //         }
    //         $($(e.target).data('target')).html(element);

    //       } else if ($(e.target).val() == 4) {

    //         var element = '<option value="">Select Unit</option>';
    //         for(var i=0; i<data.all_departments.length; i++){
    //           element += '<option value="'+data.all_departments[i].id+'">'+data.all_departments[i].name+'</option>';
    //         }
    //         $($(e.target).data('target')).html(element);

    //       }

           
    //     } 
    // });

  });
</script>

<script>
  @if(session()->has('message'))
  toastr.options =
  {
    "closeButton" : true,
    "progressBar" : true
  }
        toastr.success("{{ session('message') }}");
  @endif

  @if($errors->all())
  toastr.options =
  {
    "closeButton" : true,
    "progressBar" : true
  } 
        @if(is_iterable($errors->all()))
        toastr.error("{{ implode('\n',$errors->all()) }}");
        @else
        toastr.error("{{ $errors->all() }}");
        @endif
  @endif

  @if(session()->has('status'))
  toastr.options =
  {
    "closeButton" : true,
    "progressBar" : true
  }
        toastr.info("{{ session('status') }}");
  @endif

  @if(session()->has('error'))
  toastr.options =
  {
    "closeButton" : true,
    "progressBar" : true
  }
        toastr.error("{{ session('error') }}");
  @endif

  @if(session()->has('info'))
  toastr.options =
  {
    "closeButton" : true,
    "progressBar" : true
  }
        toastr.info("{{ session('info') }}");
  @endif

  @if(session()->has('warning'))
  toastr.options =
  {
    "closeButton" : true,
    "progressBar" : true
  }
        toastr.warning("{{ session('warning') }}");
  @endif
</script>
</body>
</html>

