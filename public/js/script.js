
 /* Project: RahisiBuy Online Shopping Platform
 * Author: Amani J. Ghachocha
 * Version: 1.0
 */
var host = "http://localhost:8000";

$('document').ready(function(){
    // Apply margin 
    applyContentMargin('.ss-margin');

    // Apply margin 
    applyContentPadding('.ss-padding');

    // Hide part of a paragraph and show some read more link
    hideParagraph('.ss-content-summary');

    // Collapse paragraph with read more by default
    collapseReadMoreDefault(".ss-paragraph-read-more");

    // Fix content height
    fixContentHeight('.ss-fixed-min-height');

    $('#ss-submit-selected-applicants').DataTable();

    $('#ss-transfers').DataTable();

    $('.ss-paginated-table').DataTable();

    $('.ss-admission-officer-table').DataTable();


    // Pop signup modal
    // if(localStorage.getItem('signup-modal-set') != '1'){
    //     $('#ss-signup-popup-modal').modal('show');
    //     localStorage.setItem('signup-modal-set','1');
    // }
    // localStorage.removeItem('signup-modal-set');
    
    // Prevent page shift on modal firing
    $('.modal').on('show.bs.modal', function () {
      if ($(document).height() > $(window).height()) {
        // no-scroll
        $('body').addClass("modal-open-noscroll");
      }
      else { 
        $('body').removeClass("modal-open-noscroll");
      }
    });

    $('.modal').on('hide.bs.modal', function () {
        $('body').removeClass("modal-open-noscroll");
    })
    $('.modal').on('hide.bs.modal', function () {
        $('body').removeClass("modal-open-noscroll");
    });

    // Enable tooltip
    $('.ss-tooltip').tooltip();

    // Enable dropdown
    $('.dropdown-toggle').dropdown();
    

    // // Initialize summer note
    //  $('.ss-summernote').summernote({
    //     height: 300,                 // set editor height
    //     minHeight: null,             // set minimum height of editor
    //     maxHeight: null,             // set maximum height of editor
    //     focus: true                  // set focus to editable area after initializing summernote
    //   });
    $('#ss-card-nhif-form').css('display','none');
    $('#ss-card-other-form').css('display','none');
    $('#ss-card-none-form').css('display','none');
    
    // // Initialize tinymce
    // tinymce.init({
    //  selector: '.ss-textarea',  
    //  auto_focus: 'element1',
    //  branding: false
    // });
    $('#ss-card-nhif').click(function(e){
         $(e.target.value).css('display','block');
         $('#ss-card-other-form').css('display','none');
         $('#ss-card-none-form').css('display','none');
    });

    $('#ss-card-other').click(function(e){
         $(e.target.value).css('display','block');
         $('#ss-card-nhif-form').css('display','none');
         $('#ss-card-none-form').css('display','none');
    });

    $('#ss-card-none').click(function(e){
         $(e.target.value).css('display','block');
         $('#ss-card-other-form').css('display','none');
         $('#ss-card-nhif-form').css('display','none');
    });

    $('.ss-select-search, .ss-select-search-lg').select2();   

    $('.ss-select-tags').select2(); 

    //  $('.ss-image-zoomer').lightzoom({
    //    glassSize   : 275,
    //    zoomPower   : 1000
    //  });

     // Enable collapse
$('#your-parent .collapse').on('show.bs.collapse', function (e) {
    var actives = $('#your-parent').find('.in, .collapsing');
    actives.each( function (index, element) {
        $(element).collapse('hide');
    });
});



    // Initialize scrollIT
    // $(function(){
    //   $.scrollIt();
    // });

    // Initialize date picker
     $(function(){
        $('.ss-datepicker').fdatepicker({
          initialDate: '22-06-1989',
          format: 'dd-mm-yyyy',
          disableDblClickSelection: true,
        });
     });

      // Initialize time picker
     $(function(){
      $('.ss-timepicker').fdatepicker({
        format: 'dd-mm-yyyy hh:ii',
        disableDblClickSelection: true,
        language: 'vi',
        pickTime: true
      });
    });
     
     // Initialize phone number input
     var inputs = document.querySelectorAll(".ss-phone");
     inputs.forEach(function(input){
         window.intlTelInput(input,{
          hiddenInput: "full_phone",
          // initialCountry: "auto",
          // geoIpLookup: function(success, failure) {
          //   $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
          //     var countryCode = (resp && resp.country) ? resp.country : "";
          //     success(countryCode);
          //   });
          // },
          initialCountry: "TZ",
          // preferedCountries: ["US","GB","UK","IN","FR"],
          // separateDialCode: true,
          utilsScript: "../assets/js/utils.js?1562189064761"
        });
     });

      var signaturePad = new SignaturePad(document.getElementById('signature-pad'));

       $('#click').click(function(){
        var data = signaturePad.toDataURL('image/png');
        $('#output').val(data);

        // $.ajax({
        //    url: '/application/upload-signature',
        //    method: 'POST',
        //    data:{
        //       image:data,
        //       student_id:$('#ss-student-id').val()
        //    }
        // }).done(function(data){
           
        // });

        $("#sign_prev").show();
        $("#sign_prev").attr("src",data);
        // Open image in the browser
        //window.open(data);
       });

    //  var input = document.querySelector("#ss-phone");
    //  window.intlTelInput(input,{
    //   hiddenInput: "full_phone",
    //   // initialCountry: "auto",
    //   // geoIpLookup: function(success, failure) {
    //   //   $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
    //   //     var countryCode = (resp && resp.country) ? resp.country : "";
    //   //     success(countryCode);
    //   //   });
    //   // },
    //   initialCountry: "TZ",
    //   // preferedCountries: ["US","GB","UK","IN","FR"],
    //   // separateDialCode: true,
    //   utilsScript: "../assets/js/utils.js?1562189064761"
    // });

     // Initialize phone number input
    //  var input2 = document.querySelector("#ss-company-phone");
    //  window.intlTelInput(input2,{
    //   hiddenInput: "company_full_phone",
    //   initialCountry: "TZ",
    //   // separateDialCode: true,
    //   utilsScript: "../assets/js/utils.js?1562189064761"
    // });

     // Toggle check or radio
     var source = '.ss-toggle-radio input[type=radio], .ss-toggle-check input[type=checkbox]';
     toggleRow(source);
     $(source).click(function(e){
          toggleRow(source);
     });

     // Highlight checkbox when clicked
     var checkbox = '.ss-checkbox-highlighted input[type=checkbox]';
     highlight(checkbox);
     $(checkbox).click(function(e){
         if($(e.target).is(':checked')){
            $(e.target).parent().css('background','#fcf8e3');
         }else{
            $(e.target).parent().css('background','#e68523'); 
            if($($(e.target).parent().data('allowed-trigger')+' input[type=checkbox]').is(':checked')){
                if($(e.target).parent().data('trigger') != null){
                   $($(e.target).parent().data('trigger')).trigger('click');
                }
            }  
         }
     });

     var unToggleSource = '.ss-untoggle-radio input[type=radio], .ss-untoggle-check input[type=checkbox]';
     unToggleRow(unToggleSource);
     $(unToggleSource).click(function(e){
          unToggleRow(unToggleSource);
     });

     // Dependent date picker
     // implementation of disabled form fields
      // var nowTemp = new Date();
      // var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
      // var checkin = $('#dpd1, .dpd1').fdatepicker({
      //   onRender: function (date) {
      //     return date.valueOf() < now.valueOf() ? 'disabled' : '';
      //   }
      // }).on('changeDate', function (ev) {
      //   if (ev.date.valueOf() > checkout.date.valueOf()) {
      //     var newDate = new Date(ev.date)
      //     newDate.setDate(newDate.getDate() + 1);
      //     checkout.update(newDate);
      //   }
      //   checkin.hide();
      //   $('#dpd2, .dpd2')[0].focus();
      // }).data('datepicker');
      // var checkout = $('#dpd2, .dpd2').fdatepicker({
      //   onRender: function (date) {
      //     return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
      //   }
      // }).on('changeDate', function (ev) {
      //   checkout.hide();
      // }).data('datepicker');

      // $('#ss-right-sidebar').css('position','fixed'); 
      // $('#ss-right-sidebar').css('top','367px'); 
     // $('#ss-right-sidebar').find('.col-md-3').addClass('col-md-offset-6');

});

 function configure(){
    Webcam.set({
       width: 320,
       height: 240,
       image_format: 'jpeg',
       jpeg_quality: 90
    });
    Webcam.attach( '#ss-my-camera' );
 }

function take_snapshot() {
    // play sound effect
    // shutter.play();

    // take snapshot and get image data
    Webcam.snap( function(data_uri) {
       // display results in page
       document.getElementById('ss-camera-results').innerHTML = 
           '<img id="ss-camera-prev" src="'+data_uri+'"/>';
     } );

     Webcam.reset();
 }

function saveSnap(student_id){
   // Get base64 value from <img id='imageprev'> source
   var base64image = document.getElementById("ss-camera-prev").src;

   Webcam.upload( base64image,'/application/upload-camera-img?student_id='+student_id, function(code, text) {
        console.log('Save successfully');
       console.log(text);
   });
   window.location.reload();

}

// Enable collapse
$('#your-parent .collapse').on('show.bs.collapse', function (e) {
    var actives = $('#your-parent').find('.in, .collapsing');
    actives.each( function (index, element) {
        $(element).collapse('hide');
    })
});

$('.ss-file-upload').on('change',function(e){
    // document.getElementById('ss-photo-placeholder').style.display='block';
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById($(e.target).data('preview-target'));
        $('#'+$(e.target).data('preview-target')).css('visibility','visible');
        output.src = reader.result;
    }
    reader.readAsDataURL(e.target.files[0]);
});

// Lock body on modal opening
$(document.body)
.on('show.bs.modal', function () {
    if (this.clientHeight <= window.innerHeight) {
        return;
    }
    // Get scrollbar width
    var scrollbarWidth = getScrollBarWidth()
    if (scrollbarWidth) {
        $(document.body).css('padding-right', scrollbarWidth);
        $('.navbar-fixed-top').css('padding-right', scrollbarWidth);    
    }
})
.on('hidden.bs.modal', function () {
    $(document.body).css('padding-right', 0);
    $('.navbar-fixed-top').css('padding-right', 0);
});

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function formatMoney(number, decPlaces, decSep, thouSep) {
  decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
  decSep = typeof decSep === "undefined" ? "." : decSep;
  thouSep = typeof thouSep === "undefined" ? "," : thouSep;
  var sign = number < 0 ? "-" : "";
  var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
  var j = (j = i.length) > 3 ? j % 3 : 0;

  return sign +
    (j ? i.substr(0, j) + thouSep : "") +
    i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
    (decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
}

function emailAlreadyTaken(email,token,target){
  $.ajax({
      url:target,
      method:'POST',
      data:{
        _token:token,
        email:email
      }
  }).done(function(data){
      if(data.user){
         return status = true;
      }else{
         return status = false;
      }
  });
}

function getScrollBarWidth () {
    var inner = document.createElement('p');
    inner.style.width = "100%";
    inner.style.height = "200px";

    var outer = document.createElement('div');
    outer.style.position = "absolute";
    outer.style.top = "0px";
    outer.style.left = "0px";
    outer.style.visibility = "hidden";
    outer.style.width = "200px";
    outer.style.height = "150px";
    outer.style.overflow = "hidden";
    outer.appendChild (inner);

    document.body.appendChild (outer);
    var w1 = inner.offsetWidth;
    outer.style.overflow = 'scroll';
    var w2 = inner.offsetWidth;
    if (w1 == w2) w2 = outer.clientWidth;

    document.body.removeChild (outer);
    return (w1 - w2);
};

function hideParagraph(targetClass){
  // Collapse content summary and display read more

     $(targetClass).each(function(){
        var length = $(this).data('character-length');
        //var target = $(this).data('read-more-target');
        //var link = '<a class="ss-read-more-link-block ss-read-more'+$(this).data('read-more-position')+'" href="'+target+'">Read More <span class="glyphicon glyphicon-circle-arrow-right"></span></a>';
        var content = $(this).text();
        var buffer = "";
        //console.log($(this).text());
        if(content.length > length){
          for(var i = 0; i < length; i++){
            buffer += content[i];
          }
          buffer += "...";
          $(this).text(buffer);
          //$(this).append(link);

        }
        
     });
}

// Action on checkbox click
$('.ss-checkbox input[type=checkbox]').click(function(e){
   $(e.target).css('background','steelblue');
});


// Apply content margin 
function applyContentMargin(target){
   $(target).each(function(){
      var top = $(this).attr('data-margin-top');
      var bottom = $(this).attr('data-margin-bottom');
      var left = $(this).attr('data-margin-left');
      var right = $(this).attr('data-margin-right');
      $(this).css('margin-top',top+'px');
      $(this).css('margin-bottom',bottom+'px');
      $(this).css('margin-left',left+'px');
      $(this).css('margin-right',right+'px');
   });
}

// Apply content padding 
function applyContentPadding(target){
   $(target).each(function(){
      var top = $(this).attr('data-padding-top');
      var bottom = $(this).attr('data-padding-bottom');
      var left = $(this).attr('data-padding-left');
      var right = $(this).attr('data-padding-right');
      $(this).css('padding-top',top+'px');
      $(this).css('padding-bottom',bottom+'px');
      $(this).css('padding-left',left+'px');
      $(this).css('padding-right',right+'px');
   });
}

// Toggle link highlight on hover
$('.ss-nav-link').hover(function(e){
   $(e.target).parent().children('.ss-link-highlight').stop(true).slideToggle('0.1');
   // if($(window).width() > 768){
   //    $(e.target).parent().children('.ss-link-highlight').stop(true).animate({width:'toggle'},350);
   // }else if($(window).width() > 600 && $(window).width() < 769){
   //    $(e.target).parent().children('.ss-link-highlight').stop(true).animate({width:'toggle'},700);
   // }else if($(window).width() > 481 && $(window).width() < 599){
   //    $(e.target).parent().children('.ss-link-highlight').stop(true).animate({width:'toggle'},500);
   // }else{
   //    $(e.target).parent().children('.ss-link-highlight').stop(true).animate({width:'toggle'},480);
   // }
});

// Trigger upload button
$('.ss-trigger-upload').click(function(e){
    var upload_field = $(e.target).data('upload-field');
    $(upload_field).trigger('click');
});

// Display upload field image
$(".ss-upload-field").on('change',function(e) {
      console.log($(e.target).data('thumb-target'));
      var hasFileAPI = (window.File && window.FileReader);
      if(hasFileAPI){
          var files = e.target.files;
          var file = e.target.files[0];
          var name = file.name;
          var size = file.size;
          var type = file.type;
          var thumbTarget = $(e.target).data('thumb-target');

          var reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = function(evt){
             if(file.type.match('image.*')){
               var image = new Image();
               image.height = 230;
               image.width = 230;
               image.src = evt.target.result;
               $(thumbTarget).empty();
               $(thumbTarget).append(image);
             }
          } 
        }
});

$('.ss-trigger-click').click(function(e){
   $($(e.target).data('target-link')).trigger('click');
});


// Prompt action confirmation before proceeding
$('.ss-stop-notice').click(function(e){
    e.preventDefault();
    var notice = $(e.target).data('notice');
    var overlayContent = $(e.target).data('overlay-content');
    $('#ss-notice-container #ss-notice-text').html(notice);
    setOverlay(overlayContent);
});

// Trigger confirmation alert
$('.ss-trigger-confirm').click(function(e){
    e.preventDefault();
    var trigger_link = $(e.target).data('confirm-trigger-link');
    var target_link = $(e.target).data('target-link');
    var warning = $(e.target).data('warning');
    if(warning != null){
       $('#ss-confirmation-container #ss-confirmation-text').html(warning);
    }
    $('#ss-confirmation-container .ss-proceed-action').attr('href',target_link);
    $(trigger_link).trigger('click');
});

// Trigger confirm alert by sliding sown
$('.ss-trigger-confirm-slide').click(function(e){
   //e.preventDefault();
   var target = $(e.target).data('target');
   if(!$(target).is(':visible')){
     $(target).slideDown();
   }
});




$('.ss-add-to-cart-').keypress(function(e){
     e.preventDefault();
     var callAJAX = $.ajax({
        url: $(e.target).attr('href'),
        method: 'GET',
        beforeSend: function(){
           $(e.target).css('background','../images/system/prime_loader.gif');
        },
        statusCode: {
          404: function(){
            console.log( "Page not found" );
          },
          405: function() {
            console.log("Technical problem occured");
          },
          500: function() {
            console.log("Internal Server Error");
          }
        }
    });

    callAJAX.done(function(data, status){
        //alert($.parseJSON(data.success_messages));
        //alert(data.success_messages[0]);
        console.log(data);
        var element = '';
        if(status == 'success'){
           
        }
    });
});



// Load regions
$('#ss-select-countries, .ss-select-countries').on('change',function(e){
    $.ajax({
      method:'POST',
      url:$(e.target).data('source-url'),
      data:{
        _token:$(e.target).data('token'),
        country_id:$(e.target).val()
      }      
    }).done(function(data, status){
        if(status == 'success'){
           var element = '<option value="">Select Region</option>';
           for(var i=0; i<data.regions.length; i++){
             element += '<option value="'+data.regions[i].id+'">'+data.regions[i].name+'</option>';
           }
           $($(e.target).data('target')).html(element);
        }
    });
});

// Load districts
$('#ss-select-regions, .ss-select-regions').on('change',function(e){
    $.ajax({
      method:'POST',
      url:$(e.target).data('source-url'),
      data:{
        _token:$(e.target).data('token'),
        region_id:$(e.target).val()
      } 
    }).done(function(data, status){
        if(status == 'success'){
           var element = '<option value="">Select District</option>';
           for(var i=0; i<data.districts.length; i++){
             element += '<option value="'+data.districts[i].id+'">'+data.districts[i].name+'</option>';
           }
           $($(e.target).data('target')).html(element);
        }
    });
});

// Load wards
$('#ss-select-districts, .ss-select-districts').on('change',function(e){
    $.ajax({
      method:'POST',
      url:$(e.target).data('source-url'),
      data:{
        _token:$(e.target).data('token'),
        district_id:$(e.target).val()
      } 
    }).done(function(data, status){
        if(status == 'success'){
           var element = '<option value="">Select Ward</option>';
           for(var i=0; i<data.wards.length; i++){
             element += '<option value="'+data.wards[i].id+'">'+data.wards[i].name+'</option>';
           }
           $($(e.target).data('target')).html(element);
        }
    });
});

// Auto fill final exam
$('select[name=course_work]').on('change',function(e){
     var value = $(e.target).val();
     $($(e.target).data('target')).val((100-parseInt(value)));
});

// Auto-fill examination policy
$('.ss-course-work-min-mark').on('keyup',function(e){
    $($(e.target).data('target')).val((100-parseInt($(e.target).val())));
});

$('.ss-course-work-percentage-pass').on('keyup',function(e){
    var percentage = parseInt($(e.target).val())*parseInt($($(e.target).data('from')).val())/100;
    $($(e.target).data('target')).val(percentage);
});

$('.ss-final-percentage-pass').on('keyup',function(e){
    var percentage = parseInt($(e.target).val())*parseInt($($(e.target).data('from')).val())/100;
    $($(e.target).data('target')).val(percentage);
});

// Select NTA Level
$('.ss-select-nta-level').on('change',function(e){
    $.ajax({
       'method':'POST',
       'url':$(e.target).data('source-url'),
       'data':{
          '_token':$(e.target).data('token'),
          'nta_level_id':$(e.target).val()
       }
    }).done(function(data, success){
        if(data.status == 'success'){
           $($(e.target).data('min-target')).val(data.nta_level.min_duration);
           $($(e.target).data('max-target')).val(data.nta_level.max_duration);
           $($(e.target).data('award-target')).val(data.nta_level.award_id);
        }
    });
});

// Select NTA Level
$('#ss-select-fee-type').on('change',function(e){
    $.ajax({
       'method':'POST',
       'url':$(e.target).data('source-url'),
       'data':{
          '_token':$(e.target).data('token'),
          'fee_type_id':$(e.target).val()
       }
    }).done(function(data, success){
        if(data.status == 'success'){
           if(data.type.name.includes('Appeal')){
              $($(e.target).data('target')).val($('#ss-subjects-number').val()*data.type.fee_items[0].fee_amounts[0].amount_in_tzs);
           }else{
             $($(e.target).data('target')).val(data.type.fee_items[0].fee_amounts[0].amount_in_tzs);
           }
        }
    });
});

// Select module
$('.ss-select-tags').on('change',function(e){
    $.ajax({
       'method':'POST',
       'url':$(e.target).data('source-url'),
       'data':{
          '_token':$(e.target).data('token'),
          'module_id':$(e.target).val()
       }
    }).done(function(data, success){
        if(data.status == 'success'){
           var code = data.module.code.replace(" ","");
           var semester_id = code.substring(5,6);
           var year = 1;
           if(code.substring(4,5) == '4' || code.substring(4,5) == '5' || code.substring(4,5) == '6'){
              year = 1;
           }else if(code.substring(4,6) == '71'){
              year = 1;
              semester_id = 1;
           }else if(code.substring(4,6) == '72'){
              year = 1;
              semester_id = 2;
           }else if(code.substring(4,6) == '73'){
              year = 2;
              semester_id = 1;
           }else if(code.substring(4,6) == '74'){
              year = 2;
              semester_id = 2;
           }else if(code.substring(4,5) == '8'){
              year = 3;
           }

           $($(e.target).data('year-target')).val(year);
           $($(e.target).data('semester-target')).val(semester_id);
           
           if(data.module.course_work_based == '0'){
              $($(e.target).data('cw-min-mark-target')).val(0);
              $($(e.target).data('cw-percentage-pass-target')).val(0);
              $($(e.target).data('cw-pass-score-target')).val(0);
              $($(e.target).data('final-min-mark-target')).val(100);

              $($(e.target).data('cw-min-mark-target')).attr('readonly','readonly');
              $($(e.target).data('cw-percentage-pass-target')).attr('readonly','readonly');
              $($(e.target).data('cw-pass-score-target')).attr('readonly','readonly');
              $($(e.target).data('final-min-mark-target')).attr('readonly','readonly');
           }
        }
    });
});

// Autofill NTA Level by code
$('.ss-autofill-nta').on('keyup',function(e){
    $.ajax({
         method:'POST',
         url:$(e.target).data('source-url'),
         data:{
            _token:$(e.target).data('token'),
            code:$(e.target).val()
         }
    }).done(function(data){
        if(data.status == 'success'){
           $($(e.target).data('target')).val(data.nta_level.id);
        }else{
           toastr.options =
           {
              "closeButton" : true,
              "progressBar" : true
           }
           toastr.error("Invalid module code entered.");
        }
    });
});

// Autofill module select
$('#ss-select-program-modules').on('change',function(e){
    $.ajax({
      method:'POST',
      url:$(e.target).data('source-url'),
      data:{
        _token:$(e.target).data('token'),
        'campus_program_id':$(e.target).val(),
        'study_academic_year_id':$(e.target).data('academic-year-id')
      }
    }).done(function(data){
        var element = '';
        for(var i = 0; i < data.modules.length; i++){
           element += '<option value="'+data.modules[i].id+'">'+data.modules[i].name+' - '+data.modules[i].code+'</option>';
        }
        $($(e.target).data('target')).html(element);
    });
});

// Autofill module assignment select
$('#ss-select-program-module-assignments').on('change',function(e){
    $.ajax({
      method:'POST',
      url:$(e.target).data('source-url'),
      data:{
        _token:$(e.target).data('token'),
        'campus_program_id':$(e.target).val(),
        'study_academic_year_id':$(e.target).data('academic-year-id')
      }
    }).done(function(data){
        var element = '';
        for(var i = 0; i < data.modules.length; i++){
           element += '<option value="'+data.modules[i].id+'">'+data.modules[i].module.name+' - '+data.modules[i].module.code+'</option>';
        }
        $($(e.target).data('target')).html(element);
    });
});

// Display form processing necta
$('.ss-form-processing-necta').submit(function(e){
     e.preventDefault();
     var resultsContainer = $(e.target).data('results-container');
     var submitText = $(e.target).find('button[type=submit]').text();
     var id = $(e.target).attr('id');
     $(e.target).find('button[type=submit]').text('Processing...');
     $(e.target).find('button[type=submit]').addClass('disabled');

     var percentComplete = 100;
     var element = '';
      element += '<p class="ss-bold">Please wait...</p>';
      element += '<div class="progress progress-striped active">';
      element += '<div class="progress-bar progress-bar-warning role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
      element += '</div>';
     if(resultsContainer != null){
         $(resultsContainer).html(element).slideDown();
     }
     if(id == null){
         $(e.target).find('.ss-ajax-messages').html(element).slideDown();
     }else{
         $(e.target).find('#'+id+' .ss-ajax-messages').html(element).slideDown();
     }

     $.ajax({
        url:'/application/fetch-necta-results/'+$(e.target).find('input[name=index_number]').val().replace(/\//g,'-')+'/'+$(e.target).find('input[name=exam_id]').val()+'?applicant_id='+$(e.target).find('input[name=applicant_id]').val(),
        method:'GET',
     }).done(function(data,success){
         if(data.error){
             alert(data.error);
         }else{
          console.log(data);
         $(e.target).find('button[type=submit]').text(submitText);
         $(e.target).find('button[type=submit]').removeClass('disabled');

         var element = '<table class="table table-bordered">';
         element += '<tr><td>Center Name:</td><td>'+data.details.center_name+'</td></tr>';
         element += '<tr><td>Center Number:</td><td>'+data.details.center_number+'</td></tr>';
         element += '<tr><td>First Name:</td><td>'+data.details.first_name+'</td></tr>';
         element += '<tr><td>Middle Name:</td><td>'+data.details.middle_name+'</td></tr>';
         element += '<tr><td>Last Name:</td><td>'+data.details.last_name+'</td></tr>';
         element += '<tr><td>Sex:</td><td>'+data.details.sex+'</td></tr>';
         element += '<tr><td>Index Number:</td><td>'+data.details.index_number+'</td></tr>'
         element += '<tr><td>Division:</td><td>'+data.details.division+'</td></tr>';
         element += '<tr><td>Points:</td><td>'+data.details.points+'</td></tr>';
         for(var i=0; i<data.details.results.length; i++){
            element += '<tr><td>'+data.details.results[i].subject_name+'</td><td>'+data.details.results[i].grade+'</td></tr>'
         }
         element += '</table>';

         $($(e.target).find('input[name=results_container]').val()).html(element);
         $($(e.target).find('input[name=display_modal]').val()).modal('show');
         
         $($(e.target).find('input[name=display_modal]').val()+' input[name=index_number]').val($(e.target).find('input[name=index_number]').val());
         $($(e.target).find('input[name=display_modal]').val()+' input[name=year]').val($(e.target).find('input[name=year]').val());
         $($(e.target).find('input[name=display_modal]').val()+' input[name=exam_id]').val($(e.target).find('input[name=exam_id]').val());
         $($(e.target).find('input[name=display_modal]').val()+' input[name=necta_result_detail_id]').val(data.details.id);
         }
     });
});

// Display form processing necta
$('.ss-form-processing-nacte').submit(function(e){
     e.preventDefault();
     var resultsContainer = $(e.target).data('results-container');
     var submitText = $(e.target).find('button[type=submit]').text();
     var id = $(e.target).attr('id');
     $(e.target).find('button[type=submit]').text('Processing...');
     $(e.target).find('button[type=submit]').addClass('disabled');

     var percentComplete = 100;
     var element = '';
      element += '<p class="ss-bold">Please wait...</p>';
      element += '<div class="progress progress-striped active">';
      element += '<div class="progress-bar progress-bar-warning role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
      element += '</div>';
     if(resultsContainer != null){
         $(resultsContainer).html(element).slideDown();
     }
     if(id == null){
         $(e.target).find('.ss-ajax-messages').html(element).slideDown();
     }else{
         $(e.target).find('#'+id+' .ss-ajax-messages').html(element).slideDown();
     }

     $.ajax({
        url:'/application/fetch-nacte-results/'+$(e.target).find('input[name=avn]').val()+'?applicant_id='+$(e.target).find('input[name=applicant_id]').val(),
        method:'GET',
     }).done(function(data,success){
         if(data.error){
            alert(data.error);
         }else{
         $(e.target).find('button[type=submit]').text(submitText);
         $(e.target).find('button[type=submit]').removeClass('disabled');

         var element = '<table class="table table-bordered">';
         element += '<tr><td>Institution:</td><td>'+data.details.institution+'</td></tr>';
         element += '<tr><td>Programme:</td><td>'+data.details.programme+'</td></tr>'
         element += '<tr><td>First Name:</td><td>'+data.details.firstname+'</td></tr>';
         element += '<tr><td>Middle Name:</td><td>'+data.details.middlename+'</td></tr>';
         element += '<tr><td>Surname:</td><td>'+data.details.surname+'</td></tr>';
         element += '<tr><td>Gender:</td><td>'+data.details.gender+'</td></tr>';
         element += '<tr><td>Birth Date:</td><td>'+data.details.date_birth+'</td></tr>';
         element += '<tr><td>AVN:</td><td>'+data.details.avn+'</td></tr>';
         element += '<tr><td>Graduation Year:</td><td>'+data.details.diploma_graduation_year+'</td></tr>';
         element += '<tr><td>Username:</td><td>'+data.details.username+'</td></tr>';
         element += '<tr><td>Diploma Code:</td><td>'+data.details.diploma_code+'</td></tr>';
         element += '<tr><td>Registration Number:</td><td>'+data.details.registration_number+'</td></tr>';
         element += '<tr><td>Diploma GPA:</td><td>'+data.details.diploma_gpa+'</td></tr>';
         for(var i=0; i<data.details.results.length; i++){
            element += '<tr><td>'+data.details.results[i].subject+'</td><td>'+data.details.results[i].grade+'</td></tr>'
         }
         element += '</table>';

         $($(e.target).find('input[name=results_container]').val()).html(element);
         $($(e.target).find('input[name=display_modal]').val()).modal('show');
         
         $($(e.target).find('input[name=display_modal]').val()+' input[name=nacte_result_detail_id]').val(data.details.id);
         }
     });
});

// Display form processing necta
$('.ss-form-processing-out').submit(function(e){
     e.preventDefault();
     var resultsContainer = $(e.target).data('results-container');
     var submitText = $(e.target).find('button[type=submit]').text();
     var id = $(e.target).attr('id');
     $(e.target).find('button[type=submit]').text('Processing...');
     $(e.target).find('button[type=submit]').addClass('disabled');

     var percentComplete = 100;
     var element = '';
      element += '<p class="ss-bold">Please wait...</p>';
      element += '<div class="progress progress-striped active">';
      element += '<div class="progress-bar progress-bar-warning role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
      element += '</div>';
     if(resultsContainer != null){
         $(resultsContainer).html(element).slideDown();
     }
     if(id == null){
         $(e.target).find('.ss-ajax-messages').html(element).slideDown();
     }else{
         $(e.target).find('#'+id+' .ss-ajax-messages').html(element).slideDown();
     }

     $.ajax({
        url:'/application/fetch-out-results/'+$(e.target).find('input[name=reg_no]').val()+'?applicant_id='+$(e.target).find('input[name=applicant_id]').val(),
        method:'GET',
     }).done(function(data,success){
         if(data.error){
            alert(data.error);
         }else{
         $(e.target).find('button[type=submit]').text(submitText);
         $(e.target).find('button[type=submit]').removeClass('disabled');

         var element = '<table class="table table-bordered">';
         element += '<tr><td>Reg No:</td><td>'+data.details.reg_no+'</td></tr>';
         element += '<tr><td>Index No:</td><td>'+data.details.index_number+'</td></tr>'
         element += '<tr><td>First Name:</td><td>'+data.details.first_name+'</td></tr>';
         element += '<tr><td>Middle Name:</td><td>'+data.details.middle_name+'</td></tr>';
         element += '<tr><td>Surname:</td><td>'+data.details.surname+'</td></tr>';
         element += '<tr><td>Gender:</td><td>'+data.details.gender+'</td></tr>';
         element += '<tr><td>Birth Date:</td><td>'+data.details.birth_date+'</td></tr>';
         element += '<tr><td>Academic Year:</td><td>'+data.details.academic_year+'</td></tr>';
         element += '<tr><td>GPA:</td><td>'+data.details.gpa+'</td></tr>';
         element += '<tr><td>Classification:</td><td>'+data.details.classification+'</td></tr>';
         for(var i=0; i<data.details.results.length; i++){
            element += '<tr><td>'+data.details.results[i].subject_name+'</td><td>'+data.details.results[i].grade+'</td></tr>'
         }
         element += '</table>';

         $($(e.target).find('input[name=results_container]').val()).html(element);
         $($(e.target).find('input[name=display_modal]').val()).modal('show');
         
         $($(e.target).find('input[name=display_modal]').val()+' input[name=out_result_detail_id]').val(data.details.id);
         }
     });
});

// Display form processing necta admin
$('.ss-form-processing-out-admin').submit(function(e){
     e.preventDefault();
     var resultsContainer = $(e.target).data('results-container');
     var submitText = $(e.target).find('button[type=submit]').text();
     var id = $(e.target).attr('id');
     $(e.target).find('button[type=submit]').text('Processing...');
     $(e.target).find('button[type=submit]').addClass('disabled');

     var percentComplete = 100;
     var element = '';
      element += '<p class="ss-bold">Please wait...</p>';
      element += '<div class="progress progress-striped active">';
      element += '<div class="progress-bar progress-bar-warning role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
      element += '</div>';
     if(resultsContainer != null){
         $(resultsContainer).html(element).slideDown();
     }
     if(id == null){
         $(e.target).find('.ss-ajax-messages').html(element).slideDown();
     }else{
         $(e.target).find('#'+id+' .ss-ajax-messages').html(element).slideDown();
     }

     $.ajax({
        url:'/application/fetch-out-results-admin/'+$(e.target).find('input[name=reg_no]').val(),
        method:'GET',
     }).done(function(data,success){
         if(data.error){
            alert(data.error);
         }else{
         $(e.target).find('button[type=submit]').text(submitText);
         $(e.target).find('button[type=submit]').removeClass('disabled');

         var element = '<table class="table table-bordered">';
         element += '<tr><td>Reg No:</td><td>'+data.response.RegNo+'</td></tr>';
         element += '<tr><td>Index No:</td><td>'+data.response.Indexno+'</td></tr>'
         element += '<tr><td>First Name:</td><td>'+data.response.FirstName+'</td></tr>';
         element += '<tr><td>Middle Name:</td><td>'+data.response.MidName+'</td></tr>';
         element += '<tr><td>Surname:</td><td>'+data.response.Surname+'</td></tr>';
         element += '<tr><td>Gender:</td><td>'+data.response.Gender+'</td></tr>';
         element += '<tr><td>Birth Date:</td><td>'+data.response.BirthDate+'</td></tr>';
         element += '<tr><td>Academic Year:</td><td>'+data.response.AcademicYear+'</td></tr>';
         element += '<tr><td>GPA:</td><td>'+data.response.GPA+'</td></tr>';
         element += '<tr><td>Classification:</td><td>'+data.response.Classification+'</td></tr>';
         for(var i=0; i<data.response.Results.Subject.length; i++){
            element += '<tr><td>'+data.response.Results.Subject[i].SubjectName+'</td><td>'+data.response.Results.Subject[i].Grade+'</td></tr>'
         }
         element += '</table>';

         $($(e.target).find('input[name=results_container]').val()).html(element);
         $($(e.target).find('input[name=display_modal]').val()).modal('show');
         
         $($(e.target).find('input[name=display_modal]').val()+' input[name=out_result_detail_id]').val(data.details.id);
         }
     });
});

// Display form processing necta
$('.ss-form-processing-necta-admin').submit(function(e){
     e.preventDefault();
     var resultsContainer = $(e.target).data('results-container');
     var submitText = $(e.target).find('button[type=submit]').text();
     var id = $(e.target).attr('id');
     $(e.target).find('button[type=submit]').text('Processing...');
     $(e.target).find('button[type=submit]').addClass('disabled');

     var percentComplete = 100;
     var element = '';
      element += '<p class="ss-bold">Please wait...</p>';
      element += '<div class="progress progress-striped active">';
      element += '<div class="progress-bar progress-bar-warning role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
      element += '</div>';
     if(resultsContainer != null){
         $(resultsContainer).html(element).slideDown();
     }
     if(id == null){
         $(e.target).find('.ss-ajax-messages').html(element).slideDown();
     }else{
         $(e.target).find('#'+id+' .ss-ajax-messages').html(element).slideDown();
     }

     $.ajax({
        url:'/application/fetch-necta-results-admin/'+$(e.target).find('input[name=index_number]').val().replace(/\//g,'-')+'/'+$(e.target).find('input[name=exam_id]').val(),
        method:'GET',
     }).done(function(data,success){
         if(data.error){
             alert(data.error);
         }else{

          console.log(data);
         $(e.target).find('button[type=submit]').text(submitText);
         $(e.target).find('button[type=submit]').removeClass('disabled');

         var element = '<table class="table table-bordered">';
         element += '<tr><td>Center Name:</td><td>'+data.response.particulars.center_name+'</td></tr>';
         element += '<tr><td>Center Number:</td><td>'+data.response.particulars.center_number+'</td></tr>';
         element += '<tr><td>First Name:</td><td>'+data.response.particulars.first_name+'</td></tr>';
         element += '<tr><td>Middle Name:</td><td>'+data.response.particulars.middle_name+'</td></tr>';
         element += '<tr><td>Last Name:</td><td>'+data.response.particulars.last_name+'</td></tr>';
         element += '<tr><td>Sex:</td><td>'+data.response.particulars.sex+'</td></tr>';
         element += '<tr><td>Index Number:</td><td>'+data.response.particulars.index_number+'</td></tr>'
         element += '<tr><td>Division:</td><td>'+data.response.particulars.division+'</td></tr>';
         element += '<tr><td>Points:</td><td>'+data.response.particulars.points+'</td></tr>';
         for(var i=0; i<data.response.subjects.length; i++){
            element += '<tr><td>'+data.response.subjects[i].subject_name+'</td><td>'+data.response.subjects[i].grade+'</td></tr>'
         }
         element += '</table>';

         $($(e.target).find('input[name=results_container]').val()).html(element);
         $($(e.target).find('input[name=display_modal]').val()).modal('show');
         
         $($(e.target).find('input[name=display_modal]').val()+' input[name=index_number]').val($(e.target).find('input[name=index_number]').val());
         $($(e.target).find('input[name=display_modal]').val()+' input[name=year]').val($(e.target).find('input[name=year]').val());
         $($(e.target).find('input[name=display_modal]').val()+' input[name=exam_id]').val($(e.target).find('input[name=exam_id]').val());
         $($(e.target).find('input[name=display_modal]').val()+' input[name=necta_result_detail_id]').val(data.details.id);
         }
     });
});

// Display form processing necta
$('.ss-form-processing-nacte-admin').submit(function(e){
     e.preventDefault();
     var resultsContainer = $(e.target).data('results-container');
     var submitText = $(e.target).find('button[type=submit]').text();
     var id = $(e.target).attr('id');
     $(e.target).find('button[type=submit]').text('Processing...');
     $(e.target).find('button[type=submit]').addClass('disabled');

     var percentComplete = 100;
     var element = '';
      element += '<p class="ss-bold">Please wait...</p>';
      element += '<div class="progress progress-striped active">';
      element += '<div class="progress-bar progress-bar-warning role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
      element += '</div>';
     if(resultsContainer != null){
         $(resultsContainer).html(element).slideDown();
     }
     if(id == null){
         $(e.target).find('.ss-ajax-messages').html(element).slideDown();
     }else{
         $(e.target).find('#'+id+' .ss-ajax-messages').html(element).slideDown();
     }

     $.ajax({
        url:'/application/fetch-nacte-results-admin/'+$(e.target).find('input[name=avn]').val()+'?applicant_id='+$(e.target).find('input[name=applicant_id]').val(),
        method:'GET',
     }).done(function(data,success){
         if(data.error){
            alert(data.error);
         }else{
         $(e.target).find('button[type=submit]').text(submitText);
         $(e.target).find('button[type=submit]').removeClass('disabled');

         console.log(data);

         var element = '<table class="table table-bordered">';
         element += '<tr><td>Institution:</td><td>'+data.response.params[0].institution+'</td></tr>';
         element += '<tr><td>Programme:</td><td>'+data.response.params[0].programme+'</td></tr>'
         element += '<tr><td>First Name:</td><td>'+data.response.params[0].firstname+'</td></tr>';
         element += '<tr><td>Middle Name:</td><td>'+data.response.params[0].middlename+'</td></tr>';
         element += '<tr><td>Surname:</td><td>'+data.response.params[0].surname+'</td></tr>';
         element += '<tr><td>Gender:</td><td>'+data.response.params[0].gender+'</td></tr>';
         element += '<tr><td>Birth Date:</td><td>'+data.response.params[0].date_birth+'</td></tr>';
         element += '<tr><td>AVN:</td><td>'+data.response.params[0].avn+'</td></tr>';
         element += '<tr><td>Graduation Year:</td><td>'+data.response.params[0].diploma_graduation_year+'</td></tr>';
         element += '<tr><td>Username:</td><td>'+data.response.params[0].username+'</td></tr>';
         element += '<tr><td>Diploma Code:</td><td>'+data.response.params[0].diploma_code+'</td></tr>';
         element += '<tr><td>Registration Number:</td><td>'+data.response.params[0].registration_number+'</td></tr>';
         element += '<tr><td>Diploma GPA:</td><td>'+data.response.params[0].diploma_gpa+'</td></tr>';
         for(var i=0; i<data.response.params[0].diploma_results.length; i++){
            element += '<tr><td>'+data.response.params[0].diploma_results[i].subject+'</td><td>'+data.response.params[0].diploma_results[i].grade+'</td></tr>'
         }
         element += '</table>';

         $($(e.target).find('input[name=results_container]').val()).html(element);
         $($(e.target).find('input[name=display_modal]').val()).modal('show');
         
         $($(e.target).find('input[name=display_modal]').val()+' input[name=nacte_result_detail_id]').val(data.details.id);
         }
     });
});

// Display form processing
$('.ss-form-processing').submit(function(e){
     var resultsContainer = $(e.target).data('results-container');
     var id = $(e.target).attr('id');
     $(e.target).find('button[type=submit]').text('Processing...');
     $(e.target).find('button[type=submit]').addClass('disabled');

     var percentComplete = 100;
     var element = '';
      element += '<p class="ss-bold">Please wait...</p>';
      element += '<div class="progress progress-striped active">';
      element += '<div class="progress-bar progress-bar-warning role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
      element += '</div>';
     if(resultsContainer != null){
         $(resultsContainer).html(element).slideDown();
     }
     if(id == null){
         $(e.target).find('.ss-ajax-messages').html(element).slideDown();
     }else{
         $(e.target).find('#'+id+' .ss-ajax-messages').html(element).slideDown();
     }
});

// Submit form with ajax
$('.ss-form-ajax').submit(function(e){
    e.preventDefault();
    //var $btn = $(e.target).children('input[type=submit]').button('loading');
    var submitText = $(e.target).find('button[type=submit]').text();
    var id = $(e.target).attr('id');
    var target = $(e.target).attr('action');
    var resultsContainer = $(e.target).data('results-container');
    // var postData =  $(e.target).serialize();
    var postData = new FormData(e.target);
    // console.log(postData.get('full_phone'));
    var element = "";
    $(e.target).find('button[type=submit]').text('Processing...');
    $(e.target).find('button[type=submit]').addClass('disabled');

    var callAJAX = $.ajax({
        url: target,
        method: 'POST',
        data: postData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(){
            var percentComplete = 100;
                element += '<p class="ss-bold">Please wait...</p>';
                element += '<div class="progress">';
                element += '<div class="progress-bar bg-warning progress-bar-striped role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
                   element += '</div>';
               if(resultsContainer != null){
                   $(resultsContainer).html(element).slideDown();
               }
               if(id == null){
                   $(e.target).find('.ss-ajax-messages').html(element).slideDown();
               }else{
                   $(e.target).find('#'+id+' .ss-ajax-messages').html(element).slideDown();
               }
             /* 
            //Upload progress
            XMLHttpRequest.addEventListener("progress", function(evt){
              if (evt.lengthComputable) {  
                var percentComplete = evt.loaded / evt.total;
                element += '<p class="ss-bold">Please wait...</p>';
                element += '<div class="progress progress-striped active">';
                element += '<div class="progress-bar progress-bar-warning role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="'+percentComplete+'" style="width: 100%"><span class="sr-only"></span></div>';
                   element += '</div>';
               if(resultsContainer != null){
                   $(resultsContainer).html(element).slideDown();
               }
               if(id == null){
                   $(e.target).children('.ss-ajax-messages').html(element).slideDown();
               }else{
                   $(e.target).children('#'+id+' .ss-ajax-messages').html(element).slideDown();
               }
              }
            }, false); 
            //Download progress
            XMLHttpRequest.addEventListener("progress", function(evt){
              if (evt.lengthComputable) {  
                var percentComplete = evt.loaded / evt.total;
                //Do something with download progress
              }
            }, false); 
    
            */
           //element = '<span class="sg_blue sg_bold"><img width="25" height="25" src="'+host+'assets/img/system-img/pending.gif"> Please wait...</span>';
           
        },
        statusCode: {
          404: function(){
            alert("Page not found");
          },
          405: function() {
            alert("Technical problem occured");
          },
          500: function() {
            alert("Internal Server Error");
          }
        }
    });

    callAJAX.done(function(data, status){
        // console.log($.parseJSON(data.error_messages));
        // alert(typeof data.success_messages);
        $(e.target).find('button[type=submit]').text(submitText);
        $(e.target).find('button[type=submit]').removeClass('disabled');
        if(status == 'success'){
           element = '<div class="alert ';
           
           if(data.error_messages != null){
              element += 'alert-danger alert-dismissible" role="alert">';
              element += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
              element += '<span aria-hidden="true">&times;</span></button>';
              if(typeof data.error_messages == 'string'){
                element += '<p><img width="20" height="20" src="'+host+'assets/img/cancel.png">';
                  //element += '<p><span class="glyphicon glyphicon-remove sg_color_red"></span>';
                element += ' '+data.error_messages+'</p>';
              }else{
                var keys = Object.keys(data.error_messages);
                // for(var k in data.error_messages) keys.push(k);
                for(var key=0; key<keys.length; key++){
                  element += '<p><img width="20" height="20" src="'+host+'assets/img/cancel.png">';
                  //element += '<p><span class="glyphicon glyphicon-remove sg_color_red"></span>';
                  element += ' '+Object.values(data.error_messages)[key]+'</p>';
                }  
              }

           }else{
              element += 'alert-success alert-dismissible" role="alert">';
              element += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
              element += '<span aria-hidden="true">&times;</span></button>';

              if(typeof data.success_messages == 'string'){
                   element += '<p><img width="20" height="20" src="'+host+'assets/img/ok.png">';
                     //element += '<p><span class="glyphicon glyphicon-remove sg_color_red"></span>';
                     element += ' '+data.success_messages+'</p>';
                }else{
                   var keys = Object.keys(data.success_messages);
                   // for(var k in data.success_messages) keys.push(k);
                   for(var key=0; key<keys.length; key++){
                     element += '<p><img width="20" height="20" src="'+host+'assets/img/ok.png">';
                     //element += '<p><span class="glyphicon glyphicon-remove sg_color_red"></span>';
                     element += ' '+Object.values(data.success_messages)[key]+'</p>';
                   } 
                }
              
           }
           element += '</div>';
           //console.log(element);
           if(resultsContainer != null){
               $(resultsContainer).html(element).slideDown();
           }
           if(id == null){
               $(e.target).children('.ss-ajax-messages').html(element).slideDown();
           }else{
               $(e.target).children('#'+id+' .ss-ajax-messages').html(element).slideDown();
           }

           if(data.callback_url != null){
               setTimeout(5000);
               console.log('Refreshing...');
               window.location.href = data.callback_url;
           }
           
           //$btn.button('reset');
           if($(e.target).data('refresh-page') != null && data.success_messages != null){
             setTimeout(5000);
             window.location.reload(true);
           }

           if($(e.target).data('next-route') != null){
              setTimeout(5000);
              console.log('Refreshing...');
              window.location.href = $(e.target).data('next-route');
           }
           
           // Hide old content 
           if($(e.target).data('hide-target') != null){
               $('#'+$(e.target).data('hide-target')).slideUp();
           }

           // Show new content 
           if($(e.target).data('show-target') != null){
               $('#'+$(e.target).data('show-target')).slideDown();
           }
        }
    });
});

// Fix content height
function fixContentHeight(target){
   $(target).each(function(){
       var height = $(this).attr('data-min-height');
       $(this).css('min-height',height+'px');
   });
}

// Toggle check or radio
var source = '.ss-toggle-radio input[type=radio], .ss-toggle-check input[type=checkbox]';
toggleRow(source);
$(source).click(function(e){
    toggleRow(source);
});

var unToggleSource = '.ss-untoggle-radio input[type=radio], .ss-untoggle-check input[type=checkbox]';
unToggleRow(unToggleSource);
$(unToggleSource).click(function(e){
    unToggleRow(unToggleSource);
});

// Toggle live chat
 $('.ss-live-chat-collapse').click(toggleLiveChart);
 
 // Toggle live chat
 function toggleLiveChart(){
   if(!$('.ss-live-text').is(':visible')){
      $('.ss-live-text').slideDown();
   }else{
      $('.ss-live-text').slideUp(); 
   }
 }

 function changePage(source,target){
    $(source).click(function(e){
        e.preventDefault();
        $(source).load(target);
    });
}

function expandMainNav(division){
    $(division).slideDown(1200);
}


// Display collapsed row when checkbox above is checked
function toggleRow(source){
    // var target = null;
     $(source).each(function(){
           var target = $(this).parent().data('target');
           if($(this).is(':checked')){
              $(target).slideDown();
           }else{
              $(target).slideUp();
           }
     });
}

// Highlight checkboxes
function highlight(source){
    $(source).each(function(){
         if($(this).is(':checked')){
            $(this).parent().css('background','#fcf8e3');
         }else{
            $(this).parent().css('background','#e68523'); 
         }
     });
}

// Untoggle row
function unToggleRow(source){
    // var target = null;
     $(source).each(function(){
           var target = $(this).parent().data('untoggle-target');
           var tempTarget = $(this).parent().data('temp-untoggle-target');
           if(target != null){
              if($(this).is(':checked')){
                $(target).slideUp();
              }
           }

           if(tempTarget != null){
              if($(this).is(':checked')){
                $(tempTarget).slideUp();
              }else{
                $(tempTarget).slideDown();
              }
           }
     });
}



// Show or collapse read more on paragraphs
function collapseReadMoreDefault(target){
   $(target).each(function(){
       var paragraph = $(this).text();
       var characters = $(this).attr('data-text-length');
       // Check if paragraph characters length exceeds the predefined collapsible length
       if(paragraph.length > characters){
           var link = '<span class="classical-read-more">Read more</span>';
           var bufferVissible = '<span class="classical-read-more-visible">';
           var bufferHidden = '<span class="classical-read-more-hidden">';
           bufferHidden += paragraph + '<span class="classical-read-more-close">Close</span></span>';

           for(var i=0; i < characters; i++){
              bufferVissible += paragraph[i];
           }
            
           bufferVissible += '<span class="classical-read-more-dots">...</span></span>'+ link + bufferHidden; 
           $(this).html(bufferVissible); 
           //console.log("Buffer: "+bufferVissible+" Buffer length: " +bufferVissible.length);
       }
   }); 
}

// Breal long paragraphs
function breakLongParagraph(target){
   $(target).each(function(){
       var paragraph = $(this).text();
       var characters = $(this).attr('data-text-length');
       if(paragraph.length > characters){
           var bufferVissible = '';

           for(var i=0; i < characters; i++){
              bufferVissible += paragraph[i];
           }
            
           bufferVissible += '...'; 
           $(this).html(bufferVissible);
           //console.log("Buffer: "+bufferVissible+" Buffer length: " +bufferVissible.length);
       }
   }); 
}

function expandReadMore(target){
    $(target).siblings(".classical-read-more-visible").slideUp();
    $(target).siblings(".classical-read-more-hidden").slideDown('slow');
    $(target).hide();
}

function readMoreClose(target){
   $(target).parent().slideUp('slow');
   $(target).parent().siblings(".classical-read-more-visible").slideDown('slow');
   $(target).parent().siblings(".classical-read-more").show();
}

$('#ss-reload-control-number').on('click',function(e){
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

// Select city from specific country
$(".ss-country-selector").on('change',function(e){
     var countryId = $(e.target).val();
     var cityTarget = $(e.target).data('target');
     var urlTarget = $(e.target).data('url');

     $.post(urlTarget,{
          country_id: $(e.target).val(),
          _token:$(e.target).data('token') 
        }, function(data, success){
            if(data.cities != null){
               var results = '';
               for(var i = 0; i < data.cities.length; i++){
                  results += '<option value="'+data.cities[i].id+'">';
                  results += data.cities[i].name+'</option>';
               }
            }
        $(cityTarget).html(results);
     });
});

// Trigger login 
$('.ss-trigger-login').click(function(e){
    e.preventDefault();
    //alert($(e.target).data('target-trigger'));
    $($(e.target).data('target-trigger')).trigger('click');
});

// Popup user profile on hover
$('.ss-popup-content').mouseover(function(e){
   e.preventDefault();
   var target = $(e.target).data('target');  
   $(target).fadeIn();
});

$('.ss-popup-content').mouseout(function(e){
   e.preventDefault();
   var target = $(e.target).data('target');  
   $(target).fadeOut();
});

// Trigger login with facebook
$('.ss-trigger-fb-login').click(function(e){
   e.preventDefault();
   $($(e.target).data('target')).trigger('click');
});

/** W3 Schools scripts override **/

// Toggle w3 schools modal
$('.w3-toggle-modal, .w3-zoom-img').click(function(e){
    var target = $(e.target).data('target-modal');
    $(target).css('display','block');
});

// Close w3 schools modal 
$('.w3-closebtn').click(function(e){
    var target = $(e.target).data('target-modal');
    $(target).css('display','none');
});

// Close image zoom container 
$('.w3-animate-zoom').click(function(e){
   $(e.target).css('display','none');
});

/** W3 Schools scripts override **/

/** Fade animation script **/

// Activate banner animation
      var animation = $("#ss-banner-slider .ss-animation-item");
      var numSlides = animation.length;
      var activeSlide = 0;
      var speed = 10000;
      var fade = 1000;
      var pause = false;
      var timer = setInterval(rotate, speed);
      animation.eq(activeSlide).show();

      $(".ss-slider-prev").click(function(event) {
       activeSlide--;
       forceRotate();
       event.preventDefault();
      });

      $(".ss-slider-next").click(function(event) {
       activeSlide++;
       forceRotate();
       event.preventDefault();
      });

      function rotate() {
       activeSlide++;
       if (activeSlide == numSlides) {
          activeSlide = 0;
       }

       if (activeSlide < 0) {
         activeSlide = numSlides - 1;
       }

       animation.not(activeSlide).fadeOut(fade);
       animation.eq(activeSlide).fadeIn(fade);
      }

      function forceRotate(){
         if (activeSlide == numSlides) {
          activeSlide = 0;
         }

         if (activeSlide < 0) {
           activeSlide = numSlides - 1;
         }

         animation.not(activeSlide).fadeOut(fade);
         animation.eq(activeSlide).fadeIn(fade);
      }

      // Pause banner animations on hover 
      $("#ss-banner-slider, .ss-slider-prev, .ss-slider-next").hover(function() {
         clearInterval(timer);
         pause = true;
      }, function() {
          timer = setInterval(rotate, speed);
          pause = false;
      });
/** End of fade animation script **/

$(function() {
  $( "#crop_div" ).draggable({ containment: "parent" });
});

function crop()
{
  var posi = document.getElementById('crop_div');
  document.getElementById("top").value=posi.offsetTop;
  document.getElementById("left").value=posi.offsetLeft;
  document.getElementById("right").value=posi.offsetWidth;
  document.getElementById("bottom").value=posi.offsetHeight;
  return true;
}