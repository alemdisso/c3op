/*
== Datepicker ==================================================================
*/
$(document).ready(function() {

  $('.datepicker').Zebra_DatePicker({
    format: 'd/m/Y'
  });
  
});


/*
== Dialogs =====================================================================
*/
$(document).ready(function() {

    $('.simple').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong>, a small, compact and highly configurable dialog box plugin for jQuery');
    });

    $('.error').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong> has no dependencies other than <em>jQuery 1.5.2+</em> and works in all major' +
            ' browsers like<br>- Firefox<br>- Opera<br>- Safari<br>- Chrome<br>- Internet Explorer 6+', {
            'type':     'error',
            'title':    'Error'
        });
    });

    $('.warning').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong> is meant to replace JavaScript\'s <em>alert</em> and <em>confirmation</em>' +
            ' dialog boxes. <br><br> Can also be used as a notification widget - when configured to show no buttons and to close' +
            ' automatically - for updates or errors, without distracting users from their browser experience by displaying obtrusive alerts.', {
            'type':     'warning',
            'title':    'Warning'
        });
    });

    $('.question').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong> can generate 5 types of dialog boxes: confirmation, information, ' +
            ' warning, error and question.<br><br>The appearance of the dialog boxes is easily customizable by changing the CSS file ', {
            'type':     'question',
            'title':    'Question'
        });
    });

    $('.information').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong> dialog boxes can be positioned anywhere on the screen - not just in the middle!' +
            '<br><br>By default, dialog boxes can be closed by pressing the ESC key or by clicking anywhere on the overlay.', {
            'type':     'information',
            'title':    'Information'
        });
    });

    $('.confirmation').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong> is a small (4KB minified), compact (one JS file, no dependencies other than jQuery 1.5.2+)' +
            ' and highly configurable dialog box plugin for jQuery meant to replace JavaScript\'s <em>alert</em> and <em>confirmation</em> dialog boxes.', {
            'type':     'confirmation',
            'title':    'Confirmation'
        });
    });

    $('.callback-after').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong>, a small, compact and highly configurable dialog box plugin for jQuery', {
            'type':     'question',
            'title':    'Custom buttons',
            'buttons':  ['Yes', 'No', 'Help'],
            'onClose':  function(caption) {
                alert((caption != '' ? '"' + caption + '"' : 'nothing') + ' was clicked');
            }
        });
    });

    $('.callback-before').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong>, a small, compact and highly configurable dialog box plugin for jQuery', {
            'type':     'question',
            'title':    'Custom buttons',
            'buttons':  [
                            {caption: 'Yes', callback: function() { alert('"Yes" was clicked')}},
                            {caption: 'No', callback: function() { alert('"No" was clicked')}},
                            {caption: 'Cancel', callback: function() { alert('"Cancel" was clicked')}}
                        ]
        });
    });

    $('.top-right').bind('click', function(e) {
        e.preventDefault();
        $.Zebra_Dialog('<strong>Zebra_Dialog</strong>, a small, compact and highly configurable dialog box plugin for jQuery', {
            'title':    'Custom positioning',
            'position': ['right - 20', 'top + 20']
        });
    });

    $('.notification').bind('click', function(e) {
        e.preventDefault();
        new $.Zebra_Dialog('<strong>Zebra_Dialog</strong>, a small, compact and highly configurable dialog box plugin for jQuery', {
            'buttons':  false,
            'modal': false,
            'position': ['right - 20', 'top + 20'],
            'auto_close': 2000
        });
    });

    $('.custom').bind('click', function(e) {
        e.preventDefault();
        new $.Zebra_Dialog('Buy me a coffee if you like this plugin!', {
            'custom_class': 'myclass',
            'title': 'Customizing the appearance'
        });
    });

});
