jQuery(document).ready(function($) {
    
    //---- Set up cached variables
    nutPopup = $('.nut-material-popup');
    nutPopupText = $('.nut-material-popup .nut-text');
    nutPopupOk = $('.nut-material-popup .nut-buttons button.nut-ok');
    nutPopupCancel = $('.nut-material-popup .nut-buttons button.nut-cancel');

    nutHightlightItem = $('.nut-highlight-item');
    nutHightlightBackground = $('.nut-highlight-overlay');

    newusertutorial = {};

    newusertutorial.steps = JSON.parse( $('#nut-tutorial-steps').val() );

    //---- Steps Handler
    function nutNextStep() {
        //---- Increase the step # and reset any button's actions
        window.nutCurrentStep++;
        nutPopupOk.attr('action', '')
        nutPopupCancel.attr('action', '');

        //---- If there are more steps, then figure out the type and set it up
        if( window.nutCurrentStep < newusertutorial.steps.length ) {
            var currentStepType = newusertutorial.steps[window.nutCurrentStep].type;
            if( 'dialog-slides' == currentStepType ) {
                nutDialogSlides( newusertutorial.steps[window.nutCurrentStep].data, 1 );
            } else if ( 'action-click' == currentStepType ) {
                nutActionClick( newusertutorial.steps[window.nutCurrentStep].data );
            }
        }
    }



    /*----------------*/
    /*---- DIALOG ----*/
    /*----------------*/

    //---- Set up the dialog
    function nutCheckDialogSlidesActions(currentSlide) {
        console.log(currentSlide);
        //---- Change the buttons depending on the slide number
        if( (currentSlide + 1) == nutPopup.attr('data-max') ) {
            nutPopupOk.attr('action', 'next-step').text('Next Step');
        } else {
            nutPopupOk.attr('action', '').text('Continue');
        }
        
        if ( 0 == currentSlide ) {
            nutPopupCancel.attr('action', 'close').text('Close');
        } else {
            nutPopupCancel.attr('action', '').text('Back');
        }
    }

    function nutDialogSlides(slides, fadeIn) {
        window.currentStepData = slides;

        nutPopup.attr('data-current', '0');
        nutPopup.attr('data-max', window.currentStepData.length);
        nutPopupText.text( slides[0] );
        nutPopupCancel.text('Close');
        if( 1 < window.currentStepData.length ) {
            nutPopupOk.text('Continue');
        }
        if( fadeIn ) {
            nutPopup.fadeIn(350, 'linear');
            nutHightlightBackground.fadeIn(350, 'linear');
        }
        nutCheckDialogSlidesActions(0);
    }

    //---- Change the dialog slide
    function nutDialogSlidesChange(direction) {
        //---- Update the current slide
        currentSlide = parseInt( nutPopup.attr('data-current') ) + direction;
        nutPopup.attr('data-current', currentSlide);

        //---- Fade Out if user clicks finish or close
        if( nutPopup.attr('data-max') <= currentSlide || 0 > currentSlide ) {
            nutHightlightBackground.fadeOut(350, 'linear');
            nutPopup.fadeOut(350, 'linear');
            return;
        } else {
            nutPopupText.css('opacity', '0').on('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd', function () {
                nutPopupText.text( window.currentStepData[currentSlide] ).css('opacity', '1');
            });
        }

        nutCheckDialogSlidesActions(currentSlide);
    }

    //---- Set up the dialog buttons
    $('.nut-material-popup .nut-buttons button.nut-ok').on('click', function(event) {
        if( 'next-step' == $(this).attr('action')  ) {
            nutHightlightBackground.fadeOut(350, 'linear');
            nutPopup.fadeOut(350, 'linear').promise().done(function () {
                nutNextStep();
            });
        } else {
            nutDialogSlidesChange(1);
        }
    });

    $('.nut-material-popup .nut-buttons button.nut-cancel').on('click', function() {
        nutDialogSlidesChange(-1);
    });



    /*----------------*/
    /*---- ACTION ----*/
    /*----------------*/

    //---- Set up the dialog
    function nutActionClick(actionData) {
        var clickThisElement = actionData['element'];
            clickThisElementOffset = $(clickThisElement).offset();

            highlightWidth = $(clickThisElement).outerWidth() + 50;
            highlightHeight = $(clickThisElement).outerHeight() + 50;
            highlightTop = clickThisElementOffset.top - 30;
            highlightLeft = clickThisElementOffset.left- 30;

        if( 'circle' == actionData['shape'] ) {
            if( highlightWidth > highlightHeight ) {
                hDiff = highlightWidth - highlightHeight;
                highlightTop += -hDiff/2
                highlightHeight = highlightWidth;
            } else {
                hDiff = highlightHeight - highlightWidth;
                highlightLeft += -hDiff/2
                highlightWidth = highlightHeight;
            }
            nutHightlightItem.css('border-radius', '1000px').addClass('nut-rotate-me');
        } else if ( 'box' == actionData['shape'] ) {
            nutHightlightItem.css('border-radius', '0px').removeClass('nut-rotate-me');
        }

        nutHightlightItem.css('width', highlightWidth + 'px');
        nutHightlightItem.css('height', highlightHeight + 'px');
        nutHightlightItem.css('top', highlightTop + 'px');
        nutHightlightItem.css('left', highlightLeft + 'px');

        nutHightlightItem.fadeIn( 350, 'linear' );

        $(clickThisElement).bind('click', function () {
            nutHightlightItem.fadeOut( 350, 'linear' );
            nutHightlightBackground.fadeOut( 350, 'linear' );
            $(clickThisElement).unbind('click');
            nutNextStep();
        });
    }

    window.nutCurrentStep = -1;
    nutNextStep();

}); 