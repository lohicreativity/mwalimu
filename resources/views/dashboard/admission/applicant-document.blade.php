@if($applicant->passport_picture)
    <iframe
    src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->passport_picture) }}#toolbar=0&scrollbar=0"
    frameBorder="0"
    scrolling="auto"
    height="400px"
    width="100%">
    </iframe>
@endif

@if($request->get('name') == 'birth_certificate')
    <iframe
    src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->birth_certificate) }}#toolbar=0&scrollbar=0"
    frameBorder="0"
    scrolling="auto"
    height="100%"
    width="100%">
    </iframe>
@endif

@if($applicant->o_level_certificate)
    <iframe
    src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->o_level_certificate) }}#toolbar=0&scrollbar=0"
    frameBorder="0"
    scrolling="auto"
    height="100%"
    width="100%">
    </iframe>
@endif

@if($applicant->diploma_certificate)
    <iframe
    src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->diploma_certificate) }}#toolbar=0&scrollbar=0"
    frameBorder="0"
    scrolling="auto"
    height="400px"
    width="100%">
    </iframe>
@endif