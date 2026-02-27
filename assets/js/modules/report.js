(function () {
	datePick();
	setDatatbl();
	searchBox();
})();
 
function searchBox(){
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="order_id"]').val();
			if($search != '') $searchTxt = '&order_id='+$search;
		$search = $('#search-form input[name="po_no"]').val();
			if($search != '') $searchTxt = '&po_no='+$search;
		$search = $('#search-form input[name="deno"]').val();
			if($search != '') $searchTxt = '&deno='+$search;
		$datet = $('#search-form input[name="datet"]').val();
			if($datet != '') $searchTxt += '&datet='+$datet;
		
		if($searchTxt != ''){
			page = 1; search = $searchTxt;
			PopulateItemsTable();
		}
	});
}
/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "GET",
	url: "report/get_item?page="+page+search,
	dataType: "json",
	beforeSend: $.LoadingOverlay("show"),
	success: function (data) {
		myTable.rows().remove().draw();
		$('#'+tblID+'_paginate, .export').remove();
		if(data.total != 0){
			$jsonReturn = data.result;
			$i = 1;
			if(data.offset > 0) $i = data.offset + 1;
			$t_deno = ''; $t_qty = $t_tamount = $t_rembal = 0;
			$.each(data.result, function(key, value) {					
				$grayRecord = $rsnum = $ctype = $date2 = '';
				var result = [];
					result.push($i++);  
					for (x = 1; x <= 11; x++) { 
						result.push('');
					}
				$t_deno = $t_deno+' '+value.deno; 
				$t_qty += parseInt(value.qty); 
				$t_tamount += parseInt(value.baltamount);
				$t_rembal += parseInt(value.balqty);
				var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).addClass('queue-tr')
							.attr('data-id', value.ocid) 
								.find('td:nth-child(2)').addClass('company').append(value.product).end()
								.find('td:nth-child(3)').addClass('order').text(value.orderid).end()
								.find('td:nth-child(4)').addClass('po').text(value.po).end() 
								.find('td:nth-child(5)').addClass('queue-tbl-span deno number').append(value.deno).end()
								.find('td:nth-child(6)').addClass('queue-tbl-span qty number').text(value.qty).end()
								.find('td:nth-child(7)').addClass('queue-tbl-span number').append(value.balqty).end()
								.find('td:nth-child(8)').addClass('queue-tbl-span tamount number').append(value.baltamount).end()
								.find('td:nth-child(9)').addClass('queue-tbl-span bfirst').append(value.bfirst).end()
								.find('td:nth-child(10)').addClass('queue-tbl-span blast').append(value.blast).end()
								.find('td:nth-child(11)').addClass('queue-tbl-span').append(value.created_time);
			});				
			pagination(data.total, data.per_page);
			$("#t-deno").text($t_deno); 
			$("#t-qty").text($t_qty).digits(); 
			$("#t-tamount").text($t_tamount).digits(); 
			$("#t-rembal").text($t_rembal).digits();
		}
		$.LoadingOverlay("hide");
	}
	});
}
function genReport() {
	url = BASEURL + 'report/export?' + search;
	$('.export').attr('href', url).click();
}
