(function($) {
	"use strict";
    /********************************
                Global vars
    *********************************/
    var FLAG = false;   
    var tr = $('.table').find('tbody tr').not(':first');
    var href = document.URL;    
    var last_segment = href.substring(href.lastIndexOf('/') + 1);
    //var success = '<div class="alert alert-success alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'+delete_success+'</div>';
    //var failed = '<div class="alert alert-danger alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'+delete_fail+'</div>';
    /********************************
                End global vars
    *********************************/

	(function addXhrProgressEvent($) {
	    var originalXhr = $.ajaxSettings.xhr;
	    $.ajaxSetup({
	        progress: function() { },
	        xhr: function() {
	            var req = originalXhr(), that = this;
	            if (req) {
	                if (typeof req.addEventListener == "function") {
	                    req.addEventListener("progress", function(evt) {
	                        that.progress(evt);
	                    },false);
	                }
	            }
	            return req;
	        }
	    });
	})(jQuery);
    /********************************
                Scrolling
    *********************************/
    if ( ($(window).height() + 100) < $(document).height() ) {
        $('#top-link-block').removeClass('hidden').affix({
            // how far to scroll down before link "slides" into view
            offset: {top:100}
        });
    }

    /* Icheck */        

    /* Datatable */
    setInterval(function() {
        console.log("12");
    }, 5000);
    $('#example').dataTable( {        
        "processing": true,
        "serverSide": true,        
        "ajax": "api/"+last_segment
    } );
    
    /* Action click on tr row */
    $('#checkall').click(function(){
        var checkboxes = $('table').find(':checkbox').not(':checkbox[disabled]');
        if($(this).prop('checked')) {
          $('.table').find('tbody tr').addClass('selected');
          checkboxes.prop('checked', true);
        } else {
          $('.table').find('tbody tr').removeClass('selected');
          checkboxes.prop('checked', false);
        }
    });
    /********************
    *** Button action ***
    *********************/  
    /* 
    $('button[bsd-action=add]').click(function(event) {
        window.location.href = url+'dashboard/'+last_segment+"/create";        
    });

    $('button[bsd-action=print]').click(function(event) {
        window.print();
    });

    $('button[bsd-action=reload]').click(function(event) {
        var table = $('#example').dataTable();
        table.fnReloadAjax();
    });  


    $('button[bsd-action=cancel]').click(function(event) {        
        window.location.href = return_url ;
    });

    $('button[bsd-action=delete]').click(function(event) {
        var items = '';
        if(confirm(confirm_delete)){
            $(".table input:checkbox:checked:not(#checkall)").each(function(index){
                if(items != ''){
                    items += ",";
                }
                items += $(this).val();
            });
            if(items == ''){
                alert(dashboard_grid_error_delete);
            }else{
                console.log(items);
                $.ajax({
                    async: false,
                    url: last_segment+'/delete',
                    type: "POST",
                    dataType:'json',
                    data : {list : items,_token :token},
                    cache: !1,
                    beforeSend: function () {
                        
                    },
                    success: function (e) {
                        if(e.message == 'success'){
                            var table = $('#example').dataTable();
                            table.fnReloadAjax();
                            $('#alert-message').html(success);
                        }else{
                            var fail = '<div class="alert alert-danger alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'+e.raw+'</div>';
                            $('#alert-message').html(fail);
                        }
                    },
                    error: function () {
                        
                    }
                })
            }            
        }
    });    
    */
    
    /*******************
    *** Autocomplete ***
    ********************

	$('#header-search .search-field').autocomplete({})
	$.ajax({
		url: 'search/json',
		dataType: 'json'
	}).done(function (data) {
		$('#search').autocomplete({
			autoSelectFirst: true,
			lookup: data,
			appendTo: '#result',
			onSearchStart: function(){
				$('#loading').show();
			},
			onSearchComplete: function(){
				$('#loading').hide();
			}
		});
	});
    */
	$("#my-form").validate({
        ignore: [],    
        rules: {    
            file: {
                required: true,
                extension: "png|jpg|gif"
            }
        } 
    });

    $('#file').change(function()
    {
        upload_image();        
    });

})(jQuery);
/*
function exporter(tables,t){
    var a = $('#export_action').val();
    var x = '';
    var items = $('#example thead th');
    items.each(function() {
        if(x != ''){
            x += ",";         
        }        
        x += $(this).text().trim();        
    });        
    var obj = {table : tables,ext : t,action:a,p : x};    
    var link= url+"dashboard/export?"+$.param(obj);
    window.location.href = link;    
}

function get_link(){
    var link = $('#fshare').val();
    $.ajax({
        async: true,
        url: 'curl',
        type: "POST",
        data : {fshare : link},
        cache: !1,
        beforeSend: function () {
            
        },
        success: function (e) {
            if(e.message == 'passed'){
                $('#output').val(e.link);
            }else{
                $('#error_default').html(e.message);
            }
        },
        error: function () {
            
        }
    })
}
*/
function remove_image(name){
	
	$.ajax({
        async: true,
        url: 'delete',
        type: "POST",
        data : {name : name},
        cache: !1,
        beforeSend: function () {
            
        },
        success: function (e) {
        	if(e.remove_id != '' && e.remove_id != null){
        		$('#'+e.remove_id).remove();
        	}
            // Conflict file lists 
            var file_lists = $('#file_lists_id').val();            
            var temp = new Array();
            var new_string = '';
            temp = file_lists.split(",");
            for(i = 0 ; i<temp.length ;i++){
                if(temp[i] == name){
                    continue;
                }
                if(new_string != ''){
                    new_string += ",";
                }
                new_string += temp[i];
            }
            $('#file_lists_id').val(new_string);
        },
        error: function () {
            
        }
    })
}

function redirect(method,id){
    var array = document.URL;    
     window.location.href= url+'dashboard/'+_table+"/"+method+"/"+id;
}

function upload_image() {
    var file = new FormData();
    jQuery.each($('#file')[0].files, function(i, filez) {
        file.append('file['+i+']', filez);
    });
    $('.progress').show();
    $.ajax({
        url: 'do_upload',
        data: file,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        async: true,
  		progress: function(evt) {			
	        if (evt.lengthComputable) {
	        	var percent = parseInt( (evt.loaded / evt.total * 100), 10);
	            $('#progressbar').attr('style','width:'+percent+"%");
	            $('#progressbar_span').html(percent+'% Total');
	        }
	        else {
	            console.log("Length not computable.");
	        }
	    },
        success: function(e){            
            console.log(e);   
            $('.progress').slideUp();
            if(e.message == 'fail'){
            	var Error_message = '<div class="alert alert-danger" role="alert">';
            	for(i = 0 ;i<e.status.length;i++){
            		Error_message += e.status[i].file[0]+"\n";
            	}
            	Error_message += '</div>';
            	$('.alert-error').html(Error_message);
            }else{
            	var Success_message = '';
                var Input_value = "";
            	for(i = 0 ; i<e.data.length ; i++){
            		var new_name     = (e.data[i]);
            		var filename     = e.id[i].replace(".","_");
            		Success_message += '<div id="'+filename+'" class="col-md-2">';
            		Success_message += '<img src="'+e.data[i]+'" alt="..." class="img-thumbnail">';	            		
            		Success_message += '<button type="button" onclick="remove_image(\''+new_name+'\')" class="btn btn-danger btn-block">Remove</button></div>';
                    if(Input_value!=''){
                        Input_value += ",";
                    }
                    Input_value     += e.data[i];
            	}
            	$('#result').append(Success_message);			
                $('#file_lists_id').val(Input_value);
            }
        }
    }); 
};