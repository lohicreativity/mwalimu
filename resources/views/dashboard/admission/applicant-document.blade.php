@if($request->get('name') == 'passport_picture')
    @if(explode('.',$applicant->passport_picture)[1] == 'pdf')
        <iframe
        src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->passport_picture) }}#toolbar=0&scrollbar=0"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->passport_picture) }}" height="600px" width="600px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'birth_certificate')
    @if(explode('.',$applicant->birth_certificate)[1] == 'pdf')
        <iframe
        src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->birth_certificate) }}#toolbar=0&scrollbar=0"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->birth_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif 
@endif

@if($request->get('name') == 'o_level_certificate')
    @if(explode('.',$applicant->o_level_certificate)[1] == 'pdf')
        <iframe
        src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->o_level_certificate) }}#toolbar=0&scrollbar=0"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else 
    <img src="{{ asset('uploads/'.$applicant->o_level_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'a_level_certificate')
    @if(explode('.',$applicant->a_level_certificate)[1] == 'pdf')
        <iframe
        src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->a_level_certificate) }}#toolbar=0&scrollbar=0"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else 
    <img src="{{ asset('uploads/'.$applicant->a_level_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'diploma_certificate')
    @if(explode('.',$applicant->diploma_certificate)[1] == 'pdf')
        <iframe
        src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->diploma_certificate) }}#toolbar=0&scrollbar=0"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->diploma_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif