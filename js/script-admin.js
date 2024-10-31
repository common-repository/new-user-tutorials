jQuery(document).ready(function($) {
    
    //---- Tutorial post type page
    nutDialogFormHtml = '<input type="text" class="widefat nut-dialog-slide" placeholder="Dialog Text"><div class="nut-add-dialog-slide button button-secondary button-large">Add Dialog Slide</div>';
    nutActionClickFormHtml = '<input type="text" class="nut-action-click" placeholder="CSS selector to click"><select class="nut-action-click-outline-type"><option value="box">Box</option><option value="circle">Circle</option></select>';

    $('#nut-add-step').on('click', function (e) {
        $('#new_user_tutorials_steps_meta table tbody').append('<tr> <td></td> <td> <select class="nut-select-step-type"> <option value="dialog-slides">Dialog Slides</option> <option value="action-click">Click Action</option> </select> </td> <td>' + nutDialogFormHtml + '</td> <td></td> </tr>');
        nutNumberStepRows();
    });

    $('#new_user_tutorials_steps_meta').on('click', 'table tbody tr td:last-child', function (e) {
        console.log('ayyy');
        $(this).parent().css('opacity','0').on('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd', function () {
            $(this).remove();
            nutUpdateStepsMetaVal();
        });
    });

    $('#new_user_tutorials_steps_meta').on('click', '.nut-add-dialog-slide', function (e) {
        $(this).before('<input type="text" class="widefat nut-dialog-slide" placeholder="Dialog Text">');
    });

    $('#new_user_tutorials_steps_meta').on('change', '.nut-select-step-type', function () {
        var selectVal = $(this).val();
        if( 'dialog-slides' == selectVal ) {
            $(this).parent().next('td').html( nutDialogFormHtml );
        } else if( 'action-click' == selectVal ) {
            $(this).parent().next('td').html( nutActionClickFormHtml );
        }
    });

    //---- To make the tutorial steps sortable
    $('#new_user_tutorials_steps_meta table tbody').sortable({
        stop: function() {
            nutNumberStepRows();
        }
    });

    //---- To delete empty dialog slides
    $('#new_user_tutorials_steps_meta').on('keydown', '.nut-dialog-slide', function (e) {

        //---- If the input is empty & either the backspace or delete key is pressed
        if( ( e.keyCode == 8 || e.keyCode == 46 ) && $(this).val() == '' ) {
            $(this).fadeOut(350, 'linear', function () {
                //---- Place cursor at end of previous input
                var temp = $(this).prev().focus().val(); 
                    $(this).prev().val('').val(temp);

                //---- Remove the input
                $(this).remove();
            });
        }

    });

    function nutNumberStepRows() {
        $('#new_user_tutorials_steps_meta table tbody tr').each(function () {
            var rowIndex = $(this).index()+1;
            $('td:first-child', this).text(rowIndex);
            nutUpdateStepsMetaVal();
        });
    }
    nutNumberStepRows();





    function nutUpdateStepsMetaVal() {
        var nutSteps = [];

        $('#new_user_tutorials_steps_meta table tbody tr').each(function () {
            var nutStepRow = {};
                nutStepRow.type = $('.nut-select-step-type', this).val();

            if( 'dialog-slides' == nutStepRow.type ) {
                nutStepRow.data = [];
                $('.nut-dialog-slide', this).each(function () {
                    nutStepRow.data.push( $(this).val() );
                });
                if( 1 < nutStepRow.data.length || ( 1 == nutStepRow.data.length && nutStepRow.data[0] != '' ) ) {
                    nutSteps.push( nutStepRow );
                }

            } else if( 'action-click' == nutStepRow.type ) {
                nutStepRow.data = {};
                nutStepRow.data.element = $('.nut-action-click', this).val();
                nutStepRow.data.shape = $('.nut-action-click-outline-type', this).val();
                if( '' != nutStepRow.data.element ) {
                    nutSteps.push( nutStepRow );
                }
            }

        });

        $('#nut_steps_meta_val').val( JSON.stringify( nutSteps ) );
    }

    $('#new_user_tutorials_steps_meta').on('mouseup keyup', function () {
        nutUpdateStepsMetaVal();
    });































});