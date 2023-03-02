@if($applicant->passport_picture)
    <iframe
    src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->passport_picture) }}#toolbar=0&scrollbar=0"
    frameBorder="0"
    scrolling="auto"
    height="400px"
    width="100%">
    </iframe>
@endif