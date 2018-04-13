/*
http://stackoverflow.com/questions/512528/set-cursor-position-in-html-textbox
*/

function mirai_set_caretpos(el, pos) {
	if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
		el.focus();
		el.setSelectionRange(pos,pos);
	} else {
		var range = el.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
}

/*
http://stackoverflow.com/questions/235411/is-there-an-internet-explorer-approved-substitute-for-selectionstart-and-selecti
*/

var mirai_bbced_current_extra=[];
var mirai_bbced_selection_start=0;
var mirai_bbced_selection_end=0;

function mirai_get_input_selection(el,editorname) {
	var start = 0, end = 0, bypass=false, normalizedValue, range,
		textInputRange, len, endRange;
	
	if(typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
		start = el.selectionStart;
		end = el.selectionEnd;
	} else {
		range = document.selection.createRange();
		if (range) {
			if(range.parentElement()==el) {
				len = el.value.length;
				normalizedValue = el.value.replace(/\r\n/g, "\n");
				textInputRange = el.createTextRange();
				textInputRange.moveToBookmark(range.getBookmark());
				endRange = el.createTextRange();
				endRange.collapse(false);
				if(textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
					end = len;
					start = end;
				} else {
					start = -textInputRange.moveStart("character", -len);
					start += normalizedValue.slice(0, start).split("\n").length - 1;
		
					if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
						end = len;
					} else {
						end = -textInputRange.moveEnd("character", -len);
						end += normalizedValue.slice(0, end).split("\n").length - 1;
					}
				}
			} else {
				if(mirai_bbced_selection_start[editorname]) {
					start=mirai_bbced_selection_start[editorname];
				} else {
					start=0;
				}
				if(mirai_bbced_selection_end[editorname]) {
					end=mirai_bbced_selection_end[editorname];
				} else {
					end=0;
				}
			}
		} else {
			return false;
		}
	}
	return [start,end];
}

function mirai_copy_selection(editorname) {
	var sourcetextarea=document.getElementById(editorname);
	var sel=false;
	if(sourcetextarea) {
		sel = mirai_get_input_selection(sourcetextarea,editorname);
		if(sel) {
			mirai_bbced_selection_start[editorname]=sel[0];
			mirai_bbced_selection_end[editorname]=sel[1];
		}
	}
}

function mirai_extra_pool() {
	return ['','sizes','colour','emote','font'];
}

function mirai_hide_extra(editorname) {
	var extraid=(mirai_bbced_current_extra[editorname]?mirai_bbced_current_extra[editorname]:0);
	var extrapool=mirai_extra_pool();
	if(extraid>0) {
		if(extraid<extrapool.length) {
			document.getElementById('bbced'+extrapool[extraid]+'sect_'+editorname).style.height='1px';
		}
	}
}

function mirai_show_extra(extraid,editorname) {
	mirai_hide_extra(editorname);
	var cextraid=(mirai_bbced_current_extra[editorname]?mirai_bbced_current_extra[editorname]:0);
	var extrapool=mirai_extra_pool();
	if(extraid!=cextraid) {
		if(extraid>0) {
			if(extraid<extrapool.length) {
				document.getElementById('bbced'+extrapool[extraid]+'sect_'+editorname).style.height='auto';
				mirai_bbced_current_extra[editorname]=extraid;
			}
		}
	} else {
		mirai_bbced_current_extra[editorname]=0;
	}
}

function mirai_insert_bbcode_cke(tagname,editorname) { mirai_insert_wrapper('['+tagname+']','[/'+tagname+']',editorname,false); }
function mirai_insert_bbcode(tagname,editorname) { mirai_insert_wrapper('['+tagname+']','[/'+tagname+']',editorname,true); }
function mirai_insert_emote(emote,editorname) { mirai_insert_wrapper('',' '+emote,editorname,true); }
function mirai_insert_size(size,editorname) { mirai_insert_wrapper('[size='+size+']','[/size]',editorname,true); }
function mirai_insert_font(font,editorname) { mirai_insert_wrapper('[font='+font+']','[/font]',editorname,true); }
function mirai_insert_colour(colour,editorname) { mirai_insert_wrapper('[color='+colour+']','[/color]',editorname,true); }
function mirai_insert_indent(editorname) { mirai_insert_wrapper('[indent]','[/indent]',editorname,true); }
function mirai_insert_list(editorname,numeric) { mirai_insert_wrapper('[list'+(numeric?'=1':'')+']'+String.fromCharCode(10)+String.fromCharCode(9)+'[*]','[/*]'+String.fromCharCode(10)+'[/list]',editorname,true); }

function mirai_insert_wrapper(opentag,closedtag,editorname,plaintext) {
	var sourcetextarea=false;
	var el=false;
	var sel=false;
	var val='';
	if(!plaintext) {
		el=document.getElementById('cke_contents_'+editorname);
		if(el) { sourcetextarea=el.getElementsByTagName('textarea')[0]; }
	} else { sourcetextarea=document.getElementById(editorname); }
	if(sourcetextarea) {
		sel = mirai_get_input_selection(sourcetextarea,editorname);
		val = sourcetextarea.value;
		if(sel) {
			valslice=val.slice(sel[0],sel[1]);
			sourcetextarea.value = val.slice(0, sel[0]) + opentag + valslice + closedtag + val.slice(sel[1]);
			mirai_set_caretpos(sourcetextarea, sel[0]+opentag.length+closedtag.length+valslice.length);
		}
	}
}