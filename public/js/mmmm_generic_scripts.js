function FieldIsEmpty (fValue) {
	    var field = new RegExp(/\S/);
	    return !field.test(fValue)
}

//------------------------------------------
// Dialog Box-related Functions
//
// 1) setupGenericMessage:	sets up the message
// 2) showGenericBox:	
//------------------------------------------

function setupGenericMessage(data, title) {
	if (!title) title = 'Info';
	var btns = '<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Ok</button>';
	megShowGenericDialogBox(title, data, 'meg-dialog-general-message-center', btns);
}

function showGenericBox(title, contents, megClass, buttons) {

	if (!megClass) megClass = 'meg-dialog-center';
	if (!contents) contents = '';
	if (!title) title = '';


	$("#megGenericDialogBoxTitle").html(title);
	$("#megGenericDialogBoxText").html(contents);
	$("#megGenericDialogBox").find(".modal-dialog:first").removeClass().addClass('modal-dialog').addClass(megClass);
	if (buttons) {
		$("#megGenericDialogBoxFooter").html(buttons);
	}
	$('#megGenericDialogBox').modal('show');
	return false;
} //megShowGenericDialogBox

function megHideGenericDialogBox() {
	$('#megGenericDialogBox').modal('hide');
} //megHideGenericDialogBox



