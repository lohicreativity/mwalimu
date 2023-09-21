@if($request->get('name') == 'passport_picture')
    @if(explode('.',$applicant->passport_picture)[1] == 'pdf')
        <iframe
        src="{{ asset('uploads/'.$applicant->passport_picture) }}"
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
        src="{{ asset('uploads/'.$applicant->birth_certificate) }}"
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
        src="{{ asset('uploads/'.$applicant->o_level_certificate) }}"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else 
    <img src="{{ asset('uploads/'.$applicant->o_level_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'basic_certificate')
    @if(explode('.',$applicant->nacte_reg_no)[1] == 'pdf')
        <iframe
        src="{{ asset('uploads/'.$applicant->nacte_reg_no) }}"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else 
    <img src="{{ asset('uploads/'.$applicant->nacte_reg_no) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'a_level_certificate')
    @if(explode('.',$applicant->a_level_certificate)[1] == 'pdf')
        <iframe
        src="{{ asset('uploads/'.$applicant->a_level_certificate) }}"
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
        src="{{ asset('uploads/'.$applicant->diploma_certificate) }}"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->diploma_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'teacher_diploma_certificate')
    @if(explode('.',$applicant->teacher_diploma_certificate)[1] == 'pdf')
        <iframe
        src="{{ asset('uploads/'.$applicant->teacher_diploma_certificate) }}"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->teacher_diploma_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'veta_certificate')
    @if(explode('.',$applicant->veta_certificate)[1] == 'pdf')
        <iframe
        src="{{ asset('uploads/'.$applicant->veta_certificate) }}"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->veta_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'degree_transcript')
    @if(explode('.',$applicant->degree_transcript)[1] == 'pdf')
        <iframe
        src="{{ asset('uploads/'.$applicant->degree_transcript) }}"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->degree_transcript) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'degree_certificate')
    @if(explode('.',$applicant->degree_certificate)[1] == 'pdf')
        <iframe
        src="{{ asset('uploads/'.$applicant->degree_certificate) }}"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->degree_certificate) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif
@endif

@if($request->get('name') == 'insurance')
    @if(explode('.',$applicant->insurances[0]->card)[1] == 'pdf')
        <iframe
        src="{{ asset('uploads/'.$applicant->insurances[0]->card) }}"
        frameBorder="0"
        scrolling="auto"
        height="100%"
        width="100%">
        </iframe>
    @else
    <img src="{{ asset('uploads/'.$applicant->insurances[0]->card) }}" height="1200px" width="1200px" alt="" style="display: block; margin-left: auto; margin-right: auto;">
    @endif 
@endif