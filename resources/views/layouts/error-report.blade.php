@php
   $status = '';
@endphp
@if($errors->all() != null || session()->get('error_messages'))
 <div class="alert alert-danger d-flex align-items-center alert-dismissible ss-messages-box" role="alert">
    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    @foreach($errors->all() as $error)
      <p class="ss-error-message">{{ $error }}</p>
    @endforeach
    @if(session()->get('error_messages') != null)
      @foreach(session()->get('error_messages') as $message)
      <p class="ss-error-message">{{ $message }}</p>
      @endforeach
    @endif
 </div><!-- end of ss-messages_box -->
 @endif

 @if(session()->get('success_messages'))
  <div class="alert alert-success d-flex align-items-center alert-dismissible ss-messages-box" role="alert">
    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    @foreach(session()->get('success_messages') as $message)
      <p class="ss-success-message"> {{ $message }}</p>
    @endforeach
 </div><!-- end of ss-messages_box -->
 @endif