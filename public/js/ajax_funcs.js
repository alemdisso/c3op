//creates a prototype Ajax object, sends a request, and registers the callback function 'handleResponse'
function passIdToAjax(actionrequest, id, handler)
{
//remember to put a word separator between elements of the camelcase action name, per the ZF manual:
var complete = handler || this.onSubmit;

var myAjax = new Ajax.Request(
    actionrequest, 
    {
        method: 'get', 
        parameters: {id: id},
        //onComplete: handler
        onComplete: function(req) { complete.call(this, req, id)}
    });
}

//handle the Ajax response …
function confirmRealizationResponse(transport, id)
{
$('confirmRealization-'+id).innerHTML = transport.responseText;
}

function rejectDeliveryResponse(transport, id)
{
$('rejectDelivery-'+id).innerHTML = transport.responseText;
}

