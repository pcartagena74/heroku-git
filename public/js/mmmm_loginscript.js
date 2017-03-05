$(document).ready(function() { 
    /* login submit */
    function submitForm() {  
        var data = $("#login-form").serialize();
    
    $.ajax({
        type : 'POST',
        url  : '/include/login_process.php',
        data : data,
        beforeSend: function() { 
            $("#err").html('');
            $("#btn-login").text('<span class="glyphicon glyphicon-transfer"></span> Sending ...');
    },
    success :  function(response) {
        var result = eval(response);
        if(result.status==="ok"){
            $("#btn-login").html('<img src="btn-ajax-loader.gif" /> &nbsp; Signing In ...');
        } else {
            bootbox.alert(result.message);
           // $("#btn-login").html('<span class="glyphicon glyphicon-log-in"></span> &nbsp; Login');
        }
    },
    error :  function(response) {
        bootbox.alert('There was a terrible error.');
    }
   });
    return false;
  }
    /* login submit */
});