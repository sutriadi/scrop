//      data.js
//      
//      Copyright 2011 Indra Sutriadi Pipii <indra.sutriadi@gmail.com>
//      
//      This program is free software; you can redistribute it and/or modify
//      it under the terms of the GNU General Public License as published by
//      the Free Software Foundation; either version 2 of the License, or
//      (at your option) any later version.
//      
//      This program is distributed in the hope that it will be useful,
//      but WITHOUT ANY WARRANTY; without even the implied warranty of
//      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//      GNU General Public License for more details.
//      
//      You should have received a copy of the GNU General Public License
//      along with this program; if not, write to the Free Software
//      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//      MA 02110-1301, USA.

	/*** jQuery dataTables ***/

	var asInitVals = new Array();
	var oTable;
	var oCache = { iCacheLower: -1 };
	var img_src_result;
	var img_src_origin;
	var img_src_upload;
	var member_id;

	function fnSetKey( aoData, sKey, mValue )
	{
		for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
		{
			if ( aoData[i].name == sKey )
			{
				aoData[i].value = mValue
			}
		}
	}

	function fnGetKey( aoData, sKey )
	{
		for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
		{
			if ( aoData[i].name == sKey )
			{
				return aoData[i].value
			}
		}
		return null;
	}

	function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {
		$.ajax( {
			"dataType": 'json', 
			"type": "POST", 
			"url": sSource, 
			"data": aoData, 
			"success": fnCallback
		} );
		var iPipe = 5; /* Ajust the pipe size */
		
		var bNeedServer = false;
		var sEcho = fnGetKey(aoData, "sEcho");
		var iRequestStart = fnGetKey(aoData, "iDisplayStart");
		var iRequestLength = fnGetKey(aoData, "iDisplayLength");
		var iRequestEnd = iRequestStart + iRequestLength;
		oCache.iDisplayStart = iRequestStart;
		
		/* outside pipeline? */
		if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper )
		{
			bNeedServer = true;
		}
		
		/* sorting etc changed? */
		if ( oCache.lastRequest && !bNeedServer )
		{
			for( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" )
				{
					if ( aoData[i].value != oCache.lastRequest[i].value )
					{
						bNeedServer = true;
						break;
					}
				}
			}
		}
		
		/* Store the request for checking next time around */
		oCache.lastRequest = aoData.slice();
		
		if ( bNeedServer )
		{
			if ( iRequestStart < oCache.iCacheLower )
			{
				iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
				if ( iRequestStart < 0 )
				{
					iRequestStart = 0;
				}
			}
			
			oCache.iCacheLower = iRequestStart;
			oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
			oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
			fnSetKey( aoData, "iDisplayStart", iRequestStart );
			fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
			
			$.getJSON( sSource, aoData, function (json) { 
				/* Callback processing */
				oCache.lastJson = jQuery.extend(true, {}, json);
				
				if ( oCache.iCacheLower != oCache.iDisplayStart )
				{
					json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
				}
				json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
				
				fnCallback(json)
			} );
		}
		else
		{
			json = jQuery.extend(true, {}, oCache.lastJson);
			json.sEcho = sEcho; /* Update the echo for each response */
			json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
			json.aaData.splice( iRequestLength, json.aaData.length );
			fnCallback(json);
			return;
		}
	}

	function get_id() {
		return id=$('input[name="member[]"]').serializeArray();
	}

	function set_image(data) {
		var data=($.type(data)=='string')?$.parseJSON(data):data;
		minWidth=180;
		maxWidth=$('body').width();
		maxHeight=500;
		var w=(data.w+50<minWidth)?minWidth:data.w+50;
		var w=(data.w+50>maxWidth)?maxWidth:data.w+50;
		var h=(data.h+50>maxHeight)?maxHeight:'auto';
		img_src_result = data.src;
		$("#s_result_image").attr('title', data.src);
		$("#s_result_image").attr('src', dirtemp+data.src);
		$("#s_result").dialog('option', 'width', w);
		$("#s_result").dialog('option', 'height', h);
		$("#s_result").dialog('open');
	}

	function no_member() {
		$("#validateTips").text("Member belum dipilih");
		$("#s_dialog").dialog("open");
	}

	function del_image() {
		$.post(
			"./php/update.php",
			{ "file": img_src_result, "action": "delete", "id": get_id() }
		);
		img_src_result = '';
	}

	function upload(return_message) {
		var data=$.parseJSON(return_message);
		$('#validateTips').text(data.msg);
		if(data.error==true){
			$('#s_dialog').dialog("open");			
		}else{
			set_image(data.attr);
		}
	}

	$(document).ready(function() {
		var tips=$('#validateTips');
		satuan(document.s_options_form.unit);
		var s_crop_frame=document.getElementById('s_crop_frame');
		
		$('#s_file').parent().hide();
		$('#s_file').bind({
			click: function() {
			},
			change: function() {
				$("#s_file_name").text($(this).val());
			},
		});
		$('#s_choose').click(function(e) {
		   $('#s_file').trigger('click');
		});

		$("#s_upload_form").jqupload({"callback":"upload"});
		$("#s_upload_form").jqupload_form();

		$('#crop').click(function() {
			var id=get_id();
			if(id.length==0){
				no_member();
			}else{
				member_id=$('input[name="member[]"]:checked').val();
				$.post(
					"./php/image.php",
					id,
					function(data){
						if(data.error==0){
							img_src_origin=data.src;
							$('#s_crop_frame').attr('src', 'frame.php?src='+data.src);
							$('#s_crop').dialog("option", "title", 'Crop Picture for Member: '+member_id );
							$('#s_crop').dialog("open");
						}else{
							tips.html("Member ID: <strong>"+data.id+"</strong> tidak memiliki gambar!<br />Silakan upload gambar terlebih dahulu!");
							$("#s_dialog").dialog("open");
						}
					},
					"json"
				);
			}
		});

		$('#upload').click(function() {
			var id=get_id();
			if(id.length==0){
				no_member();
			}else{
				member_id=$('input[name="member[]"]:checked').val();
				$.post(
					"./php/image.php",
					id,
					function(data){
						if(data.error==0){
							img_src_origin=data.src;
							img_src_upload=data.src;
						}else{
							img_src_origin=false;
							img_src_upload=false;
						}
					},
					"json"
				);
				$('#s_upload').dialog( "option", "title", 'Upload Picture for Member: '+member_id );
				$('#s_upload').dialog("open");
			}
		});

		$("#s_options_accordion").accordion({
			autoHeight: false,
			collapsible: true,
			header: "h3"
		});
		
		var dialog_conf = {
			bgiframe: true,
			autoOpen: false,
			modal: true,
		}

		var s_dialog_conf = {
			width: 350,
			close: function(event, ui) {
				$('#validateTips').empty();
			},
			buttons: {
				"OK": function() { $(this).dialog('close'); }
			},
		}

		var s_upload_conf = {
			width: 300,
			height: 200,
			close: function(event, ui) {
				$.post(
					"./php/update.php",
					{ "file": img_src_upload, "action": "delete", "id": get_id() }
				);
				img_src_upload=false;
			},
		}

		var s_crop_conf = {
			width: 600,
			height: 540,
			close: function() {
				var src = $('#s_crop_frame').attr('src').split('?')[1].split('=')[1];
				$.post(
					"./php/update.php",
					{"file": src, "action": "delete", "id": get_id() }
				);
			},
		}
		
		var s_result_conf = {
			minWidth: 180,
			buttons: {
				"Save": function() {
					$.post(
						"./php/update.php",
						{ "file": img_src_result, "orig": img_src_origin, "action": "save", "id": get_id() },
						//~ {  },
						function(data) {
							if(data.error==0){
								tips.html(data.msg);
								img_src_origin=img_src_result;
							}else{
								tips.html(data.msg);
							}
							$("#s_dialog").dialog("open");
							$("#s_result").dialog("close");
						},
						"json"
					);
				},
				"Delete": function() {
					del_image();
					$("#s_result").dialog("close");
				},
			},
			close: function(event, ui) {
				del_image();
				$("#s_result_image").removeAttr("src");
			}
		}

		/* untuk ngetes doang */
			//~ $.get("../../library/dataTables/php/processing.php?plugin=smember&table=smember", {},
		//~ $("#ngetes").click( function() {
			//~ $.get("./php/setup.php?plugin=smember&table=smember", {},
			   //~ function(data){
				 //~ alert(data);
			   //~ }, "html"
			//~ );
		//~ });

		var s_options_conf = {
			width: "660",
			buttons: {
				"Save": function() {
					$.post(
						"./php/setup.php",
						$('form[name="s_options_form"]').serializeArray(),
						function(data){
							if(data.track=="sukses") {
								$("#validateTips").text("Data sudah disimpan!").effect("highlight", {}, 1500);
								$('#s_dialog').dialog("option", "buttons", { "Refresh": function() { $(this).dialog("close"); window.location.reload(); } } );
							}
							else {
								$("#validateTips").text("Data gagal disimpan!").effect("highlight", {}, 1500);
								$('#s_dialog').dialog("option", "buttons", { "OK": function() { $(this).dialog("close"); } } );
							}
							$('#s_dialog').dialog('open');
						},
						"json"
					);
					$(this).dialog('close');
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			},
			close: function(event, ui) {
				$('#s_upload_frame').attr('src','');
				$('#s_file_name').empty();
			},
		}
		$.extend(true, s_dialog_conf, dialog_conf);
		$.extend(true, s_options_conf, dialog_conf);
		$.extend(true, s_crop_conf, dialog_conf);
		$.extend(true, s_result_conf, dialog_conf);
		$.extend(true, s_upload_conf, dialog_conf);
		$("#s_dialog").dialog(eval(s_dialog_conf));
		$("#s_crop").dialog(eval(s_crop_conf));
		$("#s_upload").dialog(eval(s_upload_conf));
		$("#s_options").dialog(eval(s_options_conf));
		$("#s_result").dialog(eval(s_result_conf));

		$('#tutup').click(function() { window.close() });
		$('#reload').click(function() { window.location.reload() });

		$('button, input[type="submit"], input[type="button"]').button();
		$('#to_options').button().click(function() {
			$('#s_options').dialog("open");
		});

		var jsDef = {
			"bProcessing": true,
			"bServerSide": true,
			"bAutoWidth": false,
			"bJQueryUI": true,
			"bFilter": true,
			"aLengthMenu": [[5, 10, 20, 30, 40, 50], [5, 10, 20, 30, 40, 50]],
			"sPaginationType": "full_numbers",
			"sAjaxSource": "../../library/dataTables/php/processing.php?plugin=scrop&table=scrop",
			"fnServerData": fnDataTablesPipeline,
			"sScrollY": "200px",
			"oLanguage": {
				"sSearch": "Search all:"
			}
		};

		$.extend(true, jsDef, phpDef);

		oTable=$('#members').dataTable( jsDef );

		$("tfoot input").keyup( function () {
			/* Filter on the column (the index) of this element */
			oTable.fnFilter( this.value, $("tfoot input").index(this) );
		} );

		$("tfoot input").each( function (i) {
			asInitVals[i] = this.value;
		} );

		$("tfoot input").focus( function () {
			if ( this.className == "search_init" )
			{
				this.className = "";
				this.value = "";
			}
		} );

		$("tfoot input").blur( function (i) {
			if ( this.value == "" )
			{
				this.className = "search_init";
				this.value = asInitVals[$("tfoot input").index(this)];
			}
		} );

	} );

	/*** options functions ***/

	function satuan(t)
	{
		var unit=t.options[t.selectedIndex].text;
		$('span[class="unit"]').text(unit);
	}

	/*** some additional functions ***/
	
	function chform(target, action)
	{
		var f=document.formulir
		f.target=target
		f.action=action
	}

	function alluncheck(t)
	{
		f=t.form
		cb=f.elements['member[]']
		if(cb){
			for(n=0;n<cb.length;n++)
				cb[n].checked=false
		}
	}
