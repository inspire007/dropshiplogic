var App = function () {
	
	$(document).ready(function(){
		$('#main_container').show();
		$('#curtain').remove();	
	});

    var isIE8 = false; // IE8 mode
    var isIE9 = false;
    var currentPage = ''; // current page

    // useful function to make equal height for contacts stand side by side
    var setEqualHeight = function (columns) {
        var tallestColumn = 0;
        columns = jQuery(columns);
        columns.each(function () {
            var currentHeight = $(this).height();
            if (currentHeight > tallestColumn) {
                tallestColumn = currentHeight;
            }
        });
        columns.height(tallestColumn);
    }

    // this function handles responsive layout on screen size resize or mobile device rotate.
    var handleResponsive = function () {
        if (jQuery.browser.msie && jQuery.browser.version.substr(0, 1) == 8) {
            isIE8 = true; // checkes for IE8 browser version
            $('.visible-ie8').show(); //
        }
        if (jQuery.browser.msie && jQuery.browser.version.substr(0, 1) == 9) {
            isIE9 = true;
        }

        var isIE10 = !! navigator.userAgent.match(/MSIE 10/);

        if (isIE10) {
            jQuery('html').addClass('ie10'); // set ie10 class on html element.
        }

        // loops all page elements with "responsive" class and applied classes for tablet mode
        // For metornic  1280px or less set as tablet mode to display the content properly
        var handleTabletElements = function () {
            if ($(window).width() <= 1280) {
                $(".responsive").each(function () {
                    var forTablet = $(this).attr('data-tablet');
                    var forDesktop = $(this).attr('data-desktop');
                    if (forTablet) {
                        $(this).removeClass(forDesktop);
                        $(this).addClass(forTablet);
                    }
                });
                handleTooltip();
            }
        }

        // loops all page elements with "responsive" class and applied classes for desktop mode
        // For metornic  higher 1280px set as desktop mode to display the content properly
        var handleDesktopElements = function () {
            if ($(window).width() > 1280) {
                $(".responsive").each(function () {
                    var forTablet = $(this).attr('data-tablet');
                    var forDesktop = $(this).attr('data-desktop');
                    if (forTablet) {
                        $(this).removeClass(forTablet);
                        $(this).addClass(forDesktop);
                    }
                });
                handleTooltip();
            }
        }

        // handle all elements which require to re-initialize on screen width change(on resize or on rotate mobile device)
        var handleElements = function () {
           /*
            if ($(window).width() < 900) { // remove sidebar toggler
                $.cookie('sidebar-closed', null);
                $('.page-container').removeClass("sidebar-closed");
            }
			*/
            handleTabletElements();
            handleDesktopElements();
        }

        // handles responsive breakpoints.
        $(window).setBreakpoints({
            breakpoints: [320, 480, 768, 900, 1024, 1280]
        });

        $(window).bind('exitBreakpoint320', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint320', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint480', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint480', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint768', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint768', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint900', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint900', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint1024', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint1024', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint1280', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint1280', function () {
            handleElements();
        });
    }
	
	var handleIndex = function() {
		
		var ordered_template = '<div class="btn-group">'+
									'<a class="btn mini green" href="javascript:void(0)" data-toggle="dropdown">'+
										'<i class="icon-black icon-ok"></i>&nbsp;&nbsp;ITEM_ORDERED_M <i class="icon-angle-down"></i>'+
									'</a>'+
									'<ul class="dropdown-menu pull-right">'+
										'<li>'+
											'<a href="javascript:void(0)" class="delete_order">'+
												'<i class="icon-black icon-trash"></i>Delete order'+
											'</a>'+
										'</li>'+
									'</ul>'+
								'</div>';
		
		
		var shipped_template = '<div class="btn-group">'+
									'<a class="btn mini green" href="javascript:void(0)" data-toggle="dropdown">'+
										'<i class="icon-black icon-ok"></i>&nbsp;&nbsp;ITEM_SHIPPED_M <i class="icon-angle-down"></i>'+
									'</a>'+
									'<ul class="dropdown-menu pull-right">'+
										'<li>'+
											'<a href="javascript:void(0)" class="delete_order">'+
												'<i class="icon-black icon-trash"></i>Delete order'+
											'</a>'+
										'</li>'+
									'</ul>'+
								'</div>';
		
		
		var pending_template = '<div class="btn-group">'+
									'<a class="btn mini black" href="javascript:void(0)" data-toggle="dropdown">'+
										'<i class="icon-black icon-time"></i>&nbsp;&nbsp;PENDING <i class="icon-angle-down"></i>'+
									'</a>'+
									'<ul class="dropdown-menu pull-right">'+
										'<li>'+
											'<a href="javascript:void(0)" class="queue_order">'+
												'<i class="icon-black icon-share"></i>Add to queue'+
											'</a>'+
										'</li>'+
										'<li>'+
											'<a href="javascript:void(0)" class="delete_order">'+
												'<i class="icon-black icon-trash"></i>Delete order'+
											'</a>'+
										'</li>'+
									'</ul>'+
								'</div>';
		var queued_template = '<div class="btn-group">'+
								'<a class="btn mini blue" href="javascript:void(0)" data-toggle="dropdown">'+
									'<i class="icon-black icon-signin"></i>&nbsp;&nbsp;QUEUED <i class="icon-angle-down"></i>'+
								'</a>'+
								'<ul class="dropdown-menu pull-right">'+
									'<li>'+
										'<a href="javascript:void(0)" class="suspend_order">'+
											'<i class="icon-black icon-ban-circle"></i>Suspend order'+
										'</a>'+
									'</li>'+
									'<li>'+
										'<a href="javascript:void(0)" class="delete_order">'+
											'<i class="icon-black icon-trash"></i>Delete order'+
										'</a>'+
									'</li>'+
								'</ul>'+
							'</div>';
		var tracking_retry_template = '<div class="btn-group">'+
								'<a class="btn mini green" data-toggle="dropdown" href="javascript:void(0)">'+
									'<i class="icon-black icon-ok"></i>&nbsp;&nbsp;TRACKING_RETRY <i class="icon-angle-down"></i>'+
								'</a>'+
								'<ul class="dropdown-menu pull-right">'+
									'<li>'+
										'<a href="javascript:void(0)" class="retry_order">'+
											'<i class="icon-black icon-refresh"></i>Retry order'+
										'</a>'+
									'</li>'+
									'<li>'+
										'<a href="javascript:void(0)" class="delete_order">'+
											'<i class="icon-black icon-trash"></i>Delete order'+
										'</a>'+
									'</li>'+
								'</ul>'+
							'</div>';
		
		var cancelled_template = '<div class="btn-group">'+
								'<a class="btn mini red" data-toggle="dropdown" href="javascript:void(0)">'+
									'<i class="icon-black icon-ban-circle"></i>&nbsp;&nbsp;CANCELLED <i class="icon-angle-down"></i>'+
								'</a>'+
								'<ul class="dropdown-menu pull-right">'+
									'<li>'+
										'<a href="javascript:void(0)" class="retry_order">'+
											'<i class="icon-black icon-refresh"></i>Retry order'+
										'</a>'+
									'</li>'+
									'<li>'+
										'<a href="javascript:void(0)" class="delete_order">'+
											'<i class="icon-black icon-trash"></i>Delete order'+
										'</a>'+
									'</li>'+
								'</ul>'+
							'</div>';
		
		$(document).keypress(function(e){
			if(e.keyCode==27){
				$(".class_edit").hide();
				$(".class_view").show();
			}
		});
		
		
		$(document).on('blur', '.class_edit', function(){
			if($(this).hasClass('no_blur'))return false;
			$(this).hide();
			$(this).prev('.class_view').show();
		});
		
		$(document).on('click', '.class_view' , function(){
			var no = $(this).attr('rel');
			$(this).hide();
			$(this).next('.class_edit').show().focus();
		});
		
		
		$(document).on('keyup', '.item_sku_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#item_sku_view_'+no).html(val);
			
			$.post('ajax.php', {
				'editSKU' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit sku for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_sku_edit").hide();
			$(".item_sku_view").show();
		});
		
		$(document).on('keyup', '.item_vendor_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#item_vendor_view_'+no).html(val)
			
			$.post('ajax.php', {
				'editVendor' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit vendor for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_vendor_edit").hide();
			$(".item_vendor_view").show();
		});
		
		$(document).on('keyup', '.item_oid_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#item_oid_view_'+no).html(val)
			
			$.post('ajax.php', {
				'editVendorOrderID' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit vendor for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_oid_edit").hide();
			$(".item_oid_view").show();
		});
		
		$(document).on('keyup', '.item_tid_edit_2' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val1 = $("#item_tid_edit_id_"+no).val();
			if(val1.trim() == '')return false;
			
			var val2 = $("#item_tid_edit_tu_"+no).val();
			var val3 = $("#item_tid_edit_tcu_"+no).val();
			
			$('#item_tid_view_'+no).html(val1)
			
			$.post('ajax.php', {
				'editTrackingID' : val1,
				'editTrackingC' : val2,
				'editTrackingCU' : val3,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit vendor for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_tid_edit").hide();
			$(".item_tid_view").show();
		});
		
		$(document).on('keyup', '.item_paid_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#item_paid_view_'+no).html('$'+val)
			
			$.post('ajax.php', {
				'editPaidPrice' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit paid amount for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_paid_edit").hide();
			$(".item_paid_view").show();
		});
		
		$(document).on('keyup', '.item_purchase_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#item_purchase_view_'+no).html('$'+val)
			
			$.post('ajax.php', {
				'editPurchasePrice' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit purchase amount for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_purchase_edit").hide();
			$(".item_purchase_view").show();
		});
		
		$(document).on('keyup', '.item_exp_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#item_exp_view_'+no).html('$'+val)
			
			$.post('ajax.php', {
				'editExpensePrice' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit expense amount for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_exp_edit").hide();
			$(".item_exp_view").show();
		});
		
		$(document).on('keyup', '.item_profit_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#item_profit_view_'+no).html('$'+val)
			
			$.post('ajax.php', {
				'editProfitPrice' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit profit amount for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_profit_edit").hide();
			$(".item_profit_view").show();
		});
		
		$(document).on('keyup', '.item_qty_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#item_qty_view_'+no).html(val)
			
			$.post('ajax.php', {
				'editQty' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit qty amount for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".item_qty_edit").hide();
			$(".item_qty_view").show();
		});
		
		$(document).on('keyup', '.addr_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == ''){
				alert('Address1 cannot be empty');
				return false;
			}
			$('#addr_view_'+no).html(val.replace(/\"/g, '&quot;'))+"<br/>";
			
			$.post('ajax.php', {
				'editAddr' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit address for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".addr_edit").hide();
			$(".addr_view").show();
		});
		
		$(document).on('keyup', '.addr2_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == ''){
				if(!confirm('Are you sure to leave address2 field empty?'))return false;
			}
			$('#addr2_view_'+no).html(val.replace(/\"/g, '&quot;'))+"<br/>";
			
			$.post('ajax.php', {
				'editAddr2' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit address 2 for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".addr2_edit").hide();
			$(".addr2_view").show();
		});
		
		
		$(document).on('keyup', '.note_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			var v = val.replace(/\"/g, '&quot;');
			v = v.replace(/</g, '&lt;');
			v = v.replace(/>/g, '&gt;');
			
			$('#note_view_'+no).html(v);
			
			$.post('ajax.php', {
				'editNote' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit notes for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".note_edit").hide();
			$(".note_view").show();
		});
		
		$(document).on('keyup', '.name_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#name_view_'+no).html(val.replace(/\"/g, '&quot;'));
			
			$.post('ajax.php', {
				'editName' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit name for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".name_edit").hide();
			$(".name_view").show();
		});
		
		$(document).on('keyup', '.city_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#city_view_'+no).html(val.replace(/\"/g, '&quot;'));
			
			$.post('ajax.php', {
				'editCity' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit address for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".city_edit").hide();
			$(".city_view").show();
		});
		
		$(document).on('keyup', '.state_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#state_view_'+no).html(val.replace(/\"/g, '&quot;'));
			
			$.post('ajax.php', {
				'editState' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit address for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".state_edit").hide();
			$(".state_view").show();
		});
		
		$(document).on('keyup', '.pscode_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#pscode_view_'+no).html(val.replace(/\"/g, '&quot;'));
			
			$.post('ajax.php', {
				'editPS' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit address for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".pscode_edit").hide();
			$(".pscode_view").show();
		});
		
		$(document).on('keyup', '.phone_edit' , function(event){
			var elem = $(this);
			var no = $(this).attr('rel');
			if(event.keyCode != 13)return false;
			
			var val = $(this).val();
			if(val.trim() == '')return false;
			$('#phone_view_'+no).html(val.replace(/\"/g, '&quot;'));
			
			$.post('ajax.php', {
				'editPhone' : val,
				'orderNo' : no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert('Failed to edit phone for orderno '+ no + '. ' + data.error)	
					}
					else elem.parent().parent().effect("highlight", {}, 5000);
			});
			
			
			$(".phone_edit").hide();
			$(".phone_view").show();
		});
		
		$(".leftArrow").click(function(){
			left = parseInt($("#main-listing").scrollLeft()) - 800;
			$("#main-listing").animate({scrollLeft: left}, 200);
		});
		
		$(".rightArrow").click(function(){
			left = parseInt($("#main-listing").scrollLeft()) + 800;
			$("#main-listing").animate({scrollLeft: left}, 200);
		});
		
		$(".upArrow").click(function(){
			$('body,html').animate({scrollTop: 0}, 500);
		});
		
		$(".downArrow").click(function(){
			$('body,html').animate({scrollTop: $(document).height()}, 500);
		});
		
		$(document).on('click', '.process_seller_list' , function(){
			var selected_records = '';
			var arr = [];
			$('.sales-record').each(function(index, value){
				var parent = $(this).parent();
				if(parent.hasClass('checked')){
					arr.push($(this).val());
					selected_records += $(this).val() + ',';
				}	
			});
			if(selected_records == '')return alert('No record selected');
			if(!confirm('Are you sure?'))return false;
			
			$.post('ajax.php', {
				'queueOrder' : selected_records	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						for(i = 0; i < arr.length; i++){
							$('.ajax-feedback-'+arr[i]).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
						}
					}
					else{
						for(i = 0; i < arr.length; i++){
							if($('.status-'+arr[i]).parent().attr('data-drop-done') != 0)continue;
							$('.ajax-feedback-'+arr[i]).html('');
							$('.status-'+arr[i]).html(queued_template);
						}
					}
			});
		});
		
		
		$(document).on('click', '.manualAddOrder' , function(){
			var data = $('#manualAddOrderForm').serialize();
			$.post('ajax.php', data, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						alert(data.error);
					}
					else{
						window.location.reload();
					}
			});
		});
		
		$(document).on('click', '.manualImportEbayOrder' , function(){
			var ebayAcc = $('#ebayAcc').val();
			var orders = $('#ebayOrderIds').val();
			if(ebayAcc == '' || orders == '')return alert('Select an Ebay account/put some orders first');
			$('.impTab').hide();
			$('.impActTab').show();
			$.post('ajax.php', {
					'ebayAcc' : ebayAcc,
					'orderIds' : orders
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						$('.impTab').show();
						$('.impActTab').hide();
						alert(data.error);
					}
					else{
						window.location.reload();
					}
			});
		});
		
		$(document).on('click', '.manualImportAmazonOrder' , function(){
			var amazonAcc = $('#amazonAcc').val();
			var orders = $('#amazonOrderIds').val();
			if(amazonAcc == '' || orders == '')return alert('Select an Amazon account/put some orders first');
			$('.impTab').hide();
			$('.impActTab').show();
			$.post('ajax.php', {
					'amazonAcc' : amazonAcc,
					'orderIds' : orders
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						$('.impTab').show();
						$('.impActTab').hide();
						alert(data.error);
					}
					else{
						window.location.reload();
					}
			});
		});
		
		
		$(document).on('click', '.suspend_seller_list' , function(){
			var selected_records = '';
			var arr = [];
			$('.sales-record').each(function(index, value){
				var parent = $(this).parent();
				if(parent.hasClass('checked')){
					arr.push($(this).val());
					selected_records += $(this).val() + ',';
				}	
			});
			if(selected_records == '')return alert('No record selected');
			if(!confirm('Are you sure?'))return false;
			
			$.post('ajax.php', {
				'suspendOrder' : selected_records	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						for(i = 0; i < arr.length; i++){
							$('.ajax-feedback-'+arr[i]).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
						}
					}
					else{
						for(i = 0; i < arr.length; i++){
							if($('.status-'+arr[i]).parent().attr('data-drop-done') != 0)continue;
							$('.ajax-feedback-'+arr[i]).html('');
							$('.status-'+arr[i]).html(pending_template);
						}
					}
			});
		});
		
		$(document).on('click', '.retry_seller_list' , function(){
			var selected_records = '';
			var arr = [];
			$('.sales-record').each(function(index, value){
				var parent = $(this).parent();
				if(parent.hasClass('checked')){
					arr.push($(this).val());
					selected_records += $(this).val() + ',';
				}	
			});
			if(selected_records == '')return alert('No record selected');
			if(!confirm('Are you sure?'))return false;
			
			$.post('ajax.php', {
				'retryOrder' : selected_records	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						for(i = 0; i < arr.length; i++){
							$('.ajax-feedback-'+arr[i]).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
						}
					}
					else{
						for(i = 0; i < arr.length; i++){
							var status = $('.status-'+arr[i]).parent().attr('data-drop-done');
							if(status != 100 && status != 200)continue;
							$('.ajax-feedback-'+arr[i]).html('');
							if(status == 100)$('.status-'+arr[i]).html(queued_template);
							else $('.status-'+arr[i]).html(tracking_retry_template);
						}
					}
			});
		});
		
		$(document).on('click', '.del_seller_list' , function(){
			var selected_records = '';
			var arr = [];
			if(!confirm('Are you sure?'))return false;
			$('.sales-record').each(function(index, value){
				var parent = $(this).parent();
				if(parent.hasClass('checked')){
					selected_records += $(this).val() + ',';
					arr.push($(this).val());
					$('.ajax-feedback-'+$(this).val()).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
				}	
			});
			if(selected_records == '')return alert('No record selected');	
			
			$.post('ajax.php', {
				'delOrder' : selected_records	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						for(i = 0; i < arr.length; i++){
							$('.ajax-feedback-'+arr[i]).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
						}
					}
					else{
						for(i = 0; i < arr.length; i++){
							$('#record-'+arr[i]).html('<td colspan="20"><div class="alert alert-error">Sales record deleted</div></td>');
						}
					}
			});
			
		});
		
		$(document).on('click', '.export_seller_list' , function(){
			var selected_records = '';
			$('.sales-record').each(function(index, value){
				var parent = $(this).parent();
				if(parent.hasClass('checked')){
					selected_records += $(this).val() + ',';
				}	
			});
			if(selected_records == '')return alert('No record selected');	
			$('#export_id').val(selected_records);
			$('#exportOrderIDs').submit();
		});
			
		$(document).on('click', '.queue_order' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'queueOrder' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						$('.ajax-feedback-'+order_no).html('');
						$('.status-'+order_no).html(queued_template);
					}
			});	
		});
		
		$(document).on('click', '.suspend_order' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'suspendOrder' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						$('.ajax-feedback-'+order_no).html('');
						$('.status-'+order_no).html(pending_template);
					}
			});	
		});
		
		$(document).on('click', '.retry_order' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'retryOrder' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						var status = $('.status-'+order_no).parent().attr('data-drop-done');
						$('.ajax-feedback-'+order_no).html('');
						if(status == 100)$('.status-'+order_no).html(queued_template);
						else $('.status-'+order_no).html(tracking_retry_template);
					}
			});	
		});
		
		$(document).on('click', '.mark_ordered' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'markOrdered' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						$('.status-'+order_no).html(ordered_template);
					}
			});	
		});
		
		$(document).on('click', '.mark_shipped' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'markShipped' : order_no	
				}, function(response){
					$('.ajax-feedback-'+order_no).html('');
					var data = $.parseJSON(response);
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						$('.status-'+order_no).html(shipped_template);
					}
			});	
		});
		
		$(document).on('click', '.mark_cancelled' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'markCancelled' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						$('.status-'+order_no).html(cancelled_template);
					}
			});	
		});
		
		$(document).on('click', '.delete_order' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			if(!confirm('Are you sure to delete order no #'+order_no+'?'))return false;
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'delOrder' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						$('#record-'+order_no).html('<td colspan="20"><div class="alert alert-error">Sales record deleted</div></td>');
					}
			});	
		});
		
		$(document).on('click', '.ignore_loss' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			var elem = $(this);
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'ignoreLoss' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						var status = $('.status-'+order_no).parent().attr('data-drop-done');
						$('.ajax-feedback-'+order_no).html('');
						if(status == 100)$('.status-'+order_no).html(queued_template);
						else if(status == 200)$('.status-'+order_no).html(tracking_retry_template);
						elem.html('<i class="icon-black icon-undo"></i>&nbsp;&nbsp;Undo ignore loss');
						elem.removeClass('ignore_loss');
						elem.addClass('undo_ignore_loss');
					}
			});	
		});
		
		$(document).on('click', '.undo_ignore_loss' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			var elem = $(this);
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'UndoIgnoreLoss' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						elem.html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;Ignore loss');
						elem.removeClass('undo_ignore_loss');
						elem.addClass('ignore_loss');
					}
			});	
		});

		$(document).on('click', '.ignore_tax' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			var elem = $(this);
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'ignoreTax' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						var status = $('.status-'+order_no).parent().attr('data-drop-done');
						$('.ajax-feedback-'+order_no).html('');
						if(status == 100)$('.status-'+order_no).html(queued_template);
						else if(status == 200)$('.status-'+order_no).html(tracking_retry_template);
						elem.html('<i class="icon-black icon-undo"></i>&nbsp;&nbsp;Undo ignore tax');
						elem.removeClass('ignore_tax');
						elem.addClass('undo_ignore_tax');
					}
			});	
		});
		
		$(document).on('click', '.undo_ignore_tax' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			var elem = $(this);
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'UndoIgnoreTax' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						elem.html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;Ignore tax');
						elem.removeClass('undo_ignore_tax');
						elem.addClass('ignore_tax');
					}
			});	
		});
		
		$(document).on('click', '.retry_tracking_up' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			var elem = $(this);
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'TrackingRetry' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						var status = $('.status-'+order_no).parent().attr('data-drop-done');
						$('.ajax-feedback-'+order_no).html('Order will be retried for updating shipping status');
						$('.status-'+order_no).html(tracking_retry_template);
						//elem.html('<i class="icon-black icon-undo"></i>&nbsp;&nbsp;Undo ignore tax');
						//elem.removeClass('ignore_tax');
						//elem.addClass('undo_ignore_tax');
					}
			});	
		});

				
		$(document).on('click', '.ignore_price' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			var elem = $(this);
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'ignorePrice' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						var status = $('.status-'+order_no).parent().attr('data-drop-done');
						$('.ajax-feedback-'+order_no).html('');
						if(status == 100)$('.status-'+order_no).html(queued_template);
						else if(status == 200)$('.status-'+order_no).html(tracking_retry_template);
						elem.html('<i class="icon-black icon-undo"></i>&nbsp;&nbsp;Undo ignore price');
						elem.removeClass('ignore_price');
						elem.addClass('undo_ignore_price');
					}
			});	
		});
		
		$(document).on('click', '.undo_ignore_price' , function(){
			var order_no = $(this).parent().parent().parent().parent().parent().attr('rel');
			var elem = $(this);
			$('.ajax-feedback-'+order_no).html('Please wait...&nbsp;&nbsp;<img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'UndoIgnorePrice' : order_no	
				}, function(response){
					var data = $.parseJSON(response);
					$('.ajax-feedback-'+order_no).html('');
					if(data.error != ''){
						$('.ajax-feedback-'+order_no).html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;' + data.error);
					}
					else{
						elem.html('<i class="icon-black icon-remove"></i>&nbsp;&nbsp;Ignore price');
						elem.removeClass('undo_ignore_price');
						elem.addClass('ignore_price');
					}
			});	
		});
		
		$('.module-stats').find('.last-up').each(function(){
			if($(this).html() == '0 min ago')$(this).html('Running');
		});
		
	}
	
	var handleToken = function() {
		$(document).on('click', '.actoken' , function(){
			var elem = $(this).parent().parent();
			elem.slideUp();
			$('.tokenizer-status').html('<h5>Please wait...</h5><img src="assets/img/loading.gif"/>');
			$.post('ajax.php', {
				'initEbayTokenSession' : 1	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error != ''){
						elem.slideDown();
						$('.tokenizer-status').html('<div class="alert alert-error">'+data.error+'</div>');	
					}
					else{
						$('.tokenizer-status').html('<div class="alert alert-block alert-success fade in"><button type="button" class="close" data-dismiss="alert"></button><h4 class="alert-heading">Success!</h4><p>First authorize your ebay account to use this app.<br/> After authorization click <b>I authorized!</b> button</p><p><a class="btn green auth_button" href="'+data.url+'" target="_blank" onclick="$(this).hide();$(\'.auth_done_button\').show();">Authorize account</a> <a class="btn red auth_done_button" href="javascript:void(0)" style="display:none" rel="'+data.SessionID[0]+'">I authorized!</a><span class="auth_loading_feedback" style="display: none"><img src="assets/img/loading.gif"/></span></p></div>');
					}
			});	
		});
		
		$(document).on('click', '.auth_done_button' , function(){
			var SessionID = $(this).attr('rel');
			var elem = $(this);
			elem.slideUp("fast", function(){$('.auth_loading_feedback').show();});
			$.post('ajax.php', {
				'authEbaySession' : SessionID	
				}, function(response){
					$('.auth_loading_feedback').hide();
					elem.slideDown();
					var data = $.parseJSON(response);
					$('.auth_error').remove();
					if(data.error != ''){
						$('.tokenizer-status').append('<div class="alert alert-error auth_error">'+data.error+'</div>');	
					}
					else{
						$('.tokenizer-status').html('<div class="alert alert-success auth_error">'+data.data+'</div>');
						$('.actoken').parent().parent().slideDown();
					}
			});	
		});				
	}
	
	var handleCharts = function() {
		
			$('.custom-date-picker').datepicker().on('changeDate', function(){document.forms[0].submit()});
		
            var plot = $.plot($("#chart_sales"), [{
                data: daily_sales,
                label: "Daily Sales"
            }], {
                series: {
                    lines: {
                        show: true,
                        lineWidth: 2,
                        fill: true,
                        fillColor: {
                            colors: [{
                                opacity: 0.05
                            }, {
                                opacity: 0.01
                            }]
                        }
                    },
                    points: {
                        show: true
                    },
                    shadowSize: 2
                },
                grid: {
                    hoverable: true,
                    clickable: true,
                    tickColor: "#eee",
                    borderWidth: 0
                },
                colors: ["#d12610", "#37b7f3", "#52e136"],
                xaxis: {
					mode:"time",
      				timeformat: "%d-%b-%y",
                },
                yaxis: {
                }
         });
		 
		 var plot = $.plot($("#chart_profit"), [{
                data: daily_profit,
                label: "Daily Profit"
            }], {
                series: {
                    lines: {
                        show: true,
                        lineWidth: 2,
                        fill: true,
                        fillColor: {
                            colors: [{
                                opacity: 0.05
                            }, {
                                opacity: 0.01
                            }]
                        }
                    },
                    points: {
                        show: true
                    },
                    shadowSize: 2
                },
                grid: {
                    hoverable: true,
                    clickable: true,
                    tickColor: "#eee",
                    borderWidth: 0
                },
                colors: ["#909cae"],
                xaxis: {
					mode:"time",
      				timeformat: "%d-%b-%y",
                },
                yaxis: {
                }
         });
		 
		 
		var previousPoint = null;
		$("#chart_sales, #chart_profit").bind("plothover", function (event, pos, item) {
			$("#x").text(pos.x.toFixed(2));
			$("#y").text(pos.y.toFixed(2));
	
			if (item) {
				if (previousPoint != item.dataIndex) {
					previousPoint = item.dataIndex;
	
					$("#tooltip").remove();
					var x = getdate(item.datapoint[0]),
						y = item.datapoint[1].toFixed(2);
	
					showTooltip(item.pageX, item.pageY, item.series.label + " of " + x + " = " + y);
				}
			} else {
				$("#tooltip").remove();
				previousPoint = null;
			}
		});
	}
	
	var handleSettings = function(){
		$(document).on('click', '.del_ebay_id', function(){
			var id = $(this).attr('rel');
			var elem = $(this);
			$.post('ajax.php', {
				'delEbayID' : id	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error == ''){
						elem.parent().html('<i class="icon-ok"></i>&nbsp;&nbsp;&nbsp;User deleted');
					}
					else{
						elem.parent().html('<i class="icon-remove"></i>&nbsp;&nbsp;&nbsp;'+data.error);
					}
			});
		});
		
		$(document).on('click', '.del_am_id', function(){
			var id = $(this).attr('rel');
			var elem = $(this);
			$.post('ajax.php', {
				'delAmID' : id	
				}, function(response){
					var data = $.parseJSON(response);
					if(data.error == ''){
						elem.parent().html('<i class="icon-ok"></i>&nbsp;&nbsp;&nbsp;User deleted');
					}
					else{
						elem.parent().html('<i class="icon-remove"></i>&nbsp;&nbsp;&nbsp;'+data.error);
					}
			});
		});	
	}
	
	function showTooltip(x, y, contents) {
		$('<div id="tooltip">' + contents + '</div>').css({
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 15,
			border: '1px solid #333',
			padding: '4px',
			color: '#fff',
			'border-radius': '3px',
			'background-color': '#333',
			opacity: 0.80,
		}).appendTo("body").fadeIn(200);
	}

	function getdate (timestamp) {
		var _w = ['Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur'];
		var _m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		var d = ((typeof timestamp === 'undefined') ? new Date() : // Not provided
		(typeof timestamp === 'object') ? new Date(timestamp) : // Javascript Date()
		new Date(timestamp) // UNIX timestamp (auto-convert to int)
		);
		var w = d.getDay();
		var m = d.getMonth();
		var y = d.getFullYear();
		var r = {};
		
		r.seconds = d.getSeconds();
		r.minutes = d.getMinutes();
		r.hours = d.getHours();
		r.mday = d.getDate();
		r.wday = w;
		r.mon = m + 1;
		r.year = y;
		r.yday = Math.floor((d - (new Date(y, 0, 1))) / 86400000);
		r.weekday = _w[w] + 'day';
		r.month = _m[m];
		r['0'] = parseInt(d.getTime() / 1000, 10);
		
		return r.mday + '-' + r.month + '-' + r.year;
	}

    var handleMainMenu = function () {
        jQuery('.page-sidebar .has-sub > a').click(function () {

            var handleContentHeight = function () {
                var content = $('.page-content');
                var sidebar = $('.page-sidebar');

                if (!content.attr("data-height")) {
                    content.attr("data-height", content.height());
                }


                if (sidebar.height() > content.height()) {
                    content.css("min-height", sidebar.height() + 20);
                } else {
                    content.css("min-height", content.attr("data-height"));
                }
            }

            var last = jQuery('.has-sub.open', $('.page-sidebar'));
            if (last.size() == 0) {
                //last = jQuery('.has-sub.active', $('.page-sidebar'));
            }
            last.removeClass("open");
            jQuery('.arrow', last).removeClass("open");
            jQuery('.sub', last).slideUp(200);

            var sub = jQuery(this).next();
            if (sub.is(":visible")) {
                jQuery('.arrow', jQuery(this)).removeClass("open");
                jQuery(this).parent().removeClass("open");
                sub.slideUp(200, function () {
                    handleContentHeight();
                });
            } else {
                jQuery('.arrow', jQuery(this)).addClass("open");
                jQuery(this).parent().addClass("open");
                sub.slideDown(200, function () {
                    handleContentHeight();
                });
            }
        });
    }

    var handleSidebarToggler = function () {

        var container = $(".page-container");

		/*
        if ($.cookie('sidebar-closed') == 1) {
            container.addClass("sidebar-closed");
        }
		*/

        // handle sidebar show/hide
        $('.page-sidebar .sidebar-toggler').click(function () {
            $(".sidebar-search").removeClass("open");
            var container = $(".page-container");
            if (container.hasClass("sidebar-closed") === true) {
                container.removeClass("sidebar-closed");
                //$.cookie('sidebar-closed', null);
            } else {
                container.addClass("sidebar-closed");
                //$.cookie('sidebar-closed', 1);
            }
            if (App.isPage("charts")) {
                setTimeout(function () {
                    handleChartGraphs();
                }, 100);
            }
        });

        // handle the search bar close
        $('.sidebar-search .remove').click(function () {
            $('.sidebar-search').removeClass("open");
        });

        // handle the search query submit on enter press
        $('.sidebar-search input').keypress(function (e) {
            if (e.which == 13) {
                window.location.href = "extra_search.html";
                return false; //<---- Add this line
            }
        });

        // handle the search submit
        $('.sidebar-search .submit').click(function () {
            if ($('.page-container').hasClass("sidebar-closed")) {
                if ($('.sidebar-search').hasClass('open') == false) {
                    $('.sidebar-search').addClass("open");
                } else {
                    window.location.href = "extra_search.html";
                }
            } else {
                window.location.href = "extra_search.html";
            }
        });
    }

    var handlePortletTools = function () {
        jQuery('.portlet .tools a.remove').click(function () {
            var removable = jQuery(this).parents(".portlet");
            if (removable.next().hasClass('portlet') || removable.prev().hasClass('portlet')) {
                jQuery(this).parents(".portlet").remove();
            } else {
                jQuery(this).parents(".portlet").parent().remove();
            }
        });

        jQuery('.portlet .tools a.reload').click(function () {
            var el = jQuery(this).parents(".portlet");
            App.blockUI(el);
            window.setTimeout(function () {
                App.unblockUI(el);
            }, 1000);
        });

        jQuery('.portlet .tools .collapse, .portlet .tools .expand').click(function () {
            var el = jQuery(this).parents(".portlet").children(".portlet-body");
            if (jQuery(this).hasClass("collapse")) {
                jQuery(this).removeClass("collapse").addClass("expand");
                el.slideUp(200);
            } else {
                jQuery(this).removeClass("expand").addClass("collapse");
                el.slideDown(200);
            }
        });

        /*
        sample code to handle portlet config popup on close
        $('#portlet-config').on('hide', function (e) {
            //alert(1);
            //if (!data) return e.preventDefault() // stops modal from being shown
        });
        */
    }

    var handlePortletSortable = function () {
        if (!jQuery().sortable) {
            return;
        }

        $("#sortable_portlets").sortable({
            connectWith: ".portlet",
            items: ".portlet",
            opacity: 0.8,
            coneHelperSize: true,
            placeholder: 'sortable-box-placeholder round-all',
            forcePlaceholderSize: true,
            tolerance: "pointer"
        });

        $(".column").disableSelection();

	}
	
    var handleFancyBox = function () {

        if (!jQuery.fancybox) {
            return;
        }

        if (jQuery(".fancybox-button").size() > 0) {
            jQuery(".fancybox-button").fancybox({
                groupAttr: 'data-rel',
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: true,
                helpers: {
                    title: {
                        type: 'inside'
                    }
                }
            });
        }
    }

    var handleLoginForm = function () {

        $('.login-form').validate({
            errorElement: 'label', //default input error message container
            errorClass: 'help-inline', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            rules: {
                username: {
                    required: true
                },
                password: {
                    required: true
                },
                remember: {
                    required: false
                }
            },

            messages: {
                username: {
                    required: "Username is required."
                },
                password: {
                    required: "Password is required."
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit   
                $('.alert-error', $('.login-form')).show();
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.control-group').addClass('error'); // set error class to the control group
            },

            success: function (label) {
                label.closest('.control-group').removeClass('error');
                label.remove();
            },

            errorPlacement: function (error, element) {
                error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
            },

            submitHandler: function (form) {
                window.location.href = "index.html";
            }
        });

        $('.login-form input').keypress(function (e) {
            if (e.which == 13) {
                if ($('.login-form').validate().form()) {
                    window.location.href = "index.html";
                }
                return false;
            }
        });

        $('.forget-form').validate({
            errorElement: 'label', //default input error message container
            errorClass: 'help-inline', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",
            rules: {
                email: {
                    required: true,
                    email: true
                }
            },

            messages: {
                email: {
                    required: "Email is required."
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit   

            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.control-group').addClass('error'); // set error class to the control group
            },

            success: function (label) {
                label.closest('.control-group').removeClass('error');
                label.remove();
            },

            errorPlacement: function (error, element) {
                error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
            },

            submitHandler: function (form) {
                window.location.href = "index.html";
            }
        });

        $('.forget-form input').keypress(function (e) {
            if (e.which == 13) {
                if ($('.forget-form').validate().form()) {
                    window.location.href = "index.html";
                }
                return false;
            }
        });

        jQuery('#forget-password').click(function () {
            jQuery('.login-form').hide();
            jQuery('.forget-form').show();
        });

        jQuery('#back-btn').click(function () {
            jQuery('.login-form').show();
            jQuery('.forget-form').hide();
        });

        $('.register-form').validate({
            errorElement: 'label', //default input error message container
            errorClass: 'help-inline', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",
            rules: {
                username: {
                    required: true
                },
                password: {
                    required: true
                },
                rpassword: {
                    equalTo: "#register_password"
                },
                email: {
                    required: true,
                    email: true
                },
                tnc: {
                    required: true
                }
            },

            messages: { // custom messages for radio buttons and checkboxes
                tnc: {
                    required: "Please accept TNC first."
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit   

            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.control-group').addClass('error'); // set error class to the control group
            },

            success: function (label) {
                label.closest('.control-group').removeClass('error');
                label.remove();
            },

            errorPlacement: function (error, element) {
                if (element.attr("name") == "tnc") { // insert checkbox errors after the container                  
                    error.addClass('help-small no-left-padding').insertAfter($('#register_tnc_error'));
                } else {
                    error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
                }
            },

            submitHandler: function (form) {
                window.location.href = "index.html";
            }
        });

        jQuery('#register-btn').click(function () {
            jQuery('.login-form').hide();
            jQuery('.register-form').show();
        });

        jQuery('#register-back-btn').click(function () {
            jQuery('.login-form').show();
            jQuery('.register-form').hide();
        });
    }

    var handleFixInputPlaceholderForIE = function () {
        //fix html5 placeholder attribute for ie7 & ie8
        if (jQuery.browser.msie && jQuery.browser.version.substr(0, 1) <= 9) { // ie7&ie8

            // this is html5 placeholder fix for inputs, inputs with placeholder-no-fix class will be skipped(e.g: we need this for password fields)
            jQuery('input[placeholder]:not(.placeholder-no-fix), textarea[placeholder]:not(.placeholder-no-fix)').each(function () {

                var input = jQuery(this);

                jQuery(input).addClass("placeholder").val(input.attr('placeholder'));

                jQuery(input).focus(function () {
                    if (input.val() == input.attr('placeholder')) {
                        input.val('');
                    }
                });

                jQuery(input).blur(function () {
                    if (input.val() == '' || input.val() == input.attr('placeholder')) {
                        input.val(input.attr('placeholder'));
                    }
                });
            });
        }
    }

    var handlePulsate = function () {
        if (!jQuery().pulsate) {
            return;
        }

        if (isIE8 == true) {
            return; // pulsate plugin does not support IE8 and below
        }

        if (jQuery().pulsate) {
            jQuery('#pulsate-regular').pulsate({
                color: "#bf1c56"
            });

            jQuery('#pulsate-once').click(function () {
                $(this).pulsate({
                    color: "#399bc3",
                    repeat: false
                });
            });

            jQuery('#pulsate-hover').pulsate({
                color: "#5ebf5e",
                repeat: false,
                onHover: true
            });

            jQuery('#pulsate-crazy').click(function () {
                $(this).pulsate({
                    color: "#fdbe41",
                    reach: 50,
                    repeat: 10,
                    speed: 100,
                    glow: true
                });
            });
        }
    }
	
	var handleGritterNotifications = function () {
        if (!jQuery.gritter) {
            return;
        }
        $('#gritter-sticky').click(function () {
            var unique_id = $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a sticky notice!',
                // (string | mandatory) the text inside the notification
                text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus eget tincidunt velit. Cum sociis natoque penatibus et <a href="#" style="color:#ccc">magnis dis parturient</a> montes, nascetur ridiculus mus.',
                // (string | optional) the image to display on the left
                image: './assets/img/avatar1.jpg',
                // (bool | optional) if you want it to fade out on its own or just sit there
                sticky: true,
                // (int | optional) the time you want it to be alive for before fading out
                time: '',
                // (string | optional) the class name you want to apply to that specific message
                class_name: 'my-sticky-class'
            });
            return false;
        });

        $('#gritter-regular').click(function () {

            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a regular notice!',
                // (string | mandatory) the text inside the notification
                text: 'This will fade out after a certain amount of time. Vivamus eget tincidunt velit. Cum sociis natoque penatibus et <a href="#" style="color:#ccc">magnis dis parturient</a> montes, nascetur ridiculus mus.',
                // (string | optional) the image to display on the left
                image: './assets/img/avatar1.jpg',
                // (bool | optional) if you want it to fade out on its own or just sit there
                sticky: false,
                // (int | optional) the time you want it to be alive for before fading out
                time: ''
            });

            return false;

        });

        $('#gritter-max').click(function () {

            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a notice with a max of 3 on screen at one time!',
                // (string | mandatory) the text inside the notification
                text: 'This will fade out after a certain amount of time. Vivamus eget tincidunt velit. Cum sociis natoque penatibus et <a href="#" style="color:#ccc">magnis dis parturient</a> montes, nascetur ridiculus mus.',
                // (string | optional) the image to display on the left
                image: './assets/img/avatar1.jpg',
                // (bool | optional) if you want it to fade out on its own or just sit there
                sticky: false,
                // (function) before the gritter notice is opened
                before_open: function () {
                    if ($('.gritter-item-wrapper').length == 3) {
                        // Returning false prevents a new gritter from opening
                        return false;
                    }
                }
            });
            return false;
        });

        $('#gritter-without-image').click(function () {
            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a notice without an image!',
                // (string | mandatory) the text inside the notification
                text: 'This will fade out after a certain amount of time. Vivamus eget tincidunt velit. Cum sociis natoque penatibus et <a href="#" style="color:#ccc">magnis dis parturient</a> montes, nascetur ridiculus mus.'
            });

            return false;
        });

        $('#gritter-light').click(function () {

            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a light notification',
                // (string | mandatory) the text inside the notification
                text: 'Just add a "gritter-light" class_name to your $.gritter.add or globally to $.gritter.options.class_name',
                class_name: 'gritter-light'
            });

            return false;
        });

        $("#gritter-remove-all").click(function () {

            $.gritter.removeAll();
            return false;

        });
    }

	var handleToggleButtons = function () {
        if (!jQuery().toggleButtons) {
            return;
        }
        $('.basic-toggle-button').toggleButtons();
        $('.text-toggle-button').toggleButtons({
            width: 200,
            label: {
                enabled: "Lorem Ipsum",
                disabled: "Dolor Sit"
            }
        });
        $('.danger-toggle-button').toggleButtons({
            style: {
                // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
                enabled: "danger",
                disabled: "info"
            }
        });
        $('.info-toggle-button').toggleButtons({
            style: {
                enabled: "info",
                disabled: ""
            }
        });
        $('.success-toggle-button').toggleButtons({
            style: {
                enabled: "success",
                disabled: "info"
            }
        });
        $('.warning-toggle-button').toggleButtons({
            style: {
                enabled: "warning",
                disabled: "info"
            }
        });

        $('.height-toggle-button').toggleButtons({
            height: 100,
            font: {
                'line-height': '100px',
                'font-size': '20px',
                'font-style': 'italic'
            }
        });
    }


    var handleTooltip = function () {
        if (App.isTouchDevice()) { // if touch device, some tooltips can be skipped in order to not conflict with click events
            jQuery('.tooltips:not(.no-tooltip-on-touch-device)').tooltip();
        } else {
            jQuery('.tooltips').tooltip();
        }
    }

    var handlePopover = function () {
        jQuery('.popovers').popover();
    }

    var handleChoosenSelect = function () {
        if (!jQuery().chosen) {
            return;
        }
        $(".chosen").chosen();

        $(".chosen-with-diselect").chosen({
            allow_single_deselect: true
        })
    }

    var initChosenSelect = function (els) {
        $(els).chosen({
            allow_single_deselect: true
        })
    }

    var handleUniform = function () {
        if (!jQuery().uniform) {
            return;
        }
        var test = $("input[type=checkbox]:not(.toggle), input[type=radio]:not(.toggle, .star)");
        if (test) {
            test.uniform();
        }
    }

    var initUniform = function (els) {
        jQuery(els).each(function () {
            if ($(this).parents(".checker").size() == 0) {
                $(this).show();
                $(this).uniform();
            }
        });
    }

    var handleWysihtml5 = function () {
        if (!jQuery().wysihtml5) {
            return;
        }

        if ($('.wysihtml5').size() > 0) {
            $('.wysihtml5').wysihtml5();
        }
    }
	
    
    var handleTagsInput = function () {
        if (!jQuery().tagsInput) {
            return;
        }
        $('#tags_1').tagsInput({
            width: 'auto',
            'onAddTag': function () {
                alert(1);
            },
        });
        $('#tags_2').tagsInput({
            width: 240
        });
    }

    var handleDateTimePickers = function () {

        if (jQuery().datepicker) {
            $('.date-picker').datepicker();
        }

        if (jQuery().timepicker) {
            $('.timepicker-default').timepicker();
            $('.timepicker-24').timepicker({
                minuteStep: 1,
                showSeconds: true,
                showMeridian: false
            });
        }

        if (!jQuery().daterangepicker) {
            return;
        }

        $('.date-range').daterangepicker();

        $('#dashboard-report-range').daterangepicker({
            ranges: {
                'Today': ['today', 'today'],
                'Yesterday': ['yesterday', 'yesterday'],
                'Last 7 Days': [Date.today().add({
                    days: -6
                }), 'today'],
                'Last 30 Days': [Date.today().add({
                    days: -29
                }), 'today'],
                'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                'Last Month': [Date.today().moveToFirstDayOfMonth().add({
                    months: -1
                }), Date.today().moveToFirstDayOfMonth().add({
                    days: -1
                })]
            },
            opens: 'left',
            format: 'MM/dd/yyyy',
            separator: ' to ',
            startDate: Date.today().add({
                days: -29
            }),
            endDate: Date.today(),
            minDate: '01/01/2012',
            maxDate: '12/31/2014',
            locale: {
                applyLabel: 'Submit',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom Range',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            showWeekNumbers: true,
            buttonClasses: ['btn-danger']
        },

        function (start, end) {
            App.blockUI(jQuery("#dashboard"));
            setTimeout(function () {
                App.unblockUI(jQuery("#dashboard"));
                $.gritter.add({
                    title: 'Dashboard',
                    text: 'Dashboard date range updated.'
                });
                App.scrollTo();
            }, 1000);
            $('#dashboard-report-range span').html(start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy'));

        });

        $('#dashboard-report-range').show();

        $('#dashboard-report-range span').html(Date.today().add({
            days: -29
        }).toString('MMMM d, yyyy') + ' - ' + Date.today().toString('MMMM d, yyyy'));

        $('#form-date-range').daterangepicker({
            ranges: {
                'Today': ['today', 'today'],
                'Yesterday': ['yesterday', 'yesterday'],
                'Last 7 Days': [Date.today().add({
                    days: -6
                }), 'today'],
                'Last 30 Days': [Date.today().add({
                    days: -29
                }), 'today'],
                'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                'Last Month': [Date.today().moveToFirstDayOfMonth().add({
                    months: -1
                }), Date.today().moveToFirstDayOfMonth().add({
                    days: -1
                })]
            },
            opens: 'right',
            format: 'MM/dd/yyyy',
            separator: ' to ',
            startDate: Date.today().add({
                days: -29
            }),
            endDate: Date.today(),
            minDate: '01/01/2012',
            maxDate: '12/31/2014',
            locale: {
                applyLabel: 'Submit',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom Range',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            showWeekNumbers: true,
            buttonClasses: ['btn-danger']
        },

        function (start, end) {
            $('#form-date-range span').html(start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy'));
        });

        $('#form-date-range span').html(Date.today().add({
            days: -29
        }).toString('MMMM d, yyyy') + ' - ' + Date.today().toString('MMMM d, yyyy'));


        if (!jQuery().datepicker || !jQuery().timepicker) {
            return;
        }
    }

    var handleClockfaceTimePickers = function () {

        if (!jQuery().clockface) {
            return;
        }

        $('#clockface_1').clockface();

        $('#clockface_2').clockface({
            format: 'HH:mm',
            trigger: 'manual'
        });

        $('#clockface_2_toggle-btn').click(function (e) {
            e.stopPropagation();
            $('#clockface_2').clockface('toggle');
        });

        $('#clockface_3').clockface({
            format: 'H:mm'
        }).clockface('show', '14:30');
    }

    var handleColorPicker = function () {
        if (!jQuery().colorpicker) {
            return;
        }
        $('.colorpicker-default').colorpicker({
            format: 'hex'
        });
        $('.colorpicker-rgba').colorpicker();
    }

    var handleAccordions = function () {
        $(".accordion").collapse().height('auto');

        var lastClicked;

        //add scrollable class name if you need scrollable panes
        jQuery('.accordion.scrollable .accordion-toggle').click(function () {
            lastClicked = jQuery(this);
        }); //move to faq section

        jQuery('.accordion.scrollable').on('shown', function () {
            jQuery('html,body').animate({
                scrollTop: lastClicked.offset().top - 150
            }, 'slow');
        });
    }

    var handleScrollers = function () {

        var setPageScroller = function () {
            $('.main').slimScroll({
                size: '12px',
                color: '#a1b2bd',
                height: $(window).height(),
                allowPageScroll: true,
                alwaysVisible: true,
                railVisible: true
            });
        }

        /*
        //if (isIE8 == false) {
            $(window).resize(function(){
               setPageScroller(); 
            });
            setPageScroller();
        //} else {
            $('.main').removeClass("main");
        //}
        */

        $('.scroller').each(function () {
            $(this).slimScroll({
                //start: $('.blah:eq(1)'),
                size: '7px',
                color: '#a1b2bd',
                height: $(this).attr("data-height"),
                alwaysVisible: ($(this).attr("data-always-visible") == "1" ? true : false),
                railVisible: ($(this).attr("data-rail-visible") == "1" ? true : false),
                disableFadeOut: true
            });
        });

    }

    var handleSliders = function () {
        // basic
        $(".slider-basic").slider(); // basic sliders

        // snap inc
        $("#slider-snap-inc").slider({
            value: 100,
            min: 0,
            max: 1000,
            step: 100,
            slide: function (event, ui) {
                $("#slider-snap-inc-amount").text("$" + ui.value);
            }
        });

        $("#slider-snap-inc-amount").text("$" + $("#slider-snap-inc").slider("value"));

        // range slider
        $("#slider-range").slider({
            range: true,
            min: 0,
            max: 500,
            values: [75, 300],
            slide: function (event, ui) {
                $("#slider-range-amount").text("$" + ui.values[0] + " - $" + ui.values[1]);
            }
        });

        $("#slider-range-amount").text("$" + $("#slider-range").slider("values", 0) + " - $" + $("#slider-range").slider("values", 1));

        //range max

        $("#slider-range-max").slider({
            range: "max",
            min: 1,
            max: 10,
            value: 2,
            slide: function (event, ui) {
                $("#slider-range-max-amount").text(ui.value);
            }
        });

        $("#slider-range-max-amount").text($("#slider-range-max").slider("value"));

        // range min
        $("#slider-range-min").slider({
            range: "min",
            value: 37,
            min: 1,
            max: 700,
            slide: function (event, ui) {
                $("#slider-range-min-amount").text("$" + ui.value);
            }
        });

        $("#slider-range-min-amount").text("$" + $("#slider-range-min").slider("value"));

        // 
        // setup graphic EQ
        $("#slider-eq > span").each(function () {
            // read initial values from markup and remove that
            var value = parseInt($(this).text(), 10);
            $(this).empty().slider({
                value: value,
                range: "min",
                animate: true,
                orientation: "vertical"
            });
        });

        // vertical slider
        $("#slider-vertical").slider({
            orientation: "vertical",
            range: "min",
            min: 0,
            max: 100,
            value: 60,
            slide: function (event, ui) {
                $("#slider-vertical-amount").text(ui.value);
            }
        });
        $("#slider-vertical-amount").text($("#slider-vertical").slider("value"));

        // vertical range sliders
        $("#slider-range-vertical").slider({
            orientation: "vertical",
            range: true,
            values: [17, 67],
            slide: function (event, ui) {
                $("#slider-range-vertical-amount").text("$" + ui.values[0] + " - $" + ui.values[1]);
            }
        });

        $("#slider-range-vertical-amount").text("$" + $("#slider-range-vertical").slider("values", 0) + " - $" + $("#slider-range-vertical").slider("values", 1));
    }

    var handlKnobElements = function () {
        //knob does not support ie8 so skip it
        if (!jQuery().knob || isIE8) {
            return;
        }

        $(".knob").knob({
            'dynamicDraw': true,
            'thickness': 0.2,
            'tickColorizeValues': true,
            'skin': 'tron'
        });

        if ($(".knobify").size() > 0) {
            $(".knobify").knob({
                readOnly: true,
                skin: "tron",
                'width': 100,
                'height': 100,
                'dynamicDraw': true,
                'thickness': 0.2,
                'tickColorizeValues': true,
                'skin': 'tron',
                draw: function () {
                    // "tron" case
                    if (this.$.data('skin') == 'tron') {

                        var a = this.angle(this.cv) // Angle
                        ,
                            sa = this.startAngle // Previous start angle
                            ,
                            sat = this.startAngle // Start angle
                            ,
                            ea // Previous end angle
                            ,
                            eat = sat + a // End angle
                            ,
                            r = 1;

                        this.g.lineWidth = this.lineWidth;

                        this.o.cursor && (sat = eat - 0.3) && (eat = eat + 0.3);

                        if (this.o.displayPrevious) {
                            ea = this.startAngle + this.angle(this.v);
                            this.o.cursor && (sa = ea - 0.3) && (ea = ea + 0.3);
                            this.g.beginPath();
                            this.g.strokeStyle = this.pColor;
                            this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
                            this.g.stroke();
                        }

                        this.g.beginPath();
                        this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
                        this.g.stroke();

                        this.g.lineWidth = 2;
                        this.g.beginPath();
                        this.g.strokeStyle = this.o.fgColor;
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
                        this.g.stroke();

                        return false;

                    }
                }
            });
        }
    }

    var handleGoTop = function () {
        /* set variables locally for increased performance */
        jQuery('.footer .go-top').click(function () {
            App.scrollTo();
        });
    }

    var handleChat = function () {
        var cont = $('#chats');
        var list = $('.chats', cont);
        var form = $('.chat-form', cont);
        var input = $('input', form);
        var btn = $('.btn', form);

        var handleClick = function () {
            var text = input.val();
            if (text.length == 0) {
                return;
            }

            var time = new Date();
            var time_str = time.toString('MMM dd, yyyy HH:MM');
            var tpl = '';
            tpl += '<li class="out">';
            tpl += '<img class="avatar" alt="" src="assets/img/avatar1.jpg"/>';
            tpl += '<div class="message">';
            tpl += '<span class="arrow"></span>';
            tpl += '<a href="#" class="name">Bob Nilson</a>&nbsp;';
            tpl += '<span class="datetime">at ' + time_str + '</span>';
            tpl += '<span class="body">';
            tpl += text;
            tpl += '</span>';
            tpl += '</div>';
            tpl += '</li>';

            var msg = list.append(tpl);
            input.val("");
            $('.scroller', cont).slimScroll({
                scrollTo: list.height()
            });
        }

        btn.click(handleClick);
        input.keypress(function (e) {
            if (e.which == 13) {
                handleClick();
                return false; //<---- Add this line
            }
        });
    }

    var handleNestableList = function () {

        var updateOutput = function (e) {
            var list = e.length ? e : $(e.target),
                output = list.data('output');
            if (window.JSON) {
                output.val(window.JSON.stringify(list.nestable('serialize'))); //, null, 2));
            } else {
                output.val('JSON browser support required for this demo.');
            }
        };

        // activate Nestable for list 1
        $('#nestable_list_1').nestable({
            group: 1
        })
            .on('change', updateOutput);

        // activate Nestable for list 2
        $('#nestable_list_2').nestable({
            group: 1
        })
            .on('change', updateOutput);

        // output initial serialised data
        updateOutput($('#nestable_list_1').data('output', $('#nestable_list_1_output')));
        updateOutput($('#nestable_list_2').data('output', $('#nestable_list_2_output')));

        $('#nestable_list_menu').on('click', function (e) {
            var target = $(e.target),
                action = target.data('action');
            if (action === 'expand-all') {
                $('.dd').nestable('expandAll');
            }
            if (action === 'collapse-all') {
                $('.dd').nestable('collapseAll');
            }
        });

        $('#nestable_list_3').nestable();

    }

    var handleStyler = function () {

        var panel = $('.color-panel');

        $('.icon-color', panel).click(function () {
            $('.color-mode').show();
            $('.icon-color-close').show();
        });

        $('.icon-color-close', panel).click(function () {
            $('.color-mode').hide();
            $('.icon-color-close').hide();
        });

        $('li', panel).click(function () {
            var color = $(this).attr("data-style");
            setColor(color);
            $('.inline li', panel).removeClass("current");
            $(this).addClass("current");
        });

        $('input', panel).change(function () {
            setLayout();
        });

        var setColor = function (color) {
            $('#style_color').attr("href", "assets/css/style_" + color + ".css");
        }

        var setLayout = function () {
            if ($('input.header', panel).is(":checked")) {
                $("body").addClass("fixed-top");
                $(".header").addClass("navbar-fixed-top");
            } else {
                $("body").removeClass("fixed-top");
                $(".header").removeClass("navbar-fixed-top");
            }
        }
    }

    var handleFormWizards = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }

        // default form wizard
        $('#form_wizard_1').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index) {
                alert('on tab click disabled');
                return false;
            },
            onNext: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                // set wizard title
                $('.step-title', $('#form_wizard_1')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#form_wizard_1')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }

                if (current == 1) {
                    $('#form_wizard_1').find('.button-previous').hide();
                } else {
                    $('#form_wizard_1').find('.button-previous').show();
                }

                if (current >= total) {
                    $('#form_wizard_1').find('.button-next').hide();
                    $('#form_wizard_1').find('.button-submit').show();
                } else {
                    $('#form_wizard_1').find('.button-next').show();
                    $('#form_wizard_1').find('.button-submit').hide();
                }
                App.scrollTo($('.page-title'));
            },
            onPrevious: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                // set wizard title
                $('.step-title', $('#form_wizard_1')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#form_wizard_1')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }

                if (current == 1) {
                    $('#form_wizard_1').find('.button-previous').hide();
                } else {
                    $('#form_wizard_1').find('.button-previous').show();
                }

                if (current >= total) {
                    $('#form_wizard_1').find('.button-next').hide();
                    $('#form_wizard_1').find('.button-submit').show();
                } else {
                    $('#form_wizard_1').find('.button-next').show();
                    $('#form_wizard_1').find('.button-submit').hide();
                }

                App.scrollTo($('.page-title'));
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#form_wizard_1').find('.bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#form_wizard_1').find('.button-previous').hide();
        $('#form_wizard_1 .button-submit').click(function () {
            alert('Finished! Hope you like it :)');
        }).hide();
    }

    var handleFormValidation = function () {

        // for more info visit the official plugin documentation: 
        // http://docs.jquery.com/Plugins/Validation

        var form1 = $('#form_sample_1');
        var error1 = $('.alert-error', form1);
        var success1 = $('.alert-success', form1);

        form1.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-inline', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",
            rules: {
                name: {
                    minlength: 2,
                    required: true
                },
                email: {
                    required: true,
                    email: true
                },
                url: {
                    required: true,
                    url: true
                },
                number: {
                    required: true,
                    number: true
                },
                digits: {
                    required: true,
                    digits: true
                },
                creditcard: {
                    required: true,
                    creditcard: true
                },
                occupation: {
                    minlength: 5,
                },
                category: {
                    required: true
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
                success1.hide();
                error1.show();
                App.scrollTo(error1, -200);
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.help-inline').removeClass('ok'); // display OK icon
                $(element)
                    .closest('.control-group').removeClass('success').addClass('error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change dony by hightlight
                $(element)
                    .closest('.control-group').removeClass('error'); // set error class to the control group
            },

            success: function (label) {
                label
                    .addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                .closest('.control-group').removeClass('error').addClass('success'); // set success class to the control group
            },

            submitHandler: function (form) {
                success1.show();
                error1.hide();
            }
        });

        //Sample 2
        var form2 = $('#form_sample_2');
        var error2 = $('.alert-error', form2);
        var success2 = $('.alert-success', form2);

        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-inline', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",
            rules: {
                name: {
                    minlength: 2,
                    required: true
                },
                email: {
                    required: true,
                    email: true
                },
                category: {
                    required: true
                },
                education: {
                    required: true
                },
                occupation: {
                    minlength: 5,
                },
                membership: {
                    required: true
                },
                service: {
                    required: true,
                    minlength: 2
                }
            },

            messages: { // custom messages for radio buttons and checkboxes
                membership: {
                    required: "Please select a Membership type"
                },
                service: {
                    required: "Please select  at least 2 types of Service",
                    minlength: jQuery.format("Please select  at least {0} types of Service")
                }
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                if (element.attr("name") == "education") { // for chosen elements, need to insert the error after the chosen container
                    error.insertAfter("#form_2_education_chzn");
                } else if (element.attr("name") == "membership") { // for uniform radio buttons, insert the after the given container
                    error.addClass("no-left-padding").insertAfter("#form_2_membership_error");
                } else if (element.attr("name") == "service") { // for uniform checkboxes, insert the after the given container
                    error.addClass("no-left-padding").insertAfter("#form_2_service_error");
                } else {
                    error.insertAfter(element); // for other inputs, just perform default behavoir
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit   
                success2.hide();
                error2.show();
                App.scrollTo(error2, -200);
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.help-inline').removeClass('ok'); // display OK icon
                $(element)
                    .closest('.control-group').removeClass('success').addClass('error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change dony by hightlight
                $(element)
                    .closest('.control-group').removeClass('error'); // set error class to the control group
            },

            success: function (label) {
                if (label.attr("for") == "service" || label.attr("for") == "membership") { // for checkboxes and radip buttons, no need to show OK icon
                    label
                        .closest('.control-group').removeClass('error').addClass('success');
                    label.remove(); // remove error label here
                } else { // display success icon for other inputs
                    label
                        .addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                    .closest('.control-group').removeClass('error').addClass('success'); // set success class to the control group
                }
            },

            submitHandler: function (form) {
                success2.show();
                error2.hide();
            }

        });

        //apply validation on chosen dropdown value change, this only needed for chosen dropdown integration.
        $('.chosen, .chosen-with-diselect', form2).change(function () {
            form2.validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
        });
    }

    var handleTree = function () {

        // handle collapse/expand for tree_1
        $('#tree_1_collapse').click(function () {
            $('.tree-toggle', $('#tree_1 > li > ul')).addClass("closed");
            $('.branch', $('#tree_1 > li > ul')).removeClass("in");
        });

        $('#tree_1_expand').click(function () {
            $('.tree-toggle', $('#tree_1 > li > ul')).removeClass("closed");
            $('.branch', $('#tree_1 > li > ul')).addClass("in");
        });

        // handle collapse/expand for tree_2
        $('#tree_2_collapse').click(function () {
            $('.tree-toggle', $('#tree_2 > li > ul')).addClass("closed");
            $('.branch', $('#tree_2 > li > ul')).removeClass("in");
        });

        $('#tree_2_expand').click(function () {
            //$('.tree-toggle', $('#tree_2 > li > ul')).removeClass("closed");
            // iterate tree nodes and exppand all nodes
            $('.tree-toggle', $('#tree_2 > li > ul')).each(function () {
                $(this).click(); //trigger tree node click
            });
            $('.branch', $('#tree_2 > li > ul')).addClass("in");
        });

        //This is a quick example of capturing the select event on tree leaves, not branches
        $("#tree_1").on("nodeselect.tree.data-api", "[data-role=leaf]", function (e) {
            var output = "";

            output += "Node nodeselect event fired:\n";
            output += "Node Type: leaf\n";
            output += "Value: " + ((e.node.value) ? e.node.value : e.node.el.text()) + "\n";
            output += "Parentage: " + e.node.parentage.join("/");

            alert(output);
        });

        //This is a quick example of capturing the select event on tree branches, not leaves
        $("#tree_1").on("nodeselect.tree.data-api", "[role=branch]", function (e) {
            var output = "Node nodeselect event fired:\n"; + "Node Type: branch\n" + "Value: " + ((e.node.value) ? e.node.value : e.node.el.text()) + "\n" + "Parentage: " + e.node.parentage.join("/") + "\n"

            alert(output);
        })

        //Listening for the 'openbranch' event. Look for e.node, which is the actual node the user opens

        $("#tree_1").on("openbranch.tree", "[data-toggle=branch]", function (e) {

            var output = "Node openbranch event fired:\n" + "Node Type: branch\n" + "Value: " + ((e.node.value) ? e.node.value : e.node.el.text()) + "\n" + "Parentage: " + e.node.parentage.join("/") + "\n"

            alert(output);
        })


        //Listening for the 'closebranch' event. Look for e.node, which is the actual node the user closed

        $("#tree_1").on("closebranch.tree", "[data-toggle=branch]", function (e) {

            var output = "Node closebranch event fired:\n" + "Node Type: branch\n" + "Value: " + ((e.node.value) ? e.node.value : e.node.el.text()) + "\n" + "Parentage: " + e.node.parentage.join("/") + "\n"

            alert(output);
        })
    }

    return {

        //main function to initiate template pages
        init: function () {
            handleResponsive(); // set and handle responsive
            handleUniform(); // handles uniform elements
            
			// page level handlers
            if (App.isPage("index")) {
                $('.nav-index').addClass('active');
				handleIndex();
            }
			
			if(App.isPage("add_account")) { //javascript for token pages
				$('.nav-conf').addClass('active');
				handleToken();
			}
			
			if(App.isPage("stats")) { //javascript for token pages
				$('.nav-stats').addClass('active');
				handleCharts();
			}
			
			if(App.isPage("settings")) { //javascript for token pages
				$('.nav-conf').addClass('active');
				handleSettings();
			}

            // global handlers
            handleChoosenSelect(); // handles bootstrap chosen dropdowns
            handleScrollers(); // handles slim scrolling contents            
            handleTagsInput() // handles tag input elements
            handleDateTimePickers(); //handles form timepickers
            handleClockfaceTimePickers(); //handles form clockface timepickers
            handleColorPicker(); // handles form color pickers            
            handlePortletTools(); // handles portlet action bar functionality(refresh, configure, toggle, remove)
            handlePulsate(); // handles pulsate functionality on page elements
            handleGritterNotifications(); // handles gritter notifications
            handleTooltip(); // handles bootstrap tooltips
            handlePopover(); // handles bootstrap popovers
            handleToggleButtons(); // handles form toogle buttons
            handleWysihtml5(); //handles WYSIWYG Editor           
            handleFancyBox(); // handles fancy box image previews
            handleStyler(); // handles style customer tool
            handleMainMenu(); // handles main menu
            handleSidebarToggler() // handles sidebar hide/show
            handleFixInputPlaceholderForIE(); // fixes/enables html5 placeholder attribute for IE9, IE8
            handleGoTop(); //handles scroll to top functionality in the footer
            handleAccordions(); //handles accordions
            handleFormWizards(); // handles form wizards
        },

        // login page setup
        initLogin: function () {
            handleLoginForm(); // handles login form
            handleUniform(); // // handles uniform elements
            handleFixInputPlaceholderForIE(); // fixes/enables html5 placeholder attribute for IE9, IE8
        },

        // wrapper function for page element pulsate
        pulsate: function (el, options) {
            var opt = jQuery.extend(options, {
                color: '#d12610', // set the color of the pulse
                reach: 15, // how far the pulse goes in px
                speed: 300, // how long one pulse takes in ms
                pause: 0, // how long the pause between pulses is in ms
                glow: false, // if the glow should be shown too
                repeat: 1, // will repeat forever if true, if given a number will repeat for that many times
                onHover: false // if true only pulsate if user hovers over the element
            });
            jQuery(el).pulsate(opt);
        },

        // wrapper function to scroll to an element
        scrollTo: function (el, offeset) {
            pos = el ? el.offset().top : 0;
            jQuery('html,body').animate({
                scrollTop: pos + (offeset ? offeset : 0)
            }, 'slow');
        },

        // wrapper function to  block element(indicate loading)
        blockUI: function (el, loaderOnTop) {
            lastBlockedUI = el;
            jQuery(el).block({
                message: '<img src="./assets/img/loading.gif" align="absmiddle">',
                css: {
                    border: 'none',
                    padding: '2px',
                    backgroundColor: 'none'
                },
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.05,
                    cursor: 'wait'
                }
            });
        },

        // wrapper function to  un-block element(finish loading)
        unblockUI: function (el) {
            jQuery(el).unblock({
                onUnblock: function () {
                    jQuery(el).removeAttr("style");
                }
            });
        },

        // public method to initialize uniform inputs
        initFancybox: function () {
            handleFancyBox();
        },

        // initializes uniform elements
        initUniform: function (el) {
            initUniform(el);
        },

        // initializes choosen dropdowns
        initChosenSelect: function (el) {
            initChosenSelect(el);
        },

        getActualVal: function (el) {
            var el = jQuery(el);
            if (el.val() === el.attr("placeholder")) {
                return "";
            }

            return el.val();
        },

        // set map page
        setPage: function (name) {
            currentPage = name;
        },

        // check current page
        isPage: function (name) {
            return currentPage == name ? true : false;
        },

        // check for device touch support
        isTouchDevice: function () {
            try {
                document.createEvent("TouchEvent");
                return true;
            } catch (e) {
                return false;
            }
        }

    };

}();