(function () {
	$('[data-toggle="tooltip"]').tooltip(); 
	loader();
})();

  
window.onload = function(){
    if (window.jQuery){
        console.log('jQuery is loaded');
    }else{
        alert('jQuery is not loaded');
    }
}
//window.setTimeout($(".alert").fadeOut("slow", function(){$(".alert").remove();}) , 10000);
$( document ).ajaxError(function( event, request, settings ) {
	//location.reload();
});
function failureCallback(){
	window.location.href = BASEURL+'/login';
};
function loader(){
	$(".form-loader").submit(function(e){
		$.LoadingOverlay("show");
	});
}
function datePick(){
	var date_input=$('.datetimepicker'); //our date input has the name "date"
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
	date_input.datepicker({
		format: 'yyyy-mm-dd',
		container: container,
		todayHighlight: true,
		autoclose: true,
	});
}

function setModal(id, title, btn, body){
	if(!title) title = id;
	if(!btn) btn = 'OK';
	if(!body) body = '';
	$('.modal').attr({'id':id, 'aria-labelledby': '#'+id});
	$('.modal .modal-title').attr('id', id).html(title);
	$('.modal .modal-btn').html(btn);
	$('.modal .modal-body').attr('id', id).html(body);
}

function urlIndex($segment){
	$url = window.location.pathname;
	if($segment == 2) $return = $url.split('/').reverse()[1];
	else $return = $url.split('/').reverse()[0];
	
	if($return == 'filter') return $url.split('/').reverse()[1];
	else return $return;
}

/*
* DataTable SETUP w/ pagination
*/
function setDatatbl($ID = '#queue-tbl', $TIMEOUT = true, $hidePopulateTbl = false){ 
	tblID = $ID.replace("#", "");
	page = 1;
	myTable = $($ID).DataTable({        
		"searching": false,
		"ordering": false,
		"bLengthChange" : false, //thought this line could hide the LengthMenu
		"bInfo":false,
		"deferRender": true,
		"paging": false,
		"lengthChange": false,
		"autoWidth": false,
		"sDom": 'lfrtip',
		"oLanguage": { 
			"sEmptyTable": "No data available in table" 
		} 
	});	
	if($hidePopulateTbl == false) PopulateItemsTable(); 
	//if($TIMEOUT == true) tblTimeOut(); //PopulateItemsTable(); 
}

function tblTimeOut(){
	//PopulateItemsTable(); 
	//setInterval(function(){
		$.getJSON("login/db_update", function(data) {
			if(data.log == false) failureCallback() ;
			else{
				if(data.stat == true){
					navCheck = true;
					if($('body').hasClass('modal-open') == false){
						PopulateItemsTable(); 	
					}						
				}else navCheck = false;
			}		
		}); 
	//}, 10000); 
}
function activateLinks(){
	var x = $('.paginate_button');
	for(var i=0;i<x.length;i++){
		$(x[i]).click(function(e){
			page = $(this).attr('data-dt-idx');
			PopulateItemsTable();
			//console.log(page);
		});
	}
	$('[data-toggle="tooltip"]').tooltip(); 
	$(".number").digits();
	
	$('#search-form .clear-all').click(function(e){
        e.stopImmediatePropagation();
		search = '&all'; 
		$('#search-form input, #search-form select').val('');		
		$('.p_date, .p_day').addClass('hide').removeClass('error-select');			
		$(".branch_li, #list_tbl").hide();
		PopulateItemsTable();
	});
	$('.export').click(function(e){
		e.stopPropagation();	
		genReport();
	});
	$('.select-order select[name="order"]').change(function(e){
        e.stopImmediatePropagation();
			search = search.replace("&order=asc", "").replace("&order=desc", "");	
		search = search+'&order='+$(e.currentTarget).val(); 
		PopulateItemsTable();
	});
	$('#checkAll').change(function (e) {
		e.preventDefault();
		$('input.checkProcess').prop('checked', $(this).prop('checked'));
		processBTN();
	});
	$('input.checkProcess').change(function (e) {
		e.preventDefault();
		processBTN();
	});
}
function processBTN(){
	$(".branch_li").hide();
	$('#create-req, #print_pa').prop("disabled", true);
	
	//if($('#checkAll').length == 0) checkArr = '';
		
	var checkBox = $('.checkProcess:checked').length;
	if(checkBox == 0) $('.process-tbl').addClass('hide');
	else{		
		$('#print_pa').prop("disabled", false);
		
		$('.process-tbl').removeClass('hide');	
		$(".process-tbl").show();
		var tVA = tUA = 0;
		$("#queue-tbl .queue-tr ").each(function(){
			if($(this).find('.checkProcess:checked').length != 0){
				$x = $(this).find('td.amountU');
				$xU = parseInt($x.attr('data-uknown')) ;
				$xV = parseInt($x.attr('data-amount')) - $xU;			
				tVA += $xV;
				tUA += $xU;
			}
		});
		if(tVA !=0 )$('#create-req').prop("disabled", false);		
		$("#tVA").text(tVA);
		$("#tUA").text(tUA);		
		$(".number").digits();			
	}
}

function pagination($totalRow, $per_page = 5, $export = true, $orderName = 'DATE & TIME', $exportName = 'EXPORT REPORT'){
	if($export == true){
		$('<a class="export" aria-label="EXPORT"><span class="glyphicon glyphicon-export" aria-hidden="true"></span>'+$exportName+'</a>').insertBefore('#'+tblID);
			
		if($('.select-order').length == 0 && $orderName !=''){
			$('<span class="select-order">SORT BY '+$orderName+' <select name="order">'
			+'<option value="asc">ASC</option><option value="desc">DESC</option>'
			+'</select></span>').insertBefore('#'+tblID);
		}
	}
		
		$tblID = tblID;
		$page = parseInt(page);
		$paginate = parseInt($totalRow);
		$total_pages = parseInt($paginate);
		$adjacents = 2;
		
		if ($page == 0) $page = 1;
		$prev = $page - 1;
		$next = $page + 1;				
		$lastpage = Math.ceil($total_pages/$per_page);
		$lpm1 = $lastpage - 1;
		
		$pageID = $tblID+'_paginate';
		$prevID = $tblID+'_previous';
		$nextID = $tblID+'_next';
		
		//console.log('hey');
		$('<div class="dataTables_paginate" id="'+$pageID+'"></div>').insertAfter('#'+$tblID);
		
		$("#"+$pageID).append('<div class="dataTables_info">Total Records '+$totalRow+'</div>');

		if($lastpage > 1){	
			//PREV BUTTON
			if ($page > 1) { 			
				$("#"+$pageID).append('<a class="paginate_button previous" aria-controls="'+$tblID+'" data-dt-idx="'+$prev+'" tabindex="0" id="'+$prevID+'">Previous</a>');
			}
			
			// list of pages
			if ($lastpage < 6 + ($adjacents * 2)){	
				for ($counter = 1; $counter <= $lastpage; $counter++){
					if ($counter == $page){
						$("#"+$pageID).append('<a class="paginate_button disabled current" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
					}else{
						$("#"+$pageID).append('<a class="paginate_button " aria-controls="queue-tbl" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');	
					}						
				}
			}else if($lastpage > 5 + ($adjacents * 2)){
				//hide active button
				if($page < 1 + ($adjacents * 2)){
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){
						if ($counter == $page){
							$("#"+$pageID).append('<a class="paginate_button disabled current" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}else{
							$("#"+$pageID).append('<a class="paginate_button " aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}							
					}					
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="'+$lpm1+'" tabindex="0">'+$lpm1+'</a>');
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="'+$lastpage+'" tabindex="0">'+$lastpage+'</a>');	
				}else if($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)){ //hide front or back	
					$("#"+$pageID).append('<a class="paginate_button page_dark " aria-controls="'+$tblID+'" data-dt-idx="1" tabindex="0">1</a>');
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="2" tabindex="0">2</a>');
					
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++){
						if ($counter == $page){
							$("#"+$pageID).append('<a class="paginate_button disabled current" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}else{
							$("#"+$pageID).append('<a class="paginate_button" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}							
					}
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="'+$lpm1+'" tabindex="0">'+$lpm1+'</a>');
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="'+$lastpage+'" tabindex="0">'+$lastpage+'</a>');	
				}else{// hide prev page		
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="1" tabindex="0">1</a>');
					$("#"+$pageID).append('<a class="paginate_button page_dark" aria-controls="'+$tblID+'" data-dt-idx="2" tabindex="0">2</a>');

					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++){
						if ($counter == $page){
							$("#"+$pageID).append('<a class="paginate_button disabled current" aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');
						}else{
							$("#"+$pageID).append('<a class="paginate_button " aria-controls="'+$tblID+'" data-dt-idx="'+$counter+'" tabindex="0">'+$counter+'</a>');	
						}							
					}
				}
			}
			// next button	
			if ($page < $counter - 1){
				$("#"+$pageID).append('<a class="paginate_button next" aria-controls="'+$tblID+'" data-dt-idx="'+$next+'" tabindex="0" id="'+$nextID+'">Next</a>');
			}	
		} 
	activateLinks(); // click event	
}

$.fn.digits = function(){ 
    return this.each(function(){ 
        $(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") ); 
    })
}

