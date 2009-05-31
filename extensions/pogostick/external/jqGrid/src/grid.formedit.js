;(function($){
/**
 * jqGrid extension for form editing Grid Data
 * Tony Tomov tony@trirand.com
 * http://trirand.com/blog/ 
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
**/ 
var rp_ge = null;
$.fn.extend({
	searchGrid : function (p) {
		p = $.extend({
			recreateFilter: false,
			drag: true,
			sField:'searchField',
			sValue:'searchString',
			sOper: 'searchOper',
			sFilter: 'filters',
			checkInput :false,
			beforeShowSearch: null,
			afterShowSearch : null,
			onInitializeSearch: null,
			closeAfterSearch : false,
			closeOnEscape : false,
			multipleSearch : false,
			// translation
			// if you want to change or remove the order change it in sopt
			// ['bw','eq','ne','lt','le','gt','ge','ew','cn'] 
			sopt: null 
			// these are common options
		}, $.jgrid.search, p || {});
		return this.each(function() {
			var $t = this;
			if(!$t.grid) {return;}
			if($.fn.searchFilter) {
				var fid = "fbox_"+$t.p.id;
				if(p.recreateFilter===true) {$("#"+fid).remove();}
				if( $("#"+fid).html() != null ) {
					if ( $.isFunction(p.beforeShowSearch) ) { p.beforeShowSearch($("#"+fid)); };
					showFilter();
					if( $.isFunction(p.afterShowSearch) ) { p.afterShowSearch($("#"+fid)); }
				} else {
					var fields = [],
					colNames = jQuery("#"+$t.p.id).getGridParam("colNames"),
					colModel = jQuery("#"+$t.p.id).getGridParam("colModel"),
					stempl = ['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc'],
					j,pos,k;
				    $.each(colModel, function(i, v) {
				        var searchable = (typeof v.search === 'undefined') ?  true: v.search ,
				        hidden = (v.hidden === true),
						soptions = $.extend({},{text: colNames[i],value: v.name},this.searchoptions),
						ignoreHiding = (soptions.searchhidden === true) || true;
						if(soptions.sopt !=null) {
							k=0;
							soptions.ops =[];
							for(j=0;j<soptions.sopt.length;j++) {
								if( (pos= $.inArray(soptions.sopt[j],stempl)) != -1 ){
									soptions.ops[k] = {op:soptions.sopt[j],text: p.odata[pos]};
									k++;
								}
							}
						}
						if(typeof(this.stype) === 'undefined') this.stype='text';
						if(this.stype == 'select') {
							if ( soptions.dataUrl != null) {}
							else if(this.editoptions) {
								var eov = this.editoptions.value;
								if(eov) {
									soptions.dataValues =[];
									if(typeof(eov) === 'string') {
										var so = eov.split(";"),sv;
										for(j=0;j<so.length;j++) {
											sv = so[j].split(":");
											soptions.dataValues[j] ={value:sv[0],text:sv[1]};
										}
									} else if (typeof(eov) === 'object') {
										j=0;
										for (var key in eov) {
											soptions.dataValues[j] ={value:key,text:eov[key]};
											j++;
										}
									}
								}
							}
						}
				        if ((ignoreHiding && searchable) || (searchable && !hidden)) {
							fields.push(soptions);
						}
					});
					if(fields.length>0){
						var oprtr = jQuery.fn.searchFilter.defaults.operators;
						if (p.sopt !=null) {
							oprtr = [];
							k=0;
							for(j=0;p.sopt.length<0;j++) {
								if( (pos= $.inArray(p.sopt[j],stempl)) != -1 ){
									oprtr[k] = {op:p.sopt[j],text: p.odata[pos]};
									k++;
								}
							}
							
						}
						$("<div id='"+fid+"' role='dialog' tabindex='-1'></div>").insertBefore("#gview_"+$t.p.id);
						jQuery("#"+fid).searchFilter(fields, { operators: oprtr, onClose:hideFilter, resetText: p.Reset, searchText: p.Find, windowTitle: p.caption,  rulesText:p.rulesText, matchText:p.matchText, onSearch: searchFilters, onReset: resetFilters,stringResult:p.multipleSearch });
						$(".ui-widget-overlay","#"+fid).remove();
						if (p.drag===true) {
							$("#"+fid+" table thead tr:first td:first").css('cursor','move');
							if(jQuery.fn.jqDrag) {
								jQuery("#"+fid).jqDrag($("#"+fid+" table thead tr:first td:first"));
							} else {
								try {
									$("#"+fid).draggable({handle: jQuery("#"+fid+" table thead tr:first td:first")});
								} catch (e) {}
							}
						}
						if(p.multipleSearch === false) {
							$(".ui-del, .ui-add, .ui-del, .ui-add-last, .matchText, .rulesText", "#"+fid).remove();
							$("select[name='groupOp']","#"+fid).hide();
						}
						if ( $.isFunction(p.onInitializeSearch) ) { p.onInitializeSearch( $("#"+fid) ); };
						if ( $.isFunction(p.beforeShowSearch) ) { p.beforeShowSearch($("#"+fid)); };
						showFilter();
						if( $.isFunction(p.afterShowSearch) ) { p.afterShowSearch($("#"+fid)); }
						if(p.closeOnEscape===true){
							jQuery("#"+fid).keydown( function( e ) {
								if( e.which == 27 ) {
									hideFilter($("#"+fid));
								}
							});
						}
					}
				}
			}
			function searchFilters(filters) {
				var hasFilters = (filters !== undefined),
				grid = jQuery("#"+$t.p.id), sdata=[];
				if(p.multipleSearch===false) {
					sdata[p.sField] = filters.rules[0].field;
					sdata[p.sValue] = filters.rules[0].data;
					sdata[p.sOper] = filters.rules[0].op;
				} else {
					sdata[p.sFilter] = filters;
				}
				grid[0].p.search = hasFilters;
				grid[0].p.searchdata = sdata;
				grid[0].p.page= 1;
				grid.trigger("reloadGrid");
				if(p.closeAfterSearch) hideFilter($("#"+fid));
			}
			function resetFilters(filters) {
				var hasFilters = (filters !== undefined),
				grid = jQuery("#"+$t.p.id), sdata=[];
				grid[0].p.search = hasFilters;
				if(p.multipleSearch===false) {
					sdata[p.sField] = sdata[p.sValue] = sdata[p.sOper] = "";
				} else {
					sdata[p.sFilter] = "";
				}
				grid[0].p.searchdata = sdata;
				grid[0].p.page= 1;
				grid.trigger("reloadGrid");
			}
			function hideFilter(selector) {
				selector.hide();
				$(".jqgrid-overlay","#gbox_"+$t.p.id).hide();
			}
			function showFilter(){
				$("#"+fid).show();
				$(".jqgrid-overlay","#gbox_"+$t.p.id).show();
				try{$(':input:visible',"#"+fid)[0].focus();}catch(_){}
			}
		});
	},
	editGridRow : function(rowid, p){
		p = $.extend({
			top : 0,
			left: 0,
			width: 300,
			height: 'auto',
			modal: false,
			drag: true,
			resize: true,
			url: null,
			mtype : "POST",
			closeAfterAdd : false,
			clearAfterAdd : true,
			closeAfterEdit : false,
			reloadAfterSubmit : true,
			onInitializeForm: null,
			beforeInitData: null,
			beforeShowForm: null,
			afterShowForm: null,
			beforeSubmit: null,
			afterSubmit: null,
			onclickSubmit: null,
			afterComplete: null,
			onclickPgButtons : null,
			afterclickPgButtons: null,
			editData : {},
			recreateForm : false,
			jqModal : true,
			closeOnEscape : false,
			addedrow : "first",
			topinfo : '',
			bottominfo: '',
			saveicon : [],
			closeicon : [],
			savekey: [false,13],
			navkeys: [false,38,40]
		}, $.jgrid.edit, p || {});
		rp_ge = p;
		return this.each(function(){
			var $t = this;
			if (!$t.grid || !rowid) { return; }
			// I hate to rewrite code, but ...
			var gID = $t.p.id,
			IDs = {themodal:'editmod'+gID,modalhead:'edithd'+gID,modalcontent:'editcnt'+gID},
			onBeforeShow = $.isFunction(rp_ge.beforeShowForm) ? rp_ge.beforeShowForm : false,
			onAfterShow = $.isFunction(rp_ge.afterShowForm) ? rp_ge.afterShowForm : false,
			onBeforeInit = $.isFunction(rp_ge.beforeInitData) ? rp_ge.beforeInitData : false,
			onInitializeForm = $.isFunction(rp_ge.onInitializeForm) ? rp_ge.onInitializeForm : false,
			frmgr = "FrmGrid_"+gID,frmtb = "TblGrid_"+gID,
			copydata = null,
			maxCols = 1, maxRows=0;
			if (rowid=="new") {
				rowid = "_empty";
				p.caption=p.addCaption;
			} else {
				p.caption=p.editCaption;
			};
			if(p.recreateForm===true && $("#"+IDs.themodal).html() != null) {
				$("#"+IDs.themodal).remove();
			}
			if ( $("#"+IDs.themodal).html() != null ) {
				$(".ui-jqdialog-title","#"+IDs.modalhead).html(p.caption);
				$("#FormError","#"+frmtb).hide();
				if(onBeforeInit) { onBeforeInit($("#"+frmgr)); }
				// filldata
				fillData(rowid,$t,frmgr);
				///
				if(rowid=="_empty") { $("#pData, #nData","#"+frmtb).hide(); } else { $("#pData, #nData","#"+frmtb).show(); }
				if(p.processing===true) {
					p.processing=false;
					$("#sData", "#"+frmtb).removeClass('ui-state-active');
				}
				if(onBeforeShow) { onBeforeShow($("#"+frmgr)); }
				viewModal("#"+IDs.themodal,{gbox:"#gbox_"+gID,jqm:p.jqModal});
				if(onAfterShow) { onAfterShow($("#"+frmgr)); }
			} else {
				$($t.p.colModel).each( function(i) {
					var fmto = this.formoptions;
					maxCols = Math.max(maxCols, fmto ? fmto.colpos || 0 : 0 );
					maxRows = Math.max(maxRows, fmto ? fmto.rowpos || 0 : 0 );
				});								
				var flr, frm = $("<form name='FormPost' id='"+frmgr+"' class='FormGrid'></form>"),
				tbl =$("<table id='"+frmtb+"' class='EditTable' cellspacing='0' cellpading='0' border='0' width='100%'><tbody></tbody></table>");
				$(frm).append(tbl);
				flr = $("<tr id='FormError' style='display:none'><td class='ui-state-error' colspan='"+(maxCols*2)+"'></td></tr>");
				flr[0].rp = 0;
				$(tbl).append(flr);
				if(rp_ge.topinfo) {
					flr = $("<tr><td class='topinfo' colspan='"+(maxCols*2)+"'>"+rp_ge.topinfo+"</td></tr>");
					flr[0].rp = 0;
					$(tbl).append(flr);
				}
				// set the id.
				// use carefull only to change here colproperties.
				if(onBeforeInit) { onBeforeInit($("#"+frmgr)); }
				// create data
				var valref = createData(rowid,$t,tbl,maxCols),
				// buttons at footer
				bP = "<a href='javascript:void(0)' id='pData' class='fm-button ui-state-default ui-corner-left'><span class='ui-icon ui-icon-triangle-1-w'></span></div>",
				bN = "<a href='javascript:void(0)' id='nData' class='fm-button ui-state-default ui-corner-right'><span class='ui-icon ui-icon-triangle-1-e'></span></div>",
				bS  ="<a href='javascript:void(0)' id='sData' class='fm-button ui-state-default ui-corner-all'>"+p.bSubmit+"</a>",
				bC  ="<a href='javascript:void(0)' id='cData' class='fm-button ui-state-default ui-corner-all'>"+p.bCancel+"</a>";
				flr = $("<tr id='Act_Buttons'><td class='navButton ui-widget-content'>"+bP+bN+"</td><td colspan='"+(maxCols*2-1)+"' class='EditButton ui-widget-content'>"+bS+"&nbsp;"+bC+"</td></tr>");
				flr[0].rp = 900;
				$(tbl).append(flr);
				if(rp_ge.bottominfo) {
					flr = $("<tr><td class='bottominfo' colspan='"+(maxCols*2)+"'>"+rp_ge.bottominfo+"</td></tr>");
					flr[0].rp = 901;
					$(tbl).append(flr);
				}
				if(maxRows >  0) {
					var sd=[];
					$.each($(tbl)[0].rows,function(i,r){
						sd[i] = r;
					});
					sd.sort(function(a,b){
						if(a.rp > b.rp) {return 1;}
						if(a.rp < b.rp) {return -1;}
						return 0;
					});
					$.each(sd, function(index, row) {
						$('tbody',tbl).append(row);
					});
				}
				p.gbox = "#gbox_"+gID;
				var cle = false;
				if(p.closeOnEscape===true){
					p.closeOnEscape = false;
					cle = true;
				}
				createModal(IDs,frm,p,"#gview_"+$t.p.id,$("#gview_"+$t.p.id)[0]);
				jQuery("#"+IDs.themodal).keydown( function( e ) {
					if(rp_ge.savekey[0] === true && e.which === rp_ge.savekey[1]) { // save
						$("#sData", "#"+frmtb).trigger("click");
						return false;
					}
					if(e.which === 27) {
						if(cle)	hideModal(this,{gb:p.gbox,jqm:p.jqModal});
						return false;
					}
					if(rp_ge.navkeys[0]===true) {
						if($("#id_g","#"+frmtb).val() == "_empty") return true;
						if(e.which === rp_ge.navkeys[1]){ //up
							$("#pData", "#"+frmtb).trigger("click");
							return false;
						}
						if(e.which === rp_ge.navkeys[2]){ //down
							$("#nData", "#"+frmtb).trigger("click");
							return false;
						}
					}
				});
				p.saveicon = $.extend([true,"left","ui-icon-disk"],p.saveicon);
				p.closeicon = $.extend([true,"left","ui-icon-close"],p.closeicon);
				// beforeinitdata after creation of the form
				if(p.saveicon[0]==true) {
					$("#sData","#"+frmtb).addClass(p.saveicon[1] == "right" ? 'fm-button-icon-right' : 'fm-button-icon-left')
					.append("<span class='ui-icon "+p.saveicon[2]+"'></span>");
				}
				if(p.closeicon[0]==true) {
					$("#cData","#"+frmtb).addClass(p.closeicon[1] == "right" ? 'fm-button-icon-right' : 'fm-button-icon-left')
					.append("<span class='ui-icon "+p.closeicon[2]+"'></span>");
				}
				// here initform - only once
				if(onInitializeForm) { onInitializeForm($("#"+frmgr)); }
				if(rowid=="_empty") { $("#pData,#nData","#"+frmtb).hide(); } else { $("#pData,#nData","#"+frmtb).show(); }
				if(onBeforeShow) { onBeforeShow($("#"+frmgr)); }
				viewModal("#"+IDs.themodal,{gbox:"#gbox_"+gID,jqm:p.jqModal});
				if(onAfterShow) { onAfterShow($("#"+frmgr)); }
				$(".fm-button","#"+frmtb).hover(
				   function(){$(this).addClass('ui-state-hover');}, 
				   function(){$(this).removeClass('ui-state-hover');}
				);
				$("#sData", "#"+frmtb).click(function(e){
					var postdata = {}, ret=[true,"",""], extpost={};
					$("#FormError","#"+frmtb).hide();
					// all depend on ret array
					//ret[0] - succes
					//ret[1] - msg if not succes
					//ret[2] - the id  that will be set if reload after submit false
					var j =0;
					$(".FormElement", "#"+frmtb).each(function(i){
						var suc =  true;
						switch ($(this).get(0).type) {
							case "checkbox":
								if($(this).attr("checked")) {
									postdata[this.name]= $(this).val();
								}else {
									var ofv = $(this).attr("offval");
									postdata[this.name]= ofv;
									extpost[this.name] = ofv;
								}
							break;
							case "select-one":
								postdata[this.name]= $("option:selected",this).val();
								extpost[this.name]= $("option:selected",this).text();
							break;
							case "select-multiple":
								postdata[this.name]= $(this).val();
								if(postdata[this.name]) postdata[this.name] = postdata[this.name].join(",");
								else postdata[this.name] ="";
								var selectedText = [];
								$("option:selected",this).each(
									function(i,selected){
										selectedText[i] = $(selected).text();
									}
								);
								extpost[this.name]= selectedText.join(",");
							break;								
							case "password":
							case "text":
							case "textarea":
							case "button":
								postdata[this.name] = $(this).val();
								postdata[this.name] = !$t.p.autoencode ? postdata[this.name] : htmlEncode(postdata[this.name]);
							break;
						}
						ret = checkValues(postdata[this.name],this.name,$t);
						if(ret[0] === false) {suc=false;}
						j++;
						if(!suc) { return false; }
					});
					if(j==0) { ret[0] = false; ret[1] = $.jgrid.errors.norecords; }
					if( $.isFunction( rp_ge.onclickSubmit)) { rp_ge.editData = rp_ge.onclickSubmit(p) || {}; }
					if(ret[0]) {
						if( $.isFunction(rp_ge.beforeSubmit))  { ret = rp_ge.beforeSubmit(postdata,$("#"+frmgr)); }
					}
					var gurl = rp_ge.url ? rp_ge.url : $t.p.editurl;
					if(ret[0]) {
						if(!gurl) { ret[0]=false; ret[1] += " "+$.jgrid.errors.nourl; }
					}
					if(ret[0] === false) {
						$("#FormError>td","#"+frmtb).html(ret[1]);
						$("#FormError","#"+frmtb).show();
					} else {
						if(!p.processing) {
							p.processing = true;
							$(this).addClass('ui-state-active');
							// we add to pos data array the action - the name is oper
							postdata.oper = postdata.id == "_empty" ? "add" : "edit";
							postdata = $.extend(postdata,rp_ge.editData);
							$.ajax({
								url:gurl,
								type: rp_ge.mtype,
								data:postdata,
								complete:function(data,Status){
									if(Status != "success") {
										ret[0] = false;
										ret[1] = Status+" Status: "+data.statusText +" Error code: "+data.status;
									} else {
										// data is posted successful
										// execute aftersubmit with the returned data from server
										if( $.isFunction(rp_ge.afterSubmit) ) {
											ret = rp_ge.afterSubmit(data,postdata);
										}
									}
									if(ret[0] === false) {
										$("#FormError>td","#"+frmtb).html(ret[1]);
										$("#FormError","#"+frmtb).show();
									} else {
										postdata = $.extend(postdata,extpost);
										// the action is add
										if(postdata.id=="_empty" ) {
											//id processing
											// user not set the id ret[2]
											if(!ret[2]) { ret[2] = parseInt($t.p.records)+1; }
											postdata.id = ret[2];
											if(rp_ge.closeAfterAdd) {
												if(rp_ge.reloadAfterSubmit) { $($t).trigger("reloadGrid"); }
												else {
													$($t).addRowData(ret[2],postdata,p.addedrow);
													$($t).setSelection(ret[2]);
												}
												hideModal("#"+IDs.themodal,{gb:"#gbox_"+gID,jqm:p.jqModal});
											} else if (rp_ge.clearAfterAdd) {
												if(rp_ge.reloadAfterSubmit) { $($t).trigger("reloadGrid"); }
												else { $($t).addRowData(ret[2],postdata,p.addedrow); }
												fillData("_empty",$t,frmgr);
											} else {
												if(rp_ge.reloadAfterSubmit) { $($t).trigger("reloadGrid"); }
												else { $($t).addRowData(ret[2],postdata,p.addedrow); }
											}
										} else {
											// the action is update
											if(rp_ge.reloadAfterSubmit) {
												$($t).trigger("reloadGrid");
												if( !rp_ge.closeAfterEdit ) { $($t).setSelection(postdata.id); }
											} else {
												if($t.p.treeGrid === true) {
													$($t).setTreeRow(postdata.id,postdata);
												} else {
													$($t).setRowData(postdata.id,postdata);
												}
											}
											if(rp_ge.closeAfterEdit) { hideModal("#"+IDs.themodal,{gb:"#gbox_"+gID,jqm:p.jqModal}); }
										}
										if($.isFunction(rp_ge.afterComplete)) {
											copydata = data;
											setTimeout(function(){rp_ge.afterComplete(copydata,postdata,$("#"+frmgr));copydata=null;},500);
										}
									}
									p.processing=false;
									$("#sData", "#"+frmtb).removeClass('ui-state-active');
									try{$(':input:visible',"#"+frmgr)[0].focus();} catch (e){}
								},
								error:function(xhr,st,err){
									$("#FormError>td","#"+frmtb).html(st+ " : "+err);
									$("#FormError","#"+frmtb).show();
									p.processing=false;
									$("#sData", "#"+frmtb).removeClass('ui-state-active');
								}
							});
						}
					}
					return false;
				});
				$("#cData", "#"+frmtb).click(function(e){
					hideModal("#"+IDs.themodal,{gb:"#gbox_"+gID,jqm:p.jqModal});
					return false;
				});
				$("#nData", "#"+frmtb).click(function(e){
					$("#FormError","#"+frmtb).hide();
					var npos = getCurrPos();
					npos[0] = parseInt(npos[0]);
					if(npos[0] != -1 && npos[1][npos[0]+1]) {
						if($.isFunction(p.onclickPgButtons)) {
							p.onclickPgButtons('next',$("#"+frmgr),npos[1][npos[0]]);
						}
						fillData(npos[1][npos[0]+1],$t,frmgr);
						$($t).setSelection(npos[1][npos[0]+1]);
						if($.isFunction(p.afterclickPgButtons)) {
							p.afterclickPgButtons('next',$("#"+frmgr),npos[1][npos[0]+1]);
						}
						updateNav(npos[0]+1,npos[1].length-1);
					};
					return false;
				});
				$("#pData", "#"+frmtb).click(function(e){
					$("#FormError","#"+frmtb).hide();
					var ppos = getCurrPos();
					if(ppos[0] != -1 && ppos[1][ppos[0]-1]) {
						if($.isFunction(p.onclickPgButtons)) {
							p.onclickPgButtons('prev',$("#"+frmgr),ppos[1][ppos[0]]);
						}
						fillData(ppos[1][ppos[0]-1],$t,frmgr);
						$($t).setSelection(ppos[1][ppos[0]-1]);
						if($.isFunction(p.afterclickPgButtons)) {
							p.afterclickPgButtons('prev',$("#"+frmgr),ppos[1][ppos[0]-1]);
						}
						updateNav(ppos[0]-1,ppos[1].length-1);
					};
					return false;
				});
			};
			var posInit =getCurrPos();
			updateNav(posInit[0],posInit[1].length-1);
			function updateNav(cr,totr,rid){
				if (cr==0) { $("#pData","#"+frmtb).addClass('ui-state-disabled'); } else { $("#pData","#"+frmtb).removeClass('ui-state-disabled'); }
				if (cr==totr) { $("#nData","#"+frmtb).addClass('ui-state-disabled'); } else { $("#nData","#"+frmtb).removeClass('ui-state-disabled'); }
			}
			function getCurrPos() {
				var rowsInGrid = $($t).getDataIDs(),
				selrow = $("#id_g","#"+frmtb).val(),
				pos = $.inArray(selrow,rowsInGrid);
				return [pos,rowsInGrid];
			}
			function createData(rowid,obj,tb,maxcols){
				var nm, hc,trdata, cnt=0,tmp, dc,elc, retpos=[], ind=false, rp,cp,
				tdtmpl = "<td class='CaptionTD ui-widget-content'>&nbsp;</td><td class='DataTD ui-widget-content'>&nbsp;</td>", tmpl=""; //*2				
				for (var i =1;i<=maxcols;i++) {
					tmpl += tdtmpl;
				}
				if(rowid != '_empty') {
					ind = $(obj).getInd(obj.rows,rowid);
				}
				$(obj.p.colModel).each( function(i) {
					nm = this.name;
					// hidden fields are included in the form
					if(this.editrules && this.editrules.edithidden == true) {
						hc = false;
					} else {
						hc = this.hidden === true ? true : false;
					}
					dc = hc ? "style='display:none'" : "";
					if ( nm !== 'cb' && nm !== 'subgrid' && this.editable===true && nm !== 'rn') {
						if(ind === false) {
							tmp = "";
						} else {
							if(nm == obj.p.ExpandColumn && obj.p.treeGrid === true) {
								tmp = $("td:eq("+i+")",obj.rows[ind]).text();
							} else {
								try {
									tmp =  $.unformat($("td:eq("+i+")",obj.rows[ind]),{colModel:this},i);
								} catch (_) {
									tmp = $("td:eq("+i+")",obj.rows[ind]).html();
								}
							}
						}
						var opt = $.extend({}, this.editoptions || {} ,{id:nm,name:nm});
						frmopt = $.extend({}, {elmprefix:'',elmsuffix:''}, this.formoptions || {}),
						rp = parseInt(frmopt.rowpos) || cnt+1,
						cp = parseInt((parseInt(frmopt.colpos) || 1)*2);
						if(rowid == "_empty" && opt.defaultValue ) {
							tmp = $.isFunction(opt.defaultValue) ? opt.defaultValue() : opt.defaultValue; 
						}
						if(!this.edittype) this.edittype = "text";
						elc = createEl(this.edittype,opt,tmp);
						$(elc).addClass("FormElement");
						trdata = $(tb).find("tr[rowpos="+rp+"]");
						if ( trdata.length==0 ) {
							trdata = $("<tr "+dc+" rowpos='"+rp+"'></tr>").addClass("FormData").attr("id","tr_"+nm);
							$(trdata).append(tmpl);
							$(tb).append(trdata);
							trdata[0].rp = rp;
						}
						$("td:eq("+(cp-2)+")",trdata[0]).html( typeof frmopt.label === 'undefined' ? obj.p.colNames[i]: frmopt.label);
						$("td:eq("+(cp-1)+")",trdata[0]).append(frmopt.elmprefix).append(elc).append(frmopt.elmsuffix);
						retpos[cnt] = i;
						cnt++;
					};
				});
				if( cnt > 0) {
					var idrow = $("<tr class='FormData' style='display:none'><td class='CaptionTD'></td><td colspan='"+ (maxcols*2-1)+"' class='DataTD'><input class='FormElement' id='id_g' type='text' name='id' value='"+rowid+"'/></td></tr>");
					idrow[0].rp = cnt+999;
					$(tb).append(idrow);
				}
				return retpos;
			}
			function fillData(rowid,obj,fmid){
				var nm, hc,cnt=0,tmp, fld,opt,vl;
				if(rowid == '_empty') {
					$(obj.p.colModel).each(function(i){
						nm = this.name;
						opt = $.extend({}, this.editoptions || {} );
						fld = $("#"+nm,"#"+fmid);
						if(fld[0] != null) {
							if(opt.defaultValue ) {
								vl = $.isFunction(opt.defaultValue) ? opt.defaultValue() : opt.defaultValue;
								if(fld[0].type=='checkbox') {
									vl = vl.toLowerCase();
									if(vl.search(/(false|0|no|off|undefined)/i)<0 && vl!=="") {
										fld[0].checked = true;
										fld[0].defaultChecked = true;
										fld[0].value = vl;
									} else {
										fld.attr({checked:"",defaultChecked:""});
									}
								} else {fld.val(vl); }
							} else {
								fld.val("");
							}
							
						}
					});
					$("#id_g","#"+fmid).val("_empty");
					return;
				}
				$('table:first tr#'+rowid+' td',obj.grid.bDiv).each( function(i) {
					nm = obj.p.colModel[i].name;
					// hidden fields are included in the form
					if(obj.p.colModel[i].editrules && obj.p.colModel[i].editrules.edithidden === true) {
						hc = false;
					} else {
						hc = obj.p.colModel[i].hidden === true ? true : false;
					}
					if ( nm !== 'cb' && nm !== 'subgrid' && obj.p.colModel[i].editable===true) {
						if(nm == obj.p.ExpandColumn && obj.p.treeGrid === true) {
							tmp = $(this).text();
						} else {
							try {
								tmp =  $.unformat(this,{colModel:obj.p.colModel[i]},i);
							} catch (_) {
								tmp = $(this).html();
							}
						}
						nm = nm.replace('.',"\\.");
						switch (obj.p.colModel[i].edittype) {
							case "password":
							case "text":
							case "button" :
							case "image":
								tmp = $.htmlDecode(tmp);
								$("#"+nm,"#"+fmid).val(tmp);
								break;
							case "textarea":
								if(tmp == "&nbsp;" || tmp == "&#160;" || (tmp.length==1 && tmp.charCodeAt(0)==160) ) {tmp='';}
								$("#"+nm,"#"+fmid).val(tmp);
								break;
							case "select":
								tmp = $.htmlDecode(tmp);
								$("#"+nm+" option","#"+fmid).each(function(j){
									if (!obj.p.colModel[i].editoptions.multiple && tmp == $(this).text() ){
										this.selected= true;
									} else if (obj.p.colModel[i].editoptions.multiple){
										if(  $.inArray($(this).text(), tmp.split(",") ) > -1  ){
											this.selected = true;
										}else{
											this.selected = false;
										}
									} else {
										this.selected = false;
									}
								});
								break;
							case "checkbox":
								tmp = tmp.toLowerCase();
								if(tmp.search(/(false|0|no|off|undefined)/i)<0 && tmp!=="") {
									$("#"+nm,"#"+fmid).attr("checked",true);
									$("#"+nm,"#"+fmid).attr("defaultChecked",true); //ie
								} else {
									$("#"+nm,"#"+fmid).attr("checked",false);
									$("#"+nm,"#"+fmid).attr("defaultChecked",""); //ie
								}
								break; 
						}
						cnt++;
					}
				});
				if(cnt>0) { $("#id_g","#"+frmtb).val(rowid); }
				return cnt;
			}
		});
	},
	viewGridRow : function(rowid, p){
		p = $.extend({
			top : 0,
			left: 0,
			width: 0,
			height: 0,
			modal: false,
			drag: true,
			resize: true,
			jqModal: true,
			closeOnEscape : false,
			labelswidth: '30%',
			closeicon: [],
			navkeys: [false,38,40]
		}, $.jgrid.view, p || {});
		return this.each(function(){
			var $t = this;
			if (!$t.grid || !rowid) { return; }
			if(!p.imgpath) { p.imgpath= $t.p.imgpath; }
			// I hate to rewrite code, but ...
			var gID = $t.p.id,
			IDs = {themodal:'viewmod'+gID,modalhead:'viewhd'+gID,modalcontent:'viewcnt'+gID},
			maxCols = 1, maxRows=0,
			frmgr = "ViewGrid_"+gID , frmtb = "ViewTbl_"+gID;
			if ( $("#"+IDs.themodal).html() != null ) {
				$(".ui-jqdialog-title","#"+IDs.modalhead).html(p.caption);
				$("#FormError","#"+frmtb).hide();
				fillData(rowid,$t);
				viewModal("#"+IDs.themodal,{gbox:"#gbox_"+gID,jqm:p.jqModal});
				focusaref();
			} else {
				$($t.p.colModel).each( function(i) {
					var fmto = this.formoptions;
					maxCols = Math.max(maxCols, fmto ? fmto.colpos || 0 : 0 );
					maxRows = Math.max(maxRows, fmto ? fmto.rowpos || 0 : 0 );
				});				
				var flr, frm = $("<form name='FormPost' id='"+frmgr+"' class='FormGrid'></form>"),
				tbl =$("<table id='"+frmtb+"' class='EditTable' cellspacing='1' cellpading='2' border='0' style='table-layout:fixed'><tbody></tbody></table>");
				// set the id.
				$(frm).append(tbl);
				var valref = createData(rowid, $t, tbl, maxCols),
				// buttons at footer
				bP = "<a href='javascript:void(0)' id='pData' class='fm-button ui-state-default ui-corner-left'><span class='ui-icon ui-icon-triangle-1-w'></span></div>",
				bN = "<a href='javascript:void(0)' id='nData' class='fm-button ui-state-default ui-corner-right'><span class='ui-icon ui-icon-triangle-1-e'></span></div>",
				bC  ="<a href='javascript:void(0)' id='cData' class='fm-button ui-state-default ui-corner-all'>"+p.bClose+"</a>";
				flr = $("<tr id='Act_Buttons'><td class='navButton ui-widget-content'>"+bP+bN+"</td><td colspan='"+(maxCols*2-1)+"'class='EditButton ui-widget-content'>"+bC+"</td></tr>");
				$(tbl).append(flr);
				flr[0].rp = valref.length + 100;
				if(maxRows >  0) {
					var sd=[];
					$.each($(tbl)[0].rows,function(i,r){
						sd[i] = r;
					});
					sd.sort(function(a,b){
						if(a.rp > b.rp) {return 1;}
						if(a.rp < b.rp) {return -1;}
						return 0;
					});
					$.each(sd, function(index, row) {
						$('tbody',tbl).append(row);
					});
				}
				p.gbox = "#gbox_"+gID;
				var cle = false;
				if(p.closeOnEscape===true){
					p.closeOnEscape = false;
					cle = true;
				}				
				createModal(IDs,frm,p,"#gview_"+$t.p.id,$("#gview_"+$t.p.id)[0]);
				jQuery("#"+IDs.themodal).keydown( function( e ) {
					if(e.which === 27) {
						if(cle)	hideModal(this,{gb:p.gbox,jqm:p.jqModal});
						return false;
					}
					if(p.navkeys[0]===true) {
						if(e.which === p.navkeys[1]){ //up
							$("#pData", "#"+frmtb).trigger("click");
							return false;
						}
						if(e.which === p.navkeys[2]){ //down
							$("#nData", "#"+frmtb).trigger("click");
							return false;
						}
					}
				});
				p.closeicon = $.extend([true,"left","ui-icon-close"],p.closeicon);
				if(p.closeicon[0]==true) {
					$("#cData","#"+frmtb).addClass(p.closeicon[1] == "right" ? 'fm-button-icon-right' : 'fm-button-icon-left')
					.append("<span class='ui-icon "+p.closeicon[2]+"'></span>");
				}
				viewModal("#"+IDs.themodal,{gbox:"#gbox_"+gID,jqm:p.jqModal});
				$(".fm-button:not(.ui-state-disabled)","#"+frmtb).hover(
				   function(){$(this).addClass('ui-state-hover');}, 
				   function(){$(this).removeClass('ui-state-hover');}
				);
				focusaref();
				$("#cData", "#"+frmtb).click(function(e){
					hideModal("#"+IDs.themodal,{gb:"#gbox_"+gID,jqm:p.jqModal});
					e.stopPropagation();
					return false;
				});
				$("#nData", "#"+frmtb).click(function(e){
					$("#FormError","#"+frmtb).hide();
					var npos = getCurrPos();
					npos[0] = parseInt(npos[0]);
					if(npos[0] != -1 && npos[1][npos[0]+1]) {
						if($.isFunction(p.onclickPgButtons)) {
							p.onclickPgButtons('next',$("#"+frmgr),npos[1][npos[0]]);
						}
						fillData(npos[1][npos[0]+1],$t);
						$($t).setSelection(npos[1][npos[0]+1]);
						if($.isFunction(p.afterclickPgButtons)) {
							p.afterclickPgButtons('next',$("#"+frmgr),npos[1][npos[0]+1]);
						}
						updateNav(npos[0]+1,npos[1].length-1);
					};
					focusaref();
					return false;
				});
				$("#pData", "#"+frmtb).click(function(e){
					$("#FormError","#"+frmtb).hide();
					var ppos = getCurrPos();
					if(ppos[0] != -1 && ppos[1][ppos[0]-1]) {
						if($.isFunction(p.onclickPgButtons)) {
							p.onclickPgButtons('prev',$("#"+frmgr),ppos[1][ppos[0]]);
						}
						fillData(ppos[1][ppos[0]-1],$t);
						$($t).setSelection(ppos[1][ppos[0]-1]);
						if($.isFunction(p.afterclickPgButtons)) {
							p.afterclickPgButtons('prev',$("#"+frmgr),ppos[1][ppos[0]-1]);
						}
						updateNav(ppos[0]-1,ppos[1].length-1);
					};
					focusaref();
					return false;
				});
			};
			function focusaref(){ //Sfari 3 issues
				if(p.closeOnEscape===true || p.navkeys[0]===true) {
					setTimeout(function(){$(".ui-jqdialog-titlebar-close","#"+IDs.modalhead).focus()},0);
				}
			}
			var posInit =getCurrPos();
			updateNav(posInit[0],posInit[1].length-1);
			function updateNav(cr,totr,rid){
				if (cr==0) { $("#pData","#"+frmtb).addClass('ui-state-disabled'); } else { $("#pData","#"+frmtb).removeClass('ui-state-disabled'); }
				if (cr==totr) { $("#nData","#"+frmtb).addClass('ui-state-disabled'); } else { $("#nData","#"+frmtb).removeClass('ui-state-disabled'); }
			}
			function getCurrPos() {
				var rowsInGrid = $($t).getDataIDs(),
				selrow = $("#id_g","#"+frmtb).val(),
				pos = $.inArray(selrow,rowsInGrid);
				return [pos,rowsInGrid];
			}
			function createData(rowid,obj,tb,maxcols){
				var nm, hc,trdata, tdl, tde, cnt=0,tmp, dc,elc, retpos=[], ind=false,
				tdtmpl = "<td class='CaptionTD ui-widget-content' width='"+p.labelswidth+"'>&nbsp;</td><td class='DataTD ui-helper-reset ui-widget-content'>&nbsp;</td>", tmpl="",
				tdtmpl2 = "<td class='CaptionTD ui-widget-content'>&nbsp;</td><td class='DataTD ui-widget-content'>&nbsp;</td>",
				fmtnum = ['integer','number','currency'],max1 =0, max2=0 ,maxw,setme;
				for (var i =1;i<=maxcols;i++) {
					tmpl += i == 1 ? tdtmpl : tdtmpl2;
				}
				// find max number align rigth with property formatter
				$(obj.p.colModel).each( function(i) {
					if(this.editrules && this.editrules.edithidden === true) {
						hc = false;
					} else {
						hc = this.hidden === true ? true : false;
					}
					if(!hc && this.align==='right') {
						if(this.formatter && $.inArray(this.formatter,fmtnum) !== -1 ) {
							max1 = Math.max(max1,parseInt(this.width,10));
						} else {
							max2 = Math.max(max2,parseInt(this.width,10));
						}
					}
				});
				maxw  = max1 !==0 ? max1 : max2 !==0 ? max2 : 0;
				ind = $(obj).getInd(obj.rows,rowid);
				$(obj.p.colModel).each( function(i) {
					nm = this.name;
					setme = false;
					// hidden fields are included in the form
					if(this.editrules && this.editrules.edithidden === true) {
						hc = false;
					} else {
						hc = this.hidden === true ? true : false;
					}
					dc = hc ? "style='display:none'" : "";
					if ( nm !== 'cb' && nm !== 'subgrid' && this.editable===true) {
						if(ind === false) {
							tmp = "";
						} else {
							if(nm == obj.p.ExpandColumn && obj.p.treeGrid === true) {
								tmp = $("td:eq("+i+")",obj.rows[ind]).text();
							} else {
								tmp = $("td:eq("+i+")",obj.rows[ind]).html();
							}
						}
						setme = this.align === 'right' && maxw !==0 ? true : false;
						var opt = $.extend({}, this.editoptions || {} ,{id:nm,name:nm}),
						frmopt = $.extend({}, this.formoptions || {}),
						rp = parseInt(frmopt.rowpos) || cnt+1,
						cp = parseInt((parseInt(frmopt.colpos) || 1)*2);
						if(!this.edittype) this.edittype = "text";
						trdata = $(tb).find("tr[rowpos="+rp+"]");
						if ( trdata.length==0 ) {
							trdata = $("<tr "+dc+" rowpos='"+rp+"'></tr>").addClass("FormData").attr("id","tr_"+nm);
							$(trdata).append(tmpl);
							$(tb).append(trdata);
							trdata[0].rp = rp;
						}
						$("td:eq("+(cp-2)+")",trdata[0]).html('<b>'+ (typeof frmopt.label === 'undefined' ? obj.p.colNames[i]: frmopt.label)+'</b>');
						$("td:eq("+(cp-1)+")",trdata[0]).append("<span style='position:absolute;float:left;'>"+tmp+"</span>").attr("id",nm);
						if(setme){
							$("td:eq("+(cp-1)+") span",trdata[0]).css({'text-align':'right',width:maxw+"px"});
						}
						retpos[cnt] = i;
						cnt++;
					};
				});
				if( cnt > 0) {
					var idrow = $("<tr class='FormData' style='display:none'><td class='CaptionTD'></td><td colspan='"+ (maxcols*2-1)+"' class='DataTD'><input class='FormElement' id='id_g' type='text' name='id' value='"+rowid+"'/></td></tr>");
					idrow[0].rp = cnt+99;
					$(tb).append(idrow);
				}
				return retpos;
			};
			function fillData(rowid,obj){
				var nm, hc,cnt=0,tmp, opt;
				$('#'+rowid+' td',obj.grid.bDiv).each( function(i) {
					nm = obj.p.colModel[i].name;
					// hidden fields are included in the form
					if(obj.p.colModel[i].editrules && obj.p.colModel[i].editrules.edithidden === true) {
						hc = false;
					} else {
						hc = obj.p.colModel[i].hidden === true ? true : false;
					}
					if ( nm !== 'cb' && nm !== 'subgrid' && obj.p.colModel[i].editable===true) {
						if(nm == obj.p.ExpandColumn && obj.p.treeGrid === true) {
							tmp = $(this).text();
						} else {
							tmp = $(this).html();
						}
						nm= nm.replace('.',"\\.");
						opt = $.extend({},obj.p.colModel[i].editoptions || {});
						$("#"+nm+" span","#"+frmtb).html(tmp);
						if (hc) { $("#"+nm,"#"+frmtb).parents("tr:first").hide(); }
						cnt++;
					}
				});
				if(cnt>0) { $("#id_g","#"+frmtb).val(rowid); }
				return cnt;
			};
		});
	},
	delGridRow : function(rowids,p) {
		p = $.extend({
			top : 0,
			left: 0,
			width: 240,
			height: 'auto',
			modal: false,
			drag: true,
			resize: true,
			url : '',
			mtype : "POST",
			reloadAfterSubmit: true,
			beforeShowForm: null,
			afterShowForm: null,
			beforeSubmit: null,
			onclickSubmit: null,
			afterSubmit: null,
			onclickSubmit: null,
			jqModal : true,
			closeOnEscape : false,
			delData: {},
			delicon : [],
			cancelicon : []
		}, $.jgrid.del, p ||{});
		rp_ge = p;
		return this.each(function(){
			var $t = this;
			if (!$t.grid ) { return; }
			if(!rowids) { return; }
			var onBeforeShow = typeof p.beforeShowForm === 'function' ? true: false,
			onAfterShow = typeof p.afterShowForm === 'function' ? true: false,
			gID = $t.p.id,
			IDs = {themodal:'delmod'+gID,modalhead:'delhd'+gID,modalcontent:'delcnt'+gID},
			dtbl = "DelTbl_"+gID;
			if (isArray(rowids)) { rowids = rowids.join(); }
			if ( $("#"+IDs.themodal).html() != null ) {
				$("#DelData>td","#"+dtbl).text(rowids);
				$("#DelError","#"+dtbl).hide();
				if( p.processing === true) {
					p.processing=false;
					$("#dData", "#"+dtbl).removeClass('ui-state-active');
				}
				if(onBeforeShow) { p.beforeShowForm($("#"+dtbl)); }
				viewModal("#"+IDs.themodal,{gbox:"#gbox_"+gID,jqm:p.jqModal});
				if(onAfterShow) { p.afterShowForm($("#"+dtbl)); }
			} else {
				var tbl =$("<table id='"+dtbl+"' class='DelTable' width='100%'><tbody></tbody></table>");
				// error data 
				$(tbl).append("<tr id='DelError' style='display:none'><td >"+"&nbsp;"+"</td></tr>");
				$(tbl).append("<tr id='DelData' style='display:none'><td >"+rowids+"</td></tr>");
				$(tbl).append("<tr><td >"+p.msg+"</td></tr><tr><td >&nbsp;</td></tr>");
				// buttons at footer
				var bS  = "<a href='javascript:void(0)' id='dData' class='fm-button ui-state-default ui-corner-all'>"+p.bSubmit+"</a>",
				bC  = "<a href='javascript:void(0)' id='eData' class='fm-button ui-state-default ui-corner-all'>"+p.bCancel+"</a>";
				$(tbl).append("<tr><td align='right' class='DelButton'>"+bS+"&nbsp;"+bC+"</td></tr>");
				p.gbox = "#gbox_"+gID;
				createModal(IDs,tbl,p,"#gview_"+$t.p.id,$("#gview_"+$t.p.id)[0]);
				$(".fm-button","#"+dtbl).hover(
				   function(){$(this).addClass('ui-state-hover');}, 
				   function(){$(this).removeClass('ui-state-hover');}
				);
				p.delicon = $.extend([true,"left","ui-icon-scissors"],p.delicon);
				p.cancelicon = $.extend([true,"left","ui-icon-cancel"],p.cancelicon);
				if(p.delicon[0]==true) {
					$("#dData","#"+dtbl).addClass(p.delicon[1] == "right" ? 'fm-button-icon-right' : 'fm-button-icon-left')
					.append("<span class='ui-icon "+p.delicon[2]+"'></span>");
				}
				if(p.cancelicon[0]==true) {
					$("#eData","#"+dtbl).addClass(p.cancelicon[1] == "right" ? 'fm-button-icon-right' : 'fm-button-icon-left')
					.append("<span class='ui-icon "+p.cancelicon[2]+"'></span>");
				}				
				$("#dData","#"+dtbl).click(function(e){
					var ret=[true,""];
					var postdata = $("#DelData>td","#"+dtbl).text(); //the pair is name=val1,val2,...
					if( typeof p.onclickSubmit === 'function' ) { p.delData = p.onclickSubmit(p) || {}; }
					if( typeof p.beforeSubmit === 'function' ) { ret = p.beforeSubmit(postdata); }
					var gurl = rp_ge.url ? rp_ge.url : $t.p.editurl;
					if(!gurl) { ret[0]=false;ret[1] += " "+$.jgrid.errors.nourl;}
					if(ret[0] === false) {
						$("#DelError>td","#"+dtbl).html(ret[1]);
						$("#DelError","#"+dtbl).show();
					} else {
						if(!p.processing) {
							p.processing = true;
							$(this).addClass('ui-state-active');
							var postd = $.extend({oper:"del", id:postdata},p.delData);
							$.ajax({
								url:gurl,
								type: p.mtype,
								data:postd,
								complete:function(data,Status){
									if(Status != "success") {
										ret[0] = false;
										ret[1] = Status+" Status: "+data.statusText +" Error code: "+data.status;
									} else {
										// data is posted successful
										// execute aftersubmit with the returned data from server
										if( typeof rp_ge.afterSubmit === 'function' ) {
											ret = rp_ge.afterSubmit(data,postdata);
										}
									}
									if(ret[0] === false) {
										$("#DelError>td","#"+dtbl).html(ret[1]);
										$("#DelError","#"+dtbl).show();
									} else {
										if(rp_ge.reloadAfterSubmit) {
											if($t.p.treeGrid) {
												$($t).setGridParam({treeANode:0,datatype:$t.p.treedatatype});
											}
											$($t).trigger("reloadGrid");
										} else {
											var toarr = [];
											toarr = postdata.split(",");
											if($t.p.treeGrid===true){
												try {$($t).delTreeNode(toarr[0])} catch(e){}
											} else {
												for(var i=0;i<toarr.length;i++) {
													$($t).delRowData(toarr[i]);
												}
											}
											$t.p.selrow = null;
											$t.p.selarrrow = [];
										}
										if($.isFunction(rp_ge.afterComplete)) {
											setTimeout(function(){rp_ge.afterComplete(data,postdata);},500);
										}
									}
									p.processing=false;
									$("#dData", "#"+dtbl).removeClass('ui-state-active');
									if(ret[0]) { hideModal("#"+IDs.themodal,{gb:"#gbox_"+gID,jqm:p.jqModal}); }
								},
								error:function(xhr,st,err){
									$("#DelError>td","#"+dtbl).html(st+ " : "+err);
									$("#DelError","#"+dtbl).show();
									p.processing=false;
									$("#dData", "#"+dtbl).removeClass('ui-state-active');;
								}
							});
						}
					}
					return false;
				});
				$("#eData", "#"+dtbl).click(function(e){
					hideModal("#"+IDs.themodal,{gb:"#gbox_"+gID,jqm:p.jqModal});
					return false;
				});
				if(onBeforeShow) { p.beforeShowForm($("#"+dtbl)); }
				viewModal("#"+IDs.themodal,{gbox:"#gbox_"+gID,jqm:p.jqModal});
				if(onAfterShow) { p.afterShowForm($("#"+dtbl)); }
			}
			if(p.closeOnEscape===true) {
				setTimeout(function(){$(".ui-jqdialog-titlebar-close","#"+IDs.modalhead).focus()},0);
			}
		});
	},
	navGrid : function (elem, o, pEdit,pAdd,pDel,pSearch, pView) {
		o = $.extend({
			edit: true,
			editicon: "ui-icon-pencil",
			add: true,
			addicon:"ui-icon-plus",
			del: true,
			delicon:"ui-icon-trash",
			search: true,
			searchicon:"ui-icon-search",
			refresh: true,
			refreshicon:"ui-icon-refresh",
			refreshstate: 'firstpage',
			view: false,
			viewicon : "ui-icon-document",
			position : "left",
			closeOnEscape : true
		}, $.jgrid.nav, o ||{});
		return this.each(function() {       
			var alertIDs = {themodal:'alertmod',modalhead:'alerthd',modalcontent:'alertcnt'},
			$t = this, vwidth, vheight, twd, tdw;
			if(!$t.grid) { return; }
			if ($("#"+alertIDs.themodal).html() == null) {
				if (typeof window.innerWidth != 'undefined') {
					vwidth = window.innerWidth,
					vheight = window.innerHeight
				} else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
					vwidth = document.documentElement.clientWidth,
					vheight = document.documentElement.clientHeight
				} else {
					vwidth=1024;
					vheight=768;
				}
				createModal(alertIDs,"<div>"+o.alerttext+"</div><span tabindex='0'><span tabindex='-1' id='jqg_alrt'></span></span>",{gbox:"#gbox_"+$t.p.id,jqModal:true,drag:true,resize:true,caption:o.alertcap,top:vheight/2-25,left:vwidth/2-100,width:200,height:'auto',closeOnEscape:o.closeOnEscape},$t.grid.hDiv,$t.grid.hDiv,true);
			}
			var tbd,
			navtbl = $("<table cellspacing='0' cellpadding='0' border='0' class='ui-pg-table navtable' style='float:left;table-layout:auto;'><tbody><tr></tr></tbody></table>"),
			sep = "<td class='ui-pg-button ui-state-disabled' style='width:4px;'><span class='ui-separator'></span></td>",
			pgid = $($t.p.pager).attr("id") || 'pager';
			if (o.add) {
				pAdd = pAdd || {};
				tbd = $("<td class='ui-pg-button ui-corner-all'></td>");
				$(tbd).append("<div class='ui-pg-div'><span class='ui-icon "+o.addicon+"'></span>"+o.addtext+"</div>");
				$("tr",navtbl).append(tbd);
				$(tbd,navtbl)
				.attr({"title":o.addtitle || "",id : pAdd.id || "add_"+$t.p.id})
				.click(function(){
					if (typeof o.addfunc == 'function') {
						o.addfunc();
					} else {
						$($t).editGridRow("new",pAdd);
					}
					return false;
				}).hover(function () {$(this).addClass("ui-state-hover");},
					function () {$(this).removeClass("ui-state-hover");}
				);
				tbd = null;
			}
			if (o.edit) {
				tbd = $("<td class='ui-pg-button ui-corner-all'></td>");
				pEdit = pEdit || {};
				$(tbd).append("<div class='ui-pg-div'><span class='ui-icon "+o.editicon+"'></span>"+o.edittext+"</div>");
				$("tr",navtbl).append(tbd);
				$(tbd,navtbl)
				.attr({"title":o.edittitle || "",id: pEdit.id || "edit_"+$t.p.id})
				.click(function(){
					var sr = $t.p.selrow;
					if (sr) {
						if(typeof o.editfunc == 'function') {
							o.editfunc(sr);
						} else {
							$($t).editGridRow(sr,pEdit);
						}
					} else {
						viewModal("#"+alertIDs.themodal,{gbox:"#gbox_"+$t.p.id,jqm:true});
						$("#jqg_alrt").focus();
					}
					return false;
				}).hover( function () {$(this).addClass("ui-state-hover");},
					function () {$(this).removeClass("ui-state-hover");}
				);
				tbd = null;
			}
			if (o.view) {
				tbd = $("<td class='ui-pg-button ui-corner-all'></td>");
				pView = pView || {};
				$(tbd).append("<div class='ui-pg-div'><span class='ui-icon "+o.viewicon+"'></span>"+o.viewtext+"</div>");
				$("tr",navtbl).append(tbd);
				$(tbd,navtbl)
				.attr({"title":o.viewtitle || "",id: pView.id || "view_"+$t.p.id})
				.click(function(){
					var sr = $t.p.selrow;
					if (sr) {
						$($t).viewGridRow(sr,pView);
					} else {
						viewModal("#"+alertIDs.themodal,{gbox:"#gbox_"+$t.p.id,jqm:true});
						$("#jqg_alrt").focus();
					}
					return false;
				}).hover( function () {$(this).addClass("ui-state-hover");},
					function () {$(this).removeClass("ui-state-hover");}
				);
				tbd = null;
			}
			if (o.del) {
				tbd = $("<td class='ui-pg-button ui-corner-all'></td>");
				pDel = pDel || {};
				$(tbd).append("<div class='ui-pg-div'><span class='ui-icon "+o.delicon+"'></span>"+o.deltext+"</div>");
				$("tr",navtbl).append(tbd);
				$(tbd,navtbl)
				.attr({"title":o.deltitle || "",id: pDel.id || "del_"+$t.p.id})
				.click(function(){
					var dr;
					if($t.p.multiselect) {
						dr = $t.p.selarrrow;
						if(dr.length==0) { dr = null; }
					} else {
						dr = $t.p.selrow;
					}
					if (dr) { $($t).delGridRow(dr,pDel); }
					else  {viewModal("#"+alertIDs.themodal,{gbox:"#gbox_"+$t.p.id,jqm:true}); $("#jqg_alrt").focus(); }
					return false;
				}).hover(function () {$(this).addClass("ui-state-hover");},
					function () {$(this).removeClass("ui-state-hover");}
				);
				tbd = null;
			}
			if(o.add || o.edit || o.del || o.view) { $("tr",navtbl).append(sep); }
			if (o.search) {
				tbd = $("<td class='ui-pg-button ui-corner-all'></td>");
				pSearch = pSearch || {};
				$(tbd).append("<div class='ui-pg-div'><span class='ui-icon "+o.searchicon+"'></span>"+o.searchtext+"</div>");
				$("tr",navtbl).append(tbd);
				$(tbd,navtbl)
				.attr({"title":o.searchtitle  || "",id:pSearch.id || "search_"+$t.p.id})
				.click(function(){
					$($t).searchGrid(pSearch);
					return false;
				}).hover(function () {$(this).addClass("ui-state-hover");},
					function () {$(this).removeClass("ui-state-hover");}
				);
				tbd = null;
			}
			if (o.refresh) {
				tbd = $("<td class='ui-pg-button ui-corner-all'></td>");
				$(tbd).append("<div class='ui-pg-div'><span class='ui-icon "+o.refreshicon+"'></span>"+o.refreshtext+"</div>");
				$("tr",navtbl).append(tbd);
				$(tbd,navtbl)
				.attr({"title":o.refreshtitle  || "",id: "refresh_"+$t.p.id})
				.click(function(){
					$t.p.search = false;
					switch (o.refreshstate) {
						case 'firstpage':
							$t.p.page=1;
							$($t).trigger("reloadGrid");
							break;
						case 'current':
							var sr = $t.p.multiselect===true ? $t.p.selarrrow : $t.p.selrow;
							$($t).trigger("reloadGrid");
							setTimeout(function(){
								if($t.p.multiselect===true) {
									if(sr.length>0) {
										for(var i=0;i<sr.length;i++){
											$($t).setSelection(sr[i],false);
										}
									}
								} else {
									if(sr) {
										$($t).setSelection(sr,false);
									}
								}
							},1000);
							break;
					}
					if(o.search) {
						var gID = $t.p.id;
						$("#fbox_"+gID).searchFilter().reset();
					}
					return false;
				}).hover(function () {$(this).addClass("ui-state-hover");},
					function () {$(this).removeClass("ui-state-hover");}
				);
				tbd = null;
			}
			tdw = $(".ui-jqgrid").css("font-size") || "11px";
			$('body').append("<div id='testpg' class='ui-jqgrid ui-widget ui-widget-content' style='font-size:"+tdw+";visibility:hidden;' ></div>");
			twd = $(navtbl).clone(false).appendTo("#testpg").width();
			$("#testpg").remove();
			$("#"+pgid+"_"+o.position,"#"+pgid).append(navtbl);
			if($t.p._nvtd) {
				if(twd > $t.p._nvtd[0] ) {
					$("#"+pgid+"_"+o.position,"#"+pgid).width(twd);
					$t.p._nvtd[0] = twd;
				}
				$t.p._nvtd[1] = twd;
			}
		});
	},
	navButtonAdd : function (elem, p) {
		p = $.extend({
			caption : "newButton",
			title: '',
			buttonicon : 'ui-icon-newwin',
			onClickButton: null,
			position : "last"
		}, p ||{});
		return this.each(function() {
			if( !this.grid)  { return; }
			if( elem.indexOf("#") != 0) { elem = "#"+elem; }
			var findnav = $(".navtable",elem)[0];
			if (findnav) {
				var tbd = $("<td></td>");
				$(tbd).addClass('ui-pg-button ui-corner-all').append("<div class='ui-pg-div'><span class='ui-icon "+p.buttonicon+"'></span>"+p.caption+"</div>");
				if(p.id) {$(tbd).attr("id",p.id);}
				if(p.position=='first'){
					if(findnav.rows[0].cells.length ===0 ) {
						$("tr",findnav).append(tbd);
					} else {
						$("tr td:eq(0)",findnav).before(tbd);
					}
				} else {
					$("tr",findnav).append(tbd);
				}
				$(tbd,findnav)
				.attr("title",p.title  || "")
				.click(function(e){
					if ($.isFunction(p.onClickButton) ) { p.onClickButton(); }
					return false;
				})
				.hover(
					function () {$(this).addClass("ui-state-hover");},
					function () {$(this).removeClass("ui-state-hover");}
				);
			}
		});
	},
	GridToForm : function( rowid, formid ) {
		return this.each(function(){
			var $t = this;
			if (!$t.grid) { return; } 
			var rowdata = $($t).getRowData(rowid);
			if (rowdata) {
				for(var i in rowdata) {
					if ( $("[name="+i+"]",formid).is("input:radio") )  {
						$("[name="+i+"]",formid).each( function() {
							if( $(this).val() == rowdata[i] ) {
								$(this).attr("checked","checked");
							} else {
								$(this).attr("checked","");
							}
						});
					} else {
					// this is very slow on big table and form.
						$("[name="+i+"]",formid).val(rowdata[i]);
					}
				}
			}
		});
	},
	FormToGrid : function(rowid, formid){
		return this.each(function() {
			var $t = this;
			if(!$t.grid) { return; }
			var fields = $(formid).serializeArray();
			var griddata = {};
			$.each(fields, function(i, field){
				griddata[field.name] = field.value;
			});
			$($t).setRowData(rowid,griddata);
		});
	}
});
})(jQuery);
