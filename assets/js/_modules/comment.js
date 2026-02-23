var COMMENT_URL = window.location.pathname.split( '/' )[2];
(function () {	
	setDatatbl('#comment-tbl');
	sendComment();
})();
/*
* AJAX POPULATE
*/

function PopulateItemsTable() {
	$.ajax({
	type: "POST",
	url: COMMENT_URL+"/get_comment/"+$('#comment-tbl').attr('data-id')+"?page="+page,
	dataType: "json",
	success: function (data) {
		myTable.rows().remove().draw();		
		$('#'+tblID+'_paginate').remove();		
		if(data.total != 0){
			$jsonReturn = data.result;
				if(data.offset > 0) $i = data.offset + 1;
			$.each(data.result, function(key, value) {
				var result = [];
					result.push(value.user_name); 
					result.push(value.comment); 
					result.push(value.date_created);
					
					var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).find('td:nth-child(1)').addClass('tr-user').attr({'width':150}).end()
							.find('td:nth-child(2)').addClass('tr-comment').end()
							.find('td:nth-child(3)').addClass('tr-date');
			});				
			pagination(data.totalData, data.per_page, false);
		}
	}
	});
}
function sendComment(){
	var modalID = 'commentModal';
	setModal(modalID, 'ERROR MESSAGE', 'OK', 'PLEASE ENTER YOUR COMMENT!');
	
	$('.modal-btn').click(function(e){
			$('.modal .close').click();
	});
	
	$('#send-btn').click(function(e){
		$me = $(e.currentTarget);	
			$msg = $('.comment').val();
		if($msg != ''){
			$.ajax({
				type: "POST",
				url: COMMENT_URL+'/send',
				data: { transid: $me.attr('data-id'), message: $msg },
				dataType: "json",	
				error: function() {
					failureCallback();
				},
				success: function (data) {
					console.log(data.success);
					$('.comment').val('');
					PopulateItemsTable();	
					callUpdate();
					console.log('notification');
				}
			});
		}else $('#'+modalID).modal('show'); 
	});	
}

