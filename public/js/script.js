
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

    
    // // Initialize tinymce
    // tinymce.init({
    //  selector: '.ss-textarea',  
    //  auto_focus: 'element1',
    //  branding: false
    // });

    // $('.ss-select-search, .ss-select-search-lg').select2();   

    // $('.ss-select-tags').select2(); 

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
    //  $(function(){
    //     $('.ss-datepicker').fdatepicker({
    //       initialDate: '22-06-1989',
    //       format: 'dd-mm-yyyy',
    //       disableDblClickSelection: true,
    //     });
    //  });

    //   // Initialize time picker
    //  $(function(){
    //   $('.ss-timepicker').fdatepicker({
    //     format: 'dd-mm-yyyy hh:ii',
    //     disableDblClickSelection: true,
    //     language: 'vi',
    //     pickTime: true
    //   });
    // });
    
      $('#ss-signup-carousel').carousel({
          pause: true,
          interval: false
      }); 

      $('#ss-signup-next').on('click',function(){
         var error = false;
         if($('#ss-signup-carousel input[name=first_name]').val() == ''){
             $('#ss-signup-carousel input[name=first_name]').siblings('.help-block').html('First name is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=first_name]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=last_name]').val() == ''){
             $('#ss-signup-carousel input[name=last_name]').siblings('.help-block').html('Last name is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=last_name]').siblings('.help-block').html('');
         }
         if(!validateEmail($('#ss-signup-carousel input[name=email]').val()) && $('#ss-signup-carousel input[name=email]').val() != ''){
            $('#ss-signup-carousel input[name=email]').siblings('.help-block').html('Email address is invalid');
             error = true;
         }
         if($('#ss-signup-carousel input[name=email]').val() != ''){
            var token = $('#ss-signup-carousel input[name=email]').data('token');
            var target = $('#ss-signup-carousel input[name=email]').data('fetch-url');
            $.ajax({
                url:target,
                method:'POST',
                async: false,
                data:{
                  _token:token,
                  email:$('#ss-signup-carousel input[name=email]').val()
                }
            }).done(function(data){
                if(data.user){
                   $('#ss-signup-carousel input[name=email]').siblings('.help-block').html('Email address already taken');
                   error = true;
                }
            });
         }else{
            $('#ss-signup-carousel input[name=email]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=email]').val() == ''){
             $('#ss-signup-carousel input[name=email]').siblings('.help-block').html('Email address is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=email]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=phone]').val() == ''){
             $('#ss-signup-carousel input[name=phone]').parent().siblings('.help-block').html('Phone number is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=phone]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=password]').val() == ''){
             $('#ss-signup-carousel input[name=password]').siblings('.help-block').html('Password is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=password]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=password_confirmation]').val() == ''){
             $('#ss-signup-carousel input[name=password_confirmation]').siblings('.help-block').html('Password confirmation is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=password_confirmation]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=password_confirmation]').val() != $('#ss-signup-carousel input[name=password]').val()){
             $('#ss-signup-carousel input[name=password_confirmation]').siblings('.help-block').html('Password confirmation does not match');
             error = true;
         }

         if(error == false){
            $('#ss-signup-carousel').carousel('next');
         }
      });

      $('#ss-signup-next-final').click(function(){
         var error = false;
         if($('#ss-signup-carousel input[name=business_name]').val() == ''){
             $('#ss-signup-carousel input[name=business_name]').siblings('.help-block').html('Business name is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=business_name]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=business_description]').val() == ''){
             $('#ss-signup-carousel input[name=business_description]').siblings('.help-block').html('Business description is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=business_description]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=location]').val() == ''){
             $('#ss-signup-carousel input[name=location]').siblings('.help-block').html('Business location is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=location]').siblings('.help-block').html('');
         }
         if(typeof $('#ss-signup-carousel input[name=company_size]').val() == 'number'){
             $('#ss-signup-carousel input[name=company_size]').siblings('.help-block').html('Company size must a number');
             error = true;
         }
         if(!validateEmail($('#ss-signup-carousel input[name=business_email]').val())){
             $('#ss-signup-carousel input[name=business_email]').siblings('.help-block').html('Business email is invalid');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=business_email]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=company_phone]').val() == ''){
             $('#ss-signup-carousel input[name=company_phone]').parent().siblings('.help-block').html('Phone number is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=company_phone]').siblings('.help-block').html('');
         }
         if($('#ss-signup-carousel input[name=logo]').val() == ''){
             $('#ss-signup-carousel input[name=logo]').parent().siblings('.help-block').html('Business logo is requird');
             error = true;
         }else{
             $('#ss-signup-carousel input[name=logo]').siblings('.help-block').html('');
         }

         if(error == false){
            $('#ss-signup-carousel').carousel('next');
         }
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

// Select product option
$('.ss-product-color-option-btn').on('click',function(e){
   e.preventDefault();
   $($(e.target).data('target-radio-option')).trigger('click');
   $('.ss-product-color-option-btn').each(function(e) {
        $(this).css('border','1px solid #C0C0C0');
   });
   $(e.target).css('border','3px solid #5bc0de');
});

$('.ss-product-size-option-btn').on('click',function(e){
   e.preventDefault();
   $($(e.target).data('target-radio-option')).trigger('click');
   $('.ss-product-size-option-btn').each(function(e) {
        $(this).css('border','1px solid #C0C0C0');
   });
   $(e.target).css('border','3px solid #5bc0de');
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


//On scroll event of home page banner, change the background of top navigation bar
// $(window).bind('scroll',function(){
//    if($(window).width() > 991){
//       if($(window).scrollTop() > 25){
//          $('#ss-site-logo').removeClass('ss-site-logo-sm', {duration:1000});
//          $('#ss-site-logo').css('height','50px',{duration:3000}); 
//          $('#ss-site-logo').css('position','relative',{duration:3000}); 
//          $('#ss-site-logo').css('top','12px',{duration:3000}); 
//          // $('#ss-home-nav-bar-id').css('background','#006E8C');
//          $('#ss-home-nav-bar-id').css('background','#fff');
//          $('#ss-home-nav-bar-id').css('box-shadow','0px 10px 10px #000');
//       }else{
//          $('#ss-site-logo').addClass('ss-site-logo-lg', {duration:3000});
//          $('#ss-site-logo').css('height','80px',{duration:3000}); 
//          $('#ss-site-logo').css('position','relative',{duration:3000}); 
//          $('#ss-site-logo').css('top','20px',{duration:3000}); 
//          $('#ss-home-nav-bar-id').css('background','transparent');
//          $('#ss-home-nav-bar-id').css('box-shadow','none');
//       }
//    }

// Fix part of the dashboard on page scroll
$(window).bind('scroll',function(){
   if($(window).width() > 991){
      if($(window).scrollTop() > 360){
         $('#ss-status-post-container, #ss-stories-post-container').css('position','fixed'); 
         $('#ss-status-post-container, #ss-stories-post-container').css('top','55px'); 
         $('#ss-status-post-container, #ss-stories-post-container').addClass('container');
         
         $('#ss-status-post-container').find('.ss-status-column').addClass('col-md-6');
         $('#ss-status-post-container').find('.ss-status-column').removeClass('col-md-8');

         $('#ss-stories-post-container').find('.ss-status-column').addClass('col-md-6');
         $('#ss-stories-post-container').find('.ss-status-column').removeClass('col-md-12');

         $('#ss-right-center-sidebar').css('position','fixed'); 
         $('#ss-right-center-sidebar').css('top','55px'); 
         $('#ss-left-sidebar, #ss-right-sidebar').css('position','fixed');
         $('#ss-left-sidebar, #ss-right-sidebar').css('top','55px');
         // $('#ss-right-sidebar').find('.col-md-3').addClass('col-md-offset-6');
      }else{
         $('#ss-status-post-container, #ss-stories-post-container').css('position','relative'); 
         $('#ss-status-post-container, #ss-stories-post-container').css('top','0px'); 
         $('#ss-status-post-container, #ss-stories-post-container').removeClass('container');

         $('#ss-status-post-container').find('.ss-status-column').removeClass('col-md-6');
         $('#ss-status-post-container').find('.ss-status-column').addClass('col-md-8');

         $('#ss-stories-post-container').find('.ss-status-column').removeClass('col-md-6');
         $('#ss-stories-post-container').find('.ss-status-column').addClass('col-md-12');

         $('#ss-right-center-sidebar').css('position','fixed'); 
         var sidebar_vertical_offset = 360; // - 65;//$(window).scrollTop();
         $('#ss-right-center-sidebar').css('top',sidebar_vertical_offset+'px'); 
         $('#ss-left-sidebar, #ss-right-sidebar').css('position','fixed');
         $('#ss-left-sidebar, #ss-right-sidebar').css('top','60px'); 
         // $('#ss-right-sidebar').find('.col-md-3').removeClass('col-md-offset-6');
         // $('#ss-right-sidebar').css('left','-15px');
      }
   }
});

// Preview image thumbnail
$('.ss-product-preview-thumbnails img').click(function(e){
    var target = $(e.target).attr('src');
    $('.ss-product-preview-media img').attr('src',target);
});

//Remove AJAX results list on click
$(':not(#ss-ajax-results-container .ss-transparent-results-list a)').click(function(e){
    if($('#ss-ajax-results-container .ss-transparent-results-list').is(':visible')){
      $('#ss-ajax-results-container').html("");
    }
});

// Search for schools from top menu bar
$('#ss-public-search-input').keypress(function(e){
     var resultsContainer = $(e.target).data('results-container');
     var callAJAX = $.ajax({
        url: $(e.target).data('target'),
        method: 'POST',
        data: {
            _token : $(e.target).data('token'),
            search_query : $(e.target).val()
        },
        // beforeSend: function(){
        //    $(e.target).css('background','../images/system/prime_loader.gif');
        // },
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
        var element = '';
        if(status == 'success'){
           var results = '';
           if(data.products.length != 0){
               results += '<a href="#" disabled="disabled" class="list-group-item ss-bold">Products</a>';
               for(var i = 0; i < data.products.length; i++){
                  results += '<a class="list-group-item ss-bold" href="'+data.host+'/products/preview/'+data.products[i].id+'/'+data.products[i].slug+'"><span class="fa fa-shopping-basket"></span>';
                  results += '<img class="'+host+'/uploads/'+data.products[i].images[0].image+'"/> ';
                  results += data.products[i].name+'</a>';
               }
            }
            if(data.stores.length != 0){
               results += '<a href="#" disabled="disabled" class="list-group-item ss-bold">Stores</a>';
               for(var i = 0; i < data.stores.length; i++){
                  results += '<a class="list-group-item ss-bold" href="'+data.host+'/stores/'+data.stores[i].slug+'/profile"><span class="fa fa-newspaper-o"></span> ';
                  results += data.stores[i].name+' @'+data.stores[i].slug+'</a>';
               }
            }
            if(data.users.length != 0){
               results += '<a href="#" disabled="disabled" class="list-group-item ss-bold">People</a>';
               for(var i = 0; i < data.users.length; i++){
                  results += '<a class="list-group-item ss-bold" href="'+data.host+'/people/'+data.users[i].username+'"><span class="fa fa-user-circle"></span> ';
                  results += data.users[i].first_name+' '+data.users[i].last_name+' @'+data.users[i].username+'</a>';
               }
            }
            if(data.products.length == 0 && data.stores.length == 0 && data.users.length == 0){
               results += '<a href="#" disabled="disabled" class="list-group-item ss-bold">Sorry, we could not find anything you searched for.</a>';
            }
          $(resultsContainer).find('.list-group').html(results);
        }
    });
});

// Search for people by name or username
$('#ss-people-search-input').on('keypress',function(e){
     var resultsContainer = $(e.target).data('results-container');
     var callAJAX = $.ajax({
        url: $(e.target).data('target'),
        method: 'POST',
        data: {
            _token : $(e.target).data('token'),
            name : $(e.target).val()
        },
        // beforeSend: function(){
        //    $(e.target).css('background','../images/system/prime_loader.gif');
        // },
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
        var element = '';
        if(status == 'success'){
           var results = '';
            if(data.users.length != 0){
               for(var i = 0; i < data.users.length; i++){
                  if($(e.target).data('auth-id') != data.users[i].id){
                      results += '<a class="list-group-item ss-bold" href="#" data-img-src="'+host+'/assets/avatars/'+data.users[i].image+'" data-name="'+data.users[i].first_name+' '+data.users[i].last_name+'" data-recepient-id="'+data.users[i].id+'" data-recepient-type="user" data-input-fill="#ss-people-search-input"><span class="fa fa-user-circle"></span> ';
                      results += data.users[i].first_name+' '+data.users[i].last_name+' @'+data.users[i].username+'</a>';
                  }
               }
            }else{
               if(data.users.length == 1 && data.users[0].id == $(e.target).data('auth-id')){
                  results += '<a href="#" disabled="disabled" class="list-group-item ss-bold">Sorry, we could not find anything you searched for.</a>';
               }else{
                  results += '<a href="#" disabled="disabled" class="list-group-item ss-bold">Sorry, we could not find anything you searched for.</a>';
               }
            }
          $(resultsContainer).find('.list-group').html(results);
        }
    });
});

// Display transfer recepient identity
$('#ss-stores-search-dropdown, #ss-people-search-dropdown').on('click','a',function(e){
   $('#ss-transfer-receiver').css('background','#fafafa');
   $('#ss-transfer-receiver').find('img').attr('src',$(e.target).data('img-src'));
   $('#ss-transfer-receiver').find('.caption').html('<p class="ss-bold">'+$(e.target).data('name')+'</p>');
   $($(e.target).data('input-fill')).val($(e.target).data('name'));
   $('#ss-transfer-credit-form').find('input[name=recepient_id]').val($(e.target).data('recepient-id'));
   $('#ss-transfer-credit-form').find('input[name=recepient_type]').val($(e.target).data('recepient-type'));
   $('#ss-transfer-credit-form').find('button[type=submit]').removeClass('disabled');
});

// Search for stores by name
$('#ss-stores-search-input').on('keypress',function(e){
     var resultsContainer = $(e.target).data('results-container');
     var callAJAX = $.ajax({
        url: $(e.target).data('target'),
        method: 'POST',
        data: {
            _token : $(e.target).data('token'),
            name : $(e.target).val()
        },
        // beforeSend: function(){
        //    $(e.target).css('background','../images/system/prime_loader.gif');
        // },
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
        var element = '';
        if(status == 'success'){
           var results = '';
            if(data.stores.length != 0){
               for(var i = 0; i < data.stores.length; i++){
                  if($(e.target).data('auth-store-id') != data.stores[i].id){
                     results += '<a class="list-group-item ss-bold" href="#" data-img-src="'+host+'/assets/uploads/'+data.stores[i].logo+'" data-name="'+data.stores[i].name+'" data-recepient-id="'+data.stores[i].id+'" data-recepient-type="store" data-input-fill="#ss-stores-search-input"><span class="fa fa-newspaper-o"></span> ';
                     results += data.stores[i].name+' @'+data.stores[i].slug+'</a>';
                  }
               }
            }else{
              if(data.stores.length == 1 && data.stores[0].id == $(e.target).data('auth-store-id')){
                 results += '<a href="#" disabled="disabled" class="list-group-item ss-bold">Sorry, we could not find any store you searched for.</a>';
              }else{
                 results += '<a href="#" disabled="disabled" class="list-group-item ss-bold">Sorry, we could not find anything you searched for.</a>';
              }
            }
          $(resultsContainer).find('.list-group').html(results);
        }
    });
});

$(':not(#ss-search-dropdown a)').on('click',function(e){
    if($('#ss-search-dropdown').is(':visible') && !$('#ss-public-search-input').is(':focus')){
      $('#ss-search-dropdown').hide();
    }
});

$(':not(.ss-search-dropdown a)').on('click',function(e){
    if($($('#ss-stores-search-input').siblings('.ss-search-dropdown')).is(':visible') && !$('#ss-stores-search-input').is(':focus')){
      $($('#ss-stores-search-input').siblings('.ss-search-dropdown')).hide();
    }
});

$(':not(.ss-search-dropdown a)').on('click',function(e){
    if($($('#ss-people-search-input').siblings('.ss-search-dropdown')).is(':visible') && !$('#ss-people-search-input').is(':focus')){
      $($('#ss-people-search-input').siblings('.ss-search-dropdown')).hide();
    }
});

$('#ss-public-search-input, #ss-people-search-input, #ss-stores-search-input').on('focus',function(e){
     $($(e.target).data('results-container')).show();
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

// Follow toggle store
$('.ss-follow-btn').click(function(e){
   e.preventDefault();
   $.ajax({
      url:$(e.target).data('post-url'),
      method:'POST',
      data:{
        _token:$(e.target).data('token'),
        user_id:$(e.target).data('user-id'),
        followable_id:$(e.target).data('followable-id'),
        followable_type:$(e.target).data('followable-type')
      }
   }).done(function(data){
      if(data.status == 'success'){
        
        $($(e.target).data('count-target')).html(data.followers_count+' Followers');
        if($(e.target).data('status') == '0'){
           $(e.target).html('<span class="fa fa-check"></span> Following');
           $(e.target).css('background','#fff');
           $(e.target).css('color','#000');
           $(e.target).data('status','1');
        }else{
           $(e.target).html('<span class="fa fa-plus"></span> Follow');
           $(e.target).css('background','#5bc0de');
           $(e.target).css('color','#fff');
           $(e.target).data('status','0');
        }
        var holder = $(e.target).data('post-url');
        $(e.target).data('post-url',$(e.target).data('flip-url'));
        $(e.target).data('flip-url',holder);
      }
   });
});

// Like content
$('.ss-like-btn').click(function(e){
   e.preventDefault();
   $.ajax({
      url:$(e.target).data('post-url'),
      method:'POST',
      data:{
        _token:$(e.target).data('token'),
        user_id:$(e.target).data('user-id'),
        likable_id:$(e.target).data('likable-id'),
        likable_type:$(e.target).data('likable-type')
      },
      statusCode:{
        500: function() {
              alert("Internal Server Error");
            }
      }  
   }).done(function(data){
      if(data.status == 'success'){
        $($(e.target).data('count-target')).html(data.likes_count+' Likes');
        if($(e.target).data('status') == '0'){  
           $(e.target).css('color','#c93636');
           $(e.target).data('status','1');
        }else{
           $(e.target).css('color','#999');
           $(e.target).data('status','0');
        }
        var holder = $(e.target).data('post-url');
        $(e.target).data('post-url',$(e.target).data('flip-url'));
        $(e.target).data('flip-url',holder);
      }
   });
});

// Connect to mate
$('.ss-connect-btn').click(function(e){
   e.preventDefault();
   $.ajax({
      url:$(e.target).data('post-url'),
      method:'POST',
      data:{
        _token:$(e.target).data('token'),
        user_id:$(e.target).data('user-id'),
        mate_id:$(e.target).data('mate-id')
      },
      statusCode:{
        500: function() {
              alert("Internal Server Error");
            }
      }  
   }).done(function(data){
      if(data.status == 'success'){
        if($(e.target).data('status') == '0'){
           $(e.target).html('<span class="fa fa-check"></span> Requested');
           $(e.target).css('background','#fff');
           $(e.target).css('color','#000');
           $(e.target).data('status','2');
        }else if($(e.target).data('status') == '1'){
           $(e.target).html('<span class="fa fa-plus"></span> Connect');
           $(e.target).css('background','#5bc0de');
           $(e.target).css('color','#fff');
           $(e.target).data('status','0');
        }else if($(e.target).data('status') == '2'){
           $(e.target).html('<span class="fa fa-plus"></span> Connect');
           $(e.target).css('background','#5bc0de');
           $(e.target).css('color','#fff');
           $(e.target).data('status','0');
        }
        var holder = $(e.target).data('post-url');
        $(e.target).data('post-url',$(e.target).data('flip-url'));
        $(e.target).data('flip-url',holder);
      }
   });
});

// Connect to mate
$('.ss-connect-accept-btn').click(function(e){
   e.preventDefault();
   $.ajax({
      url:$(e.target).data('post-url'),
      method:'POST',
      data:{
        _token:$(e.target).data('token'),
        user_id:$(e.target).data('user-id'),
        mate_id:$(e.target).data('mate-id')
      },
      statusCode:{
        500: function() {
              alert("Internal Server Error");
            },
        404: function(){
              alert( "Page not found" );
            },
        405: function() {
              alert("Technical problem occured");
            }
      }  
   }).done(function(data){
      console.log(data.status);
      if(data.status == 'success'){
        if($(e.target).data('status') == '0'){
           $(e.target).html('<span class="fa fa-check"></span> Accepted');
           $(e.target).css('background','#fff');
           $(e.target).css('color','#000');
           $(e.target).data('status','1');
           $(e.target).parent().parent().find('.ss-connect-delete-btn').attr('disabled','disabled');
        }else{
           $(e.target).html('Accept');
           $(e.target).css('background','#5bc0de');
           $(e.target).css('color','#fff');
           $(e.target).data('status','0');
           $(e.target).parent().parent().find('.ss-connect-delete-btn').removeAttr('disabled');
        }
        var holder = $(e.target).data('post-url');
        $(e.target).data('post-url',$(e.target).data('flip-url'));
        $(e.target).data('flip-url',holder);
      }
   });
});

// Delete connection requests
$('.ss-connect-delete-btn').click(function(e){
   e.preventDefault();
   $.ajax({
      url:$(e.target).data('post-url'),
      method:'POST',
      data:{
        _token:$(e.target).data('token'),
        user_id:$(e.target).data('user-id'),
        mate_id:$(e.target).data('mate-id')
      },
      statusCode:{
        500: function() {
              alert("Internal Server Error");
            },
        404: function(){
              alert( "Page not found" );
            },
        405: function() {
              alert("Technical problem occured");
            }
      }  
   }).done(function(data){
      if(data.status == 'success'){
        if($(e.target).parent().parent().parent().find('.ss-mate-media').size() == 1){ 
          $(e.target).parent().parent().parent().parent().remove();
        }else{
          $(e.target).parent().parent().remove();
        }
      }
   });
});

// Favour content
$('.ss-favourite-btn').click(function(e){
   e.preventDefault();
   $.ajax({
      url:$(e.target).data('post-url'),
      method:'POST',
      data:{
        _token:$(e.target).data('token'),
        user_id:$(e.target).data('user-id'),
        favourable_id:$(e.target).data('favourable-id'),
        favourable_type:$(e.target).data('favourable-type')
      },
      statusCode:{
        500: function() {
              alert("Internal Server Error");
            },
        404: function(){
              alert( "Page not found" );
            },
        405: function() {
              alert("Technical problem occured");
            }
      }   
   }).done(function(data){
      if(data.status == 'success'){
        $($(e.target).data('count-target')).html(data.favourites_count+' Favourites');
        if($(e.target).data('status') == '0'){
           $(e.target).css('color','#EFCF14');
           $(e.target).data('status','1');
        }else{
           $(e.target).css('color','#999');
           $(e.target).data('status','0');
        }
        var holder = $(e.target).data('post-url');
        $(e.target).data('post-url',$(e.target).data('flip-url'));
        $(e.target).data('flip-url',holder);
      }
   });
});

// Start chat
$('.ss-start-chat').on('click',function(e){
   e.preventDefault();
   if(!$('#'+$(e.target).data('chat-target')).is(':visible')){
      var element = '<div class="panel panel-default ss-chat-panel col-md-4 col-sm-6 ss-no-padding" id="'+$(e.target).data('chat-target')+'">';
      element += '<div class="panel-heading">';
      element += '<div class="media ss-chat-recepient-media">';
      element += '<div class="media-left">';
      element += '<a href="'+$(e.target).data('user-url')+'"><img src="'+$(e.target).data('img-url')+'" class="media-object img-circle ss-media-avatar-xxxs ss-avatar-border" onerror="this.src=\''+$(e.target).data('img-placeholder')+'\'"></a>';
      element += '</div>';
      element += '<div class="media-body">';
      element += '<h5><a href="'+$(e.target).data('user-url')+'" class="ss-color-white ss-bold">'+$(e.target).data('name')+'</a></h5>';
      element += '</div>';
       element += '<div class="media-right"><a href="#" class="btn btn-default fa fa-window-minimize ss-custom-blue ss-color-white ss-chat-minimize"></a></div>';
      element += '<div class="media-right"><a href="#" class="btn btn-default fa fa-remove ss-custom-blue ss-color-white ss-chat-close"></a></div>';
      element += '</div>';
      element += '</div>';
      element += '<div class="panel-body">';
      $.ajax({
         url:$(e.target).data('fetch-chats-url'),
         method:'POST',
         data:{
            _token:$(e.target).data('token'),
            sender_id:$(e.target).data('sender-id'),
            recepient_id:$(e.target).data('recepient-id')
         },
         async: false,
         cache: false,
         statusCode:{
            500: function() {
                  alert("Internal Server Error");
                },
            404: function(){
                  alert( "Page not found" );
                },
            405: function() {
                  alert("Technical problem occured");
                }
          }  
      }).done(function(data,success){
         if(success = 'success'){
           console.log(data.chats.length);
           if(data.chats.length != 0){
             for(var i = 0; i < data.chats.length; i++){
                element += '<div class="media ss-chat-media">';
                element += '<div class="media-left">';
                element += '<img src="'+data.host+'/assets/avatars/'+data.chats[i].sender.image+'" class="media-object img-circle ss-media-avatar-xxs ss-avatar-border" onerror="this.src=\''+$(e.target).data('img-placeholder')+'\'">';
                element += '</div>';
                element += '<div class="media-body">';
                element += '<h5>'+data.chats[i].sender.first_name+' '+data.chats[i].sender.last_name+'</h5>';
                element += '<p>'+data.chats[i].message+'</p>';
                element += '</div>';
                element += '</div>';
             }  
           }
         }
      });
      element += '</div>';
      element += '<div class="panel-footer">';
      element += '<form action="'+$(e.target).data('chat-form-action')+'" method="POST">';
      element += '<input type="hidden" name="_token" value="'+$(e.target).data('token')+'">';
      element += '<input type="hidden" name="sender_id" value="'+$(e.target).data('sender-id')+'">';
      element += '<input type="hidden" name="recepient_id" value="'+$(e.target).data('recepient-id')+'">';
      element += '<input type="hidden" name="recepient_type" value="'+$(e.target).data('recepient-type')+'">';
      element += '<textarea name="message" class="form-control ss-chat-textarea" rows="1" placeholder="Write a message..." required></textarea>';
      element += '</form>';
      element += '</div>';
      element += '</div>';
      
      if(!$('.ss-chat-form-container').children('.row').find('#'+$(e.target).data('chat-target')).is(':visible')){
         $('.ss-chat-form-container').children('.row').html(element);
         $('.ss-chat-form-container-md').children('.row').html(element);
      }
      
      if($('.ss-chat-form-container').children('.row').find('.ss-chat-panel').size() == 3){
        $('.ss-chat-form-container').children('.row').find('.ss-chat-panel:last-child').remove();
      }
   }
});

// Close chat window
$(document).on('click','.ss-chat-close',function(e){
   e.preventDefault();
   $(e.target).parent().parent().parent().parent().remove();
   var chat_heads = $('.ss-chat-form-container').children('.row').html();
    var target = host+'/chats/heads/store';
    $.ajax({
      url: target,
      method:'POST',
      data:{
         chat_heads:chat_heads
      }
    }).done(function(data,success){
        console.log('Done');
    });
});

// Close chat window
$(document).on('click','.ss-chat-minimize',function(e){
   e.preventDefault();
   if($(e.target).parent().parent().parent().siblings().is(':visible')){
      $(e.target).parent().parent().parent().siblings().hide();
   }else{
      $(e.target).parent().parent().parent().siblings().show();
   }
   var chat_heads = $('.ss-chat-form-container').children('.row').html();
    var target = host+'/chats/heads/store';
    $.ajax({
      url: target,
      method:'POST',
      data:{
         chat_heads:chat_heads
      }
    }).done(function(data,success){
        console.log('Done');
    });
});

// Maintain a chat window on link click
$(window).on('beforeunload', function () {
    var chat_heads = $('.ss-chat-form-container').children('.row').html();
    var target = host+'/chats/heads/store';
    $.ajax({
      url: target,
      method:'POST',
      data:{
         chat_heads:chat_heads
      }
    }).done(function(data,success){
        console.log('Done');
    });
});

// Submit chat with Enter Click
$(document).on('keypress','.ss-chat-panel textarea',function(e){
    if(e.which == 13){
       e.preventDefault();
       var target = $(e.target).parent().attr('action');
       var postData = $(e.target).parent().serialize();
       var element = '';
       $.ajax({
          url:target,
          method:'POST',
          data:postData,
          async: false,
          cache: false,
          statusCode:{
            500: function() {
                  alert("Internal Server Error");
                },
            404: function(){
                  alert( "Page not found" );
                },
            405: function() {
                  alert("Technical problem occured");
                }
          }  
       }).done(function(data,success){
          if(success = 'success'){
              element += '<div class="media ss-chat-media">';
              element += '<div class="media-left">';
              element += '<img src="'+data.host+'/assets/avatars/'+data.chat.sender.image+'" class="media-oject img-circle ss-media-avatar-xxs ss-avatar-border" onerror="this.src=\''+data.host+'/assets/img/user-avatar.png\'">';
              element += '</div>';
              element += '<div class="media-body">';
              element += '<h5>'+data.chat.sender.first_name+' '+data.chat.sender.last_name+'</h5>';
              element += '<p>'+data.chat.message+'</p>';
              element += '</div>';
              element += '</div>';
          }    
       });
       $(e.target).val('');
       $(e.target).css('height','40px');
       $(e.target).parent().parent().parent().find('.panel-body').append(element);
       $(e.target).parent().parent().parent().find('.panel-body').scrollTop(1000);
    }
});

// Submit chat with Press Send
$('.ss-chat-panel form').submit(function(e){
       e.preventDefault();
       var target = $(e.target).attr('action');
       var postData = $(e.target).serialize();
       var element = '';
       $.ajax({
          url:target,
          method:'POST',
          data:postData
       }).done(function(data,success){
          element += '<div class="media ss-chat-media">';
          element += 'div class="media-left">';
          element += '<img src="'+data.host+'/assets/avatars/'+data.chat.sender.image+'" class="ss-media-avatar-xxs ss-avatar-border">';
          element += '</div>';
          element += '<div class="media-body">';
          element += '<h5>'+data.chat.sender.first_name+''+data.chat.sender.last_name+'</h5>';
          element += '<p>'+data.chat.message+'</p>';
          element += '</div>';
          element += '</div>';
       });
       $(e.target).find('textarea').val('');
       $(e.target).parent().parent().find('.media-body').append(element);
});

// Ajax link
$('.ss-ajax-link, .ss-add-cart-btn').click(function(e){
    e.preventDefault();
    var content = $(e.target).text();
    $.ajax({
      url:$(e.target).attr('href'),
      method: 'GET',
      beforeSend:function(){
        // $(e.target).append('<img src="../img/ajax-loader.gif" width="25px" height="25px">');
        $(e.target).addClass('disabled');
        $(e.target).html('Processing...');
      },
      statusCode:{
            500: function() {
                  alert("Internal Server Error");
                },
            404: function(){
                  alert( "Page not found" );
                },
            405: function() {
                  alert("Technical problem occured");
                }
          }  
    }).done(function(data,success){
        if(data.cart_items_count != null){
           if(data.cart_items_count == 0){
              window.location.reload(true);
           }else{
              $($(e.target).data('count-target')).html(data.cart_items_count);
           }    
        }
        $(e.target).removeClass('disabled');
        $(e.target).text(content)
        // $(e.target).find('img').fadeOut();
        // $(e.target).append('<img src="../img/ajax-loader.gif" width="25px" height="25px">');

        if($(e.target).data('refresh-page') != null){
           setTimeout(5000);
           window.location.reload(true);
         }
    });
});

// Trigger post image upload
$('.ss-img-upload-btn').click(function(e){
   e.preventDefault();
   $($(e.target).data('target')).trigger('click');
});

$('textarea').each(function () {
  // Do something if you want
}).on('input', function () {
  this.style.height = 'auto';
  // Customize if you want
  this.style.height = (this.scrollHeight - 30) + 'px'; //The weight is 30
});

// Submit comment
$('.ss-comment-ajax textarea').bind('keypress',function(e){
    if(e.which == 13){
      e.preventDefault();
      var target = $(e.target).parent().attr('action');
      var postData = $(e.target).parent().serialize();
      var element = "";
      var callAJAX = $.ajax({
           url: target,
           method: 'POST',
           data: postData,
           beforeSend: function(){
              // $(e.target).parent().parent().next().children('img').fadeIn();
           },
           statusCode: {
            404: function(){
              alert( "Page not found" );
            },
            405: function() {
              alert("Technical problem occured");
            },
            500: function() {
              alert("Internal Server Error");
            }
          }
      });

      callAJAX.done(function(data,status){
        if(status == 'success'){
           // $(e.target).parent().parent().next().children('img').fadeOut();
           element += '<div class="media ss-post-media">';
           element += '<div class="media-left">';
           element += '<a href="'+data.host+'/profile?id='+data.user.id+'"><img class="media-object img-circle ss-media-avatar-xxs ss-avatar-border" src="'+data.host+'/assets/avatars/'+data.user.image+'" onerror="this.src=\''+data.host+'/assets/img/user-avatar.png\'"></a>';
           element += '</div>';
           element += '<div class="media-body">';
           element += '<p class="ss-font-xs"><span class="ss-bold"><a href="'+data.host+'/people/'+data.user.username+'">'+data.user.first_name+' '+data.user.last_name+'</a></span>, '+data.comment.comment+'</p>';
           element += '<span class="ss-key-value ss-font-xs">';
           element += '<span class="ss-key"></span>';
           element += '<span class="ss-value"> <a data-toggle="collapse" href="#ss-reply-comment-'+data.commentable.id;
           if(data.comment.commentable_type == 'comment'){
             element +='-'+data.comment.id;
           }
           element += '" aria-expanded="false" aria-controls="ss-reply-comment-'+data.commentable.id;
           if(data.comment.commentable_type == 'comment'){
            element +='-'+data.comment.id;
           }
           element += '"><span class="fa fa-reply"></span> Reply</a></span>';
           element += '</span>';
           element += '</div>';
           element += '</div>';
           if(data.comment.commentable_type == 'comment'){
              $(element).insertBefore($(e.target).parent().parent().parent().parent().children().last());
           }else{
              $(element).insertAfter($(e.target).parent().parent().parent().parent().prev().children().last());
           }
           $($(e.target).parent().find('input[name=count_target]').val()).html(data.comments_count+' Comments');
           $(e.target).val('');

        }
      });

      
    }
});

// Submit post
$('.ss-form-ajax-post').submit(function(e){
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
                element += '';
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
           setTimeout(5000);
           $(resultsContainer).slideUp();
           window.location.reload(true);
        }
    });
});

// Match country code on on country selector
$('#ss-country-code-selector').change(function(e){
    var target = $(e.target).data('target');
    $.ajax({
       url:$(this).data('fetch-url'),
       method:'POST',
       data:{
          _token:$(this).data('token'),
          country_id:$(this).val()
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
    }).done(function(data,success){
        if(target == '#ss-phone'){
            var input = document.querySelector("#ss-phone");
            window.intlTelInput(input,{
              hiddenInput: "full_phone",
              initialCountry: data.country.iso_code_2,
              // separateDialCode: true,
              utilsScript: "../assets/js/utils.js?1562189064761"
            });
        }
        
        if(target == '#ss-company-phone'){
            var input2 = document.querySelector("#ss-company-phone");
            window.intlTelInput(input2,{
              hiddenInput: "company_full_phone",
              initialCountry: data.country.iso_code_2,
              // separateDialCode: true,
              utilsScript: "../assets/js/utils.js?1562189064761"
            });
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