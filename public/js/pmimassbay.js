// PMI MassBay script for Event Listing
// ====================================

// For this script to work 2 things are required on the page invoking this script:
// 1. embed jquery
// 2. have a div with id="output"

document.writeln('<iframe id="mCentric_frame" src="https://www.mCentric.org/10/99" width="100%" scrolling="no" border="0" frameBorder="0" style="border:0px;position:relative;">Your browser does not support Iframes. Please upgrade to a newer version.</iframe>');

/*
$(document).ready(function() {
    $.ajax({
        type: 'GET',
        cache: false,
        async: true,
        url: "https://www.mCentric.org/eventlist/10/99/1",
        dataType: 'json',
        success: function (data) {
            var result = eval(data);
            $('#output').html(result.message).fadeIn(0);
        },
        error: function (data) {
            var result = eval(data);
            $('#output').html(result.message).fadeIn(0);
        }
    });
});
*/
