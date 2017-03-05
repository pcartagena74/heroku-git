function validateCode(eventID) {
	var codeValue = $("#discount_code").val();
	if (FieldIsEmpty(codeValue)) {
		var message = '<span><i class="fa fa-warning fa-2x text-warning mid_align">&nbsp;</i>Enter a discount code.</span>';
		$('#status_msg').html(message).fadeIn(500).fadeOut(3000);
	
	} else {
		
		$.ajax({
			type: 'POST',
			cache: false,
			async: true,
            url: '/discount/'+eventID,
			dataType: 'json',
			data: { 
				event_id: 		eventID,
				discount_code : codeValue 
			},
			beforeSend: function() { $('#status_msg').html('').fadeIn(0); },
			success: function(data){
				var result = eval(data);
				 $('#status_msg').html(result.message).fadeIn(0);
				 $('#discount').text(result.percent);
			},
			error: function(data){
				var result = eval(data);
				$('#status_msg').html(result.message).fadeIn(0);
			}
		});
	}
};
