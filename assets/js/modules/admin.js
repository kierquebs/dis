(function () {
   $('#comment-tbl').DataTable({        
		"searching": false,
        "ordering": false,
		"bLengthChange" : false, //thought this line could hide the LengthMenu
		"bInfo":false
    });	
	reset_pass();
	datePick();
})();
function reset_pass(){
	$('.reset').click(function(e){
		$dataID = $(e.currentTarget).attr('data-id');	
		$.ajax({
			type: 'POST',
			url: 'admin/resetpass',  
			data: { id: $dataID},
			success: function(data) {
				alert(data.msg);
			},
			dataType: 'json'
		});
	})
}
function datePick(){
	var date_input=$('.datetimepicker'); //our date input has the name "date"
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
	date_input.datepicker({
		format: 'mm/dd/yyyy',
		container: container,
		todayHighlight: true,
		autoclose: true,
	})
}
