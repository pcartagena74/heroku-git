function FieldIsEmpty (fValue) {
	    var field = new RegExp(/\S/);
	    return !field.test(fValue)
}
