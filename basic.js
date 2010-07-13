var maxqns = 22;
var maxans = 22;
var page = {locked: false};

/* FIF */
var maxflds = 197;

function importn(c) {
	if(page.locked) return;
	//var msg = (c == 'quiz') ? 'Loading Quiz Items' : (c=='survey' ? 'Loading Survey Items' : 'Loading Phone numbers');
	var msg = '';
	if(c == 'quiz') {
		document.getElementById('qimportlist').value = '';
		document.getElementById('qlabel').innerHTML = '&nbsp; Import Nos. From Existing Quiz';
		msg = 'Loading Quiz Items';
	}
	else if(c == 'survey') {
		document.getElementById('qimportlist').value = '';
		document.getElementById('qlabel').innerHTML = '&nbsp; Import Nos. From Existing Survey';
		msg = 'Loading Survey Items';
	}
	else if(c=='general') {
		document.getElementById('gimportlist').value = '';
		document.getElementById('glabel').innerHTML = '&nbsp; Import Nos. From General List';
		msg = 'Loading Phone numbers';
	}
	else {
		document.getElementById('wimportlist').value = '';
		msg = 'Loading User Groups';
	}
	wait(msg+'.. Please wait'); //return;
	var r = createHttpRequest();
	r.open('GET', 'action.php?action=import&category='+c+'&t='+ new Date().getTime(), true);
	r.onreadystatechange = function() {
		if(r.readyState == 4) {//alert(r.responseText);
		    hidewait();
			if(/^<div/i.test(r.responseText)){
				var p = document.createElement('div');
				with(p.style) {
					position = 'absolute';
					width = '500px';
					//height = '300px';
					//backgroundColor = '#E2FFC6';
					backgroundColor = '#FFFFFF';
					border = 'solid #EEB7C6 2px';
					padding = '15px';
					top = '200px';
					left = '370px';
					overflow = 'auto';
				}
				lockpage();
				p.innerHTML = r.responseText; //alert(r.responseText);
				document.getElementsByTagName("body").item(0).appendChild(p);
				return;
			}
			var rt = eval('('+r.responseText+')'); 
			if(rt.error) {
				showmsg('<span style="color: #FF0000"><br/>Error Importing phone numbers!</span>');
				return;
			}
			else if(!rt.result) {
				msg ='<br/><span style="color: #008800; font-weight: bold">No Phone Numbers found in this category</span>';//= c=='quiz' ? 'No Quiz Items found' : (c=='survey' ? 'No Survey Items found' : 'No Phone numbers found');
				showmsg(msg);
			}
			else if(rt.result){
				//alert(rt.html());
			}
		}
	}
	r.send(null);
};

function addtoqnos(q, id) { 
	var list = document.getElementById('qimportlist').value;
	var ids = list.split(",");
	if(q.checked) {
		list += id+',';
	}
	else {
		var newlist = '';
		for(var i=0; i<ids.length; i++) {
			if(ids[i]==id || ids[i].length==0)continue;
			newlist += ids[i]+',';
		}
		list = newlist;
	}
	document.getElementById('qimportlist').value = list; //alert(list);
};

function addtognos(q, id) { //alert(id);
	var list = document.getElementById('gimportlist').value; //alert(list);
	var ids = list.split(",");
	if(q.checked) {
		list += id+',';
	}
	else {
		var newlist = '';
		for(var i=0; i<ids.length; i++) {
			if(ids[i]==id || ids[i].length==0)continue;
			newlist += ids[i]+',';
		}
		list = newlist;
	}
	document.getElementById('gimportlist').value = list; 
};

function addtolnos(q, id) { //alert(id);
	var list = document.getElementById('wimportlist').value; //alert(list);
	var ids = list.split(",");
	if(q && q.checked) {
		list += id+',';
	}
	else {
		var newlist = '';
		for(var i=0; i<ids.length; i++) {
			if(ids[i]==id || ids[i].length==0)continue;
			newlist += ids[i]+',';
		}
		list = newlist;
	}
	document.getElementById('wimportlist').value = list; 
};

function previewm(qn, sall) {
	if(page.locked) return;
	wait('Generating Preview');
	var r = createHttpRequest();
	r.open('GET', 'action.php?action='+(sall==0 ? 'preview' : 'preview_m')+'&qn='+qn+'&t='+new Date().getTime());
	r.onreadystatechange = function () {
		if(r.readyState==4) {
			hidewait();
			var t = r.responseText; //alert(t);
			if(/<div/.test(t)){
				var p = document.createElement('div');
				with(p.style) {
					position = 'absolute';
					width = '500px';
					height = '300px';
					backgroundColor = '#FFFFFF';
					border = 'solid #EEB7C6 2px';
					padding = '15px';
					top = '200px';
					left = '370px';
					overflow = 'auto';
				}
				lockpage();
				p.innerHTML = t;
				document.getElementsByTagName("body").item(0).appendChild(p);			
			}
			else {
				var rt = eval('('+t+')');
				if(rt.error){
					showmsg('<span style="color: #FF0000">Error Generating Preview</span>');
				}
				else if(!rt.error) {
					showmsg('<span style="color: #FF3300">No recipients found for this message</span>');
				}
			}
		}
	}
	r.send(null);
};

function setlabel(i){
	if(i=='quiz') {
		var list = document.getElementById('qimportlist').value;
		list = list.replace(/,$/, "");
		var ids = list.split(",");
		var n = new Array();
		for(var i=0,j=0; i<ids.length; i++) {
			if(!(/^[0-9]+$/.test(ids[i]))) continue;
			n[j++] = ids[i];
		}
		var total = n.length;	
		if(total > 0)
		document.getElementById('qlabel').innerHTML = '&nbsp;<span style="color: #008800">Imported Nos. from ('+total+') Items</span>\
		<span style="cursor: pointer;color: #FF0000; font-size: 10px" onclick="removelabel(\'ql\')" title="Click to remove numbers">[delete]</span>';
	}
	else if(i=='general') {
		var list = document.getElementById('gimportlist').value;
		list = list.replace(/,$/, "");
		var ids = list.split(",");
		var n = new Array();
		for(var i=0,j=0; i<ids.length; i++) {
			if(!(/^[0-9]+$/.test(ids[i]))) continue;
			n[j++] = ids[i];
		}
		var total = n.length;	
		if(total > 0)
		document.getElementById('glabel').innerHTML = '&nbsp;<span style="color: #008800">Imported ('+total+') Number(s)</span>\
		<span style="cursor: pointer;color: #FF0000; font-size: 10px" onclick="removelabel(\'gl\')" title="Click to remove numbers">[delete]</span>';	
	}
	else if(i=='worklist') {
		var total = 0;
		if(document.getElementById('wlist').disabled==false && document.getElementById('wlist').checked) {
			if(document.getElementById('wlist').value.length > 0) {
				document.getElementById('wimportlist').value = document.getElementById('wlist').value;
				var nos = document.getElementById('wlist').value.split(','); 
				total += nos.length;
			}
		} /*
		var list = document.getElementById('wlist').value;// alert(list);
		var nos = list.split(",");
		var n = new Array();
		for(var i=0,j=0; i<ids.length; i++) {
			if(!document.getElementById(ids[i]) || !document.getElementById(ids[i]).checked) {
				continue;
			}
			addtolnos(document.getElementById(ids[i]), document.getElementById(ids[i]).value);
			n[j++] = ids[i];
		}
		var total = n.length;*/
		// groups
		//document.getElementById('wimportlist').value = document.getElementById('wimportlist').value.replace(/,$/, '');
		var list = document.getElementById('grpids').value;
		var ids = list.split(",");
		var grptotal = 0;
		for(var i=0; i<ids.length; i++) {
			if(!document.getElementById(ids[i]) || !document.getElementById(ids[i]).checked) {
				continue;
			}
			var nos = (document.getElementById(ids[i]).value).split(',');
			for(var k=0; k<nos.length; k++) {
			    //addtolnos(document.getElementById(ids[i]), document.getElementById(ids[i]).value);
				document.getElementById('wimportlist').value += ',' + nos[k];
				grptotal++;
			}
		} 	
		document.getElementById('wimportlist').value = document.getElementById('wimportlist').value.replace(/^,/, '');
		total += grptotal; //alert(document.getElementById('wimportlist').value);
		
		if(total > 0)
		document.getElementById('wlabel').innerHTML = '&nbsp;<span style="color: #008800"> Added ('+total+') Number(s)</span>\
		<span style="cursor: pointer;color: #FF0000; font-size: 10px" onclick="removelabel(\'wl\')" title="Click to remove numbers">[delete]</span>';			
	}
	clearmsg();
};

function removelabel(l, q) { 
	if(!confirm('Are you sure?'))return false;
	if(l=='ql') {
		document.getElementById('qlabel').innerHTML = '&nbsp;Import Nos. From Existing '+(q ? 'Quiz' : 'Survey');
		document.getElementById('qimportlist').value = '';
	} 
	else if(l=='gl'){
		document.getElementById('glabel').innerHTML = '&nbsp;Import Nos. From General List';
		document.getElementById('gimportlist').value = '';
	}
	else if(l=='wl'){
		document.getElementById('wlabel').innerHTML = '';
		document.getElementById('wimportlist').value = '';
	}	
}
function showmsg(msg){
	var p = document.createElement('div');
	with(p.style) {
		position = 'absolute';
		width = '300px';
		height = '50px';
		backgroundColor = '#FFFFFF';
		border = 'solid #EEB7C6 2px';
		padding = '20px';
		top = '400px';
		left = '450px';
	}		
	p.innerHTML = '<div align="center">'+msg+'</div>\
		<div align="center" style="padding-top: 10px">\
		  <input type="button" class="button" value="OK" style="width: 50px" style="cursor: pointer" \
		  title="Accept to Close" onclick="clearmsg()"/>\
		</div>';
	document.getElementsByTagName("body").item(0).appendChild(p);
    lockpage();
};

function clearmsg(){
		document.getElementsByTagName("body").item(0).removeChild(document.getElementsByTagName("body").item(0).lastChild);
		unlockpage();
};

function wait(msg) {
	var p = document.createElement('div');
	with(p.style) {
		position = 'absolute';
		width = '300px';
		height = '50px';
		backgroundColor = '#FFFFFF';
		border = 'solid #EEB7C6 2px';
		padding = '20px';
		top = '400px';
		left = '450px';
	}
	var html = '<table border="0" align="center">\
	<tr><td colspan="2" height="10"></tr>\
	<tr>\
	  <td><img src="images/loading.gif" /></td>\
	  <td style="font-family: verdana; font-size: 11px">'+msg+'..</td>\
	</tr>\
   </table>';
   p.innerHTML = html;
   document.getElementsByTagName("body").item(0).appendChild(p);
   lockpage();
};

function hidewait() {
	document.getElementsByTagName("body").item(0).removeChild(document.getElementsByTagName("body").item(0).lastChild);
	unlockpage();
};

function lockpage() {
	page.locked = true;
};

function unlockpage() {
	page.locked = false;
};

function selectall(m) {
	var list = document.getElementById('list').value;
	if(list.length==0) return;
	list = list.split(',');
	for(var i=0; i<list.length; i++) {
		if(document.getElementById(list[i]))
		document.getElementById(list[i]).checked=m;
	}
};

function moreq(q) { 
	for(var i=1; i<=maxqns; i++) {
		if(moreqns['qn'+i].showing) continue;
		moreqns['qn'+i].showing = true; 
		
		var size = q ? '55' : '70'; 
		var hrs = '';
		if(q) {	
		   for(var j=0; j<24; j++) {
			   hrs += '<option value="'+j+'">'+(j<10 ? '0' : '')+j+'</option>';
		   }
		}
		//alert(document.getElementById('row'+(i+3)).style.display);// = 'inline';
		//document.getElementById('row'+(i+3)).style.visibility = 'visible';
		//alert(document.getElementById('row'+(i+3)).style.display);
		var nl = document.forms[0].sendall;
		var st = nl[1].checked ? 'disabled="disabled"' : ''; 
		
		document.getElementById('qn'+i+'ht').style.height = '28px';
		document.getElementById('qn'+i+'label').innerHTML = 'Qn '+(i+3)+':&nbsp;&nbsp;';
		document.getElementById('qn'+i+'fields').innerHTML = 
		'<input name="question'+(i+3)+'" id="question'+(i+3)+'" type="text" class="input" size="'+size+'" />'+(!q ? '' : '\
		&nbsp;<img src="images/datepicker.gif" style="cursor: pointer" title="Select Date" \
		onclick="displayDatePicker(\'date'+(i+3)+'\')" />&nbsp;\
		<input name="date'+(i+3)+'" id="date'+(i+3)+'" type="text" class="input" style="cursor: pointer" title="Select Date" \
		onclick="displayDatePicker(\'date'+(i+3)+'\')" value="" size="15" readonly="true" '+st+'/>\
        <select name="hr'+(i+3)+'" class="input" id="hr'+(i+3)+'" '+st+'>'+hrs+'\
        </select>\
        <input name="min'+(i+3)+'" type="text" class="input" id="min'+(i+3)+'" value="00" size="4" '+st+'/> HRS');                               
		break;
	}
	if(moreqns['qn'+maxqns].showing) {
		document.getElementById('mqbtn').disabled=true;
	}
	if(document.getElementById('lqbtn').disabled) {
		document.getElementById('lqbtn').disabled=false;
	}
};
function lessq(s) { 
    var rm = false;
	for(var i=maxqns; i>=1; i--) {
		if(!moreqns['qn'+i].showing) continue;
		moreqns['qn'+i].showing = false; 
		document.getElementById('qn'+i+'ht').style.height = '0px';
		document.getElementById('qn'+i+'label').innerHTML = '';
		document.getElementById('qn'+i+'fields').innerHTML = '';                          
		break;
	}
	if(!moreqns['qn1'].showing) {
		document.getElementById('lqbtn').disabled=true;
	}
	if(!moreqns['qn'+maxqns].showing) {
		document.getElementById('mqbtn').disabled=false;
	}
};

function morea() { 
	for(var i=1; i<=maxans; i++) {
		if(moreans['ans'+i].showing) continue;
		moreans['ans'+i].showing = true; 
		document.getElementById('ans'+i+'ht').style.height = '28px';
		document.getElementById('ans'+i+'label').innerHTML = (i+3)+')&nbsp;&nbsp;';
		document.getElementById('ans'+i+'field').innerHTML = '<input type="text" name="ans'+(i+3)+'" class="input" value="" size="40" />';
/*		document.getElementById('ans'+i+'radio_incorr').innerHTML = '<input type="radio" checked="checked" name="ans'+(i+3)+'correct" value="0" />';
		document.getElementById('ans'+i+'radio_incorr_label').innerHTML = '&nbsp;Incorrect';
		document.getElementById('ans'+i+'radio_corr').innerHTML = '<input type="radio" name="ans'+(i+3)+'correct" value="1" />';
		document.getElementById('ans'+i+'radio_corr_label').innerHTML = '&nbsp;Correct'; */                           
		break;
	}
	if(moreans['ans'+maxans].showing) {
		document.getElementById('mabtn').disabled=true;
	}
	if(document.getElementById('labtn').disabled) {
		document.getElementById('labtn').disabled=false;
	}
};

function lessa() { 
	for(var i=maxans; i>=1; i--) {
		if(!moreans['ans'+i].showing) continue;
		moreans['ans'+i].showing = false; 
		document.getElementById('ans'+i+'ht').style.height = '0px';
		document.getElementById('ans'+i+'label').innerHTML = '';
		document.getElementById('ans'+i+'field').innerHTML = '';
/*		document.getElementById('ans'+i+'radio_incorr').innerHTML = '';
		document.getElementById('ans'+i+'radio_incorr_label').innerHTML = '';
		document.getElementById('ans'+i+'radio_corr').innerHTML = '';
		document.getElementById('ans'+i+'radio_corr_label').innerHTML = '';    */                        
		break;
	}
	if(!moreans['ans1'].showing) {
		document.getElementById('labtn').disabled=true;
	}
	if(document.getElementById('mabtn').disabled) {
		document.getElementById('mabtn').disabled=false;
	}
};

function morea_e(m, c) { 
	for(var i=1; i<=m; i++) {
		if(moreans['ans'+i].showing) continue;
		moreans['ans'+i].showing = true; 
		document.getElementById('ans'+i+'ht').style.height = '28px';
		document.getElementById('ans'+i+'label').innerHTML = (c+i)+')&nbsp;&nbsp;';
		document.getElementById('ans'+i+'field').innerHTML = '<input type="text" name="ans'+(c+i)+'" class="input" value="" size="40" />';
/*		document.getElementById('ans'+i+'radio_incorr').innerHTML = 
		'<input type="radio" checked="checked" name="ans'+(c+i)+'correct" value="0" />';
		document.getElementById('ans'+i+'radio_incorr_label').innerHTML = '&nbsp;Incorrect';
		document.getElementById('ans'+i+'radio_corr').innerHTML = '<input type="radio" name="ans'+(c+i)+'correct" value="1" />';
		document.getElementById('ans'+i+'radio_corr_label').innerHTML = '&nbsp;Correct';    */                        
		break;
	}
	if(moreans['ans'+m].showing) {
		document.getElementById('mabtn').disabled=true;
	}
	if(document.getElementById('labtn').disabled) {
		document.getElementById('labtn').disabled=false;
	}
};

function lessa_e(m) { 
	for(var i=m; i>=1; i--) {
		if(!moreans['ans'+i].showing) continue;
		moreans['ans'+i].showing = false; 
		document.getElementById('ans'+i+'ht').style.height = '0px';
		document.getElementById('ans'+i+'label').innerHTML = '';
		document.getElementById('ans'+i+'field').innerHTML = '';
/*		document.getElementById('ans'+i+'radio_incorr').innerHTML = '';
		document.getElementById('ans'+i+'radio_incorr_label').innerHTML = '';
		document.getElementById('ans'+i+'radio_corr').innerHTML = '';
		document.getElementById('ans'+i+'radio_corr_label').innerHTML = '';   */                         
		break;
	}
	if(!moreans['ans1'].showing) {
		document.getElementById('labtn').disabled=true;
	}
	if(document.getElementById('mabtn').disabled) {
		document.getElementById('mabtn').disabled=false;
	}
};

function moref() { 
	for(var i=1; i<=maxflds; i++) {
		if(moreflds['field'+i].showing) {
			continue;
		};
		moreflds['field'+i].showing = true; 
		document.getElementById('row'+i).style.display = /msie/i.test(navigator.userAgent) ? 'inline' : 'table-row';
		document.getElementById('fld'+i+'ht').style.height = '28px';
		document.getElementById('fld'+i+'label').innerHTML = (i+3)+'.';
		document.getElementById('fld'+i+'field').innerHTML = '<input name="field'+(i+3)+'" type="text" class="input" size="35" />'; 
		document.getElementById('fld'+i+'type').innerHTML = 
		'<select name="type'+(i+3)+'" id="type'+(i+3)+'" class="input" style="width: 100px" onchange="color('+(i+3)+')">\
		<option value="Data">DATA</option><option value="Image">IMAGE</option>\
		<option value="Checkbox">CHECKBOX</option><option value="Radio">RADIO</option>\
		<option value="Menu">MENU</option><option value="Numeric">NUMERIC</option></select>\
		<input type="hidden" name="field'+(i+3)+'options" id="field'+(i+3)+'options" />';
		//document.getElementById('fld'+i+'code').innerHTML = '<input name="code'+(i+3)+'" type="text" class="input" size="35" />'; 
		document.getElementById('optl'+(i+3)).innerHTML = 'options';
		break;
	}
	if(moreflds['field'+maxflds].showing) {
		document.getElementById('mfbtn').disabled=true;
	}
	if(document.getElementById('lfbtn').disabled) {
		document.getElementById('lfbtn').disabled=false;
	}
};

function lessf() { 
	for(var i=maxflds; i>=1; i--) { 
		if(!moreflds['field'+i].showing) continue;
		moreflds['field'+i].showing = false; 
		document.getElementById('row'+i).style.display = 'none';
		document.getElementById('fld'+i+'ht').style.height = '0px';
		document.getElementById('fld'+i+'label').innerHTML = '';
		document.getElementById('fld'+i+'field').innerHTML = ''; 
		document.getElementById('fld'+i+'type').innerHTML = ''; 
		document.getElementById('optl'+(i+3)).innerHTML = ''; 
		//document.getElementById('fld'+i+'code').innerHTML = '';                          
		break;
	}
	if(!moreflds['field1'].showing) {
		document.getElementById('lfbtn').disabled=true;
	}
	if(!moreflds['field'+maxflds].showing) {
		document.getElementById('mfbtn').disabled=false;
	}
};

//############!!!!!!!!!!!!!!!!
function moref_e(m, c) { 
	for(var i=1; i<=m; i++) {
		if(moreflds['field'+i].showing) continue;
		moreflds['field'+i].showing = true;
		document.getElementById('row'+i).style.display = /msie/i.test(navigator.userAgent) ? 'inline' : 'table-row';
		document.getElementById('fld'+i+'ht').style.height = '28px';          
		document.getElementById('fld'+i+'label').innerHTML = (i+c)+'.';
		document.getElementById('fld'+i+'field').innerHTML = '<input name="field'+(i+c)+'" type="text" class="input" size="35" />'; 
		document.getElementById('fld'+i+'type').innerHTML = '\
		<select name="type'+(i+c)+'" id="type'+(i+c)+'" class="input" style="width: 100px" onchange="color('+(i+c)+')">\
		<option value="Data">DATA</option><option value="Image">IMAGE</option>\
		<option value="Checkbox">CHECKBOX</option><option value="Radio">RADIO</option>\
		<option value="Menu">MENU</option><option value="Numeric">NUMERIC</option></select>\
		<input type="hidden" name="field'+(i+c)+'options" id="field'+(i+c)+'options" />';	
		//alert(document.getElementById('optl'+(i+c)).innerHTML);
		document.getElementById('optl'+(i+c)).innerHTML = 'options';
		//alert(document.getElementById('optl'+(i+c)).innerHTML);
		//re_order(i+c);
		break;
	}
	if(moreflds['field'+m].showing) {
		document.getElementById('mfbtn').disabled=true;
	}
	if(document.getElementById('lfbtn').disabled) {
		document.getElementById('lfbtn').disabled=false;
	}
};

function re_order(c){
//nlabel
	for(var i = 1; i<=c; i++){
		var select = '<select name="position"'+i+' id="position"'+i+'>';
		for(var j = 1; j<=c; j++){
			select += '<option value = "'+j+'" '+(j==i ? 'selected="selected"' : '')+'>'+j+'</option>';
		}
		select +='</select>';
		document.getElementById('pos'+i).innerHTML = select;
	}
};

function lessf_e(m, c) { 
	for(var i=m; i>=1; i--) { 
		if(moreflds['field'+i].showing) {
		moreflds['field'+i].showing = false; 
		//if(document.getElementById('fld'+i+'ht'))
		document.getElementById('fld'+i+'ht').style.height = '0px';
		//if(document.getElementById('fld'+i+'label'))
		document.getElementById('fld'+i+'label').innerHTML = '';
		//if(document.getElementById('fld'+i+'field'))
		document.getElementById('fld'+i+'field').innerHTML = ''; 
		//if(document.getElementById('fld'+i+'type'))
		
		document.getElementById('pos'+(i+c)).innerHTML = "";
		
		document.getElementById('fld'+i+'type').innerHTML = ''; //alert(m+' '+i);
		document.getElementById('optl'+(i+c)).innerHTML = '';
		//document.getElementById('optl'+i).innerHTML = ''; 
		//re_order(i+c-1);
		break;
		}
	}
	if(!moreflds['field1'].showing) {
		document.getElementById('lfbtn').disabled=true;
	}
	if(document.getElementById('mfbtn').disabled) {
		document.getElementById('mfbtn').disabled=false;
	}
};



function opts(i) { 
	/* hide others */
	for(var j=1; j<=25; j++) {
		if(j==i || !document.getElementById('field'+j+'opts')) continue;
		hopts(j);
	}; 
	if(!document.getElementById('type'+i)) return;
	var t = document.getElementById('type'+i);
	var v = t.options[t.selectedIndex].value;
	if(v != 'Checkbox' && v != 'Radio' && v != 'Menu') {
		return;
	};
	with(document.getElementById('field'+i+'opts').style) {
		position = 'absolute';
		width = '200px';
		height = '150px';
		/*left = '650px';*/
		left = '550px';
		border = 'solid #FF6600 1px';
		backgroundColor = '#FFFFFF';
		padding = '20px';
		margin = '0px';
	};
	/* hide select boxes, !!IE */
	hide_sb();
	var opts = document.getElementById('field'+i+'options').value;
	document.getElementById('field'+i+'opts').innerHTML = '\
	<div style="font-size: 10px"><u>Field '+i+' Options (One per line)</u></div><br/>\
	<div><textarea style="width: 200px; height: 100px" class="input" id="ta'+i+'">'+opts+'</textarea></div><br/>\
	<div><input type="button" value="Add Options" class="button" onclick="addopts('+i+')" />\
	<input type="button" value="Cancel" class="button" onclick="hopts('+i+')"/></div>';
	document.getElementById('submit').disabled = true;
};

function addopts(i) {
	var opts = document.getElementById('ta'+i).value;
	document.getElementById('field'+i+'options').value = opts;
	hopts(i);
	document.getElementById('submit').disabled = false;
	show_sb();
};

function hopts(i) {
	with(document.getElementById('field'+i+'opts').style) {
		position = 'absolute';
		width = '0px';
		height = '0px';
		border = 'none';
		backgroundColor = '#FFFFFF';
		padding = '0px';
		margin = '0px';
	};
	document.getElementById('field'+i+'opts').innerHTML ='';
	show_sb();
	document.getElementById('submit').disabled = false;
};

function hide_sb() {
	for(var i=1; i<=25; i++) {
		if(!document.getElementById('type'+i)) continue;
		document.getElementById('type'+i).disabled = true;
		//document.getElementById('field'+i).disabled = true;
		if(/msie/i.test(navigator.userAgent))
		document.getElementById('type'+i).style.visibility = 'hidden';
	};
};

function show_sb() {
	for(var i=1; i<=25; i++) {
		if(!document.getElementById('type'+i)) continue;
		document.getElementById('type'+i).disabled = false;
		//document.getElementById('field'+i).disabled = false;
		document.getElementById('type'+i).style.visibility = 'visible';
	};
};

function color(i) {
	var t = document.getElementById('type'+i);
	var v = t.options[t.selectedIndex].value;
	if(v != 'Checkbox' && v != 'Radio' && v != 'Menu') {
		document.getElementById('optl'+i).style.color = '#666666';
		return;
	};
	document.getElementById('optl'+i).style.color = '#008800';
};

function colorall() {
	for(var i=1; i<=25; i++) {
		if(!document.getElementById('type'+i)) continue; 
	    var t = document.getElementById('type'+i); 
	    var v = t.options[t.selectedIndex].value; 
	    if(v != 'Checkbox' && v != 'Radio' && v != 'Menu') {
		   document.getElementById('optl'+i).style.color = '#666666';
	    } 
		else {
			document.getElementById('optl'+i).style.color = '#008800';
		};
	}
};

function checklimit_kword(f, n) {
	var i = f.value.length;
	//if(i>n) {
	//	f.value = f.value.substr(0, n);
	//}
	var attrLen = document.getElementById('attribution').value.length;
	var cotLen = document.getElementById('content'). value.length;
		document.getElementById('chars').value= attrLen + cotLen;
};

function checklimit(f, n) {
	var i = f.value.length;
	//if(i>n) {
	//	f.value = f.value.substr(0, n);
	//}
	document.getElementById('chars').value= i;
};

function s(id, text) {
	with(document.getElementById(id).style) {
		position='absolute';
		border='solid #FF3300 1px';
		backgroundColor='#fff';
		width='450px';
		height='100px';
		padding='20px';
	};
	document.getElementById(id).innerHTML=text;
};

function h(id) {
	with(document.getElementById(id).style) {
		position='relative';
		border='none';
		width='0px';
		height='0px';
		padding='0px';
		document.getElementById(id).innerHTML='';
	};
};

/* ######################################## */
function sot(c) {
	if(c) {
	   //document.getElementById('tht').style.height = '26px';
	   document.getElementById('tlabel').innerHTML = '<br/>Target Keyword(s):&nbsp;&nbsp;'; 
	   var html = 
	   '<div id="option1" style="height: 25px">Option 1.&nbsp;&nbsp;<input type="text" \
	   name="keyword1" id="keyword1" size="35" class="input"></div>\
	   <div id="option2" style="height: 25px">Option 2.&nbsp;&nbsp;<input type="text" \
	   name="keyword2" id="keyword2" size="35" class="input"></div>';
	   for(var i=3; i<=9; i++) {
		   html += '<div id="option'+i+'" style="display: none"></div>';
	   }
	   document.getElementById('options').innerHTML = html;
	   snote();
	   document.getElementById('keyword1').focus();
	   document.getElementById('mbtn').disabled = false;
	   document.getElementById('lbtn').disabled = false;
	}
	else {
		document.getElementById('tlabel').innerHTML = '';
		document.getElementById('tnote').innerHTML = '';
		document.getElementById('options').innerHTML = '';
	    document.getElementById('mbtn').disabled = true;
	    document.getElementById('lbtn').disabled = true;		
	}
};

function mopt() {
	for(var i=3; i<=9; i++) { 
		if(document.getElementById('option'+i).innerHTML.length == 0) {
			with(document.getElementById('option'+i).style) {
				height = '25px'; 
				display = 'block';
			}; 
			document.getElementById('option'+i).innerHTML = 
			'Option '+i+'.&nbsp;&nbsp;<input type="text" name="keyword'+i+'" id="keyword'+i+'" size="35" class="input">';
			break;
		}
	};
};

function lopt() {
	for(var i=9; i>=3; i--) {
		if(document.getElementById('option'+i).innerHTML.length > 0 ) {
			document.getElementById('option'+i).style.height = '0px';
			with(document.getElementById('option'+i).style) {
				height = '0px'; 
				display = 'none';
			};
			document.getElementById('option'+i).innerHTML = '';
			break;
		}
	};
};

function sethtml() {
	var html = '';
	for(var i=1, j=0; i<=9; i++) {
		if(!document.getElementById('keyword'+i)) {
			continue;
		}
		j++;
		html += '<div id="option'+i+'" style="height: 25px;">Option '+i+'.&nbsp;&nbsp;<input name="keyword'+i+'" id="keyword'+i+'" size="35" class="input" type="text" value="'+document.getElementById('keyword'+i).value+'"></div>';
	};
	document.getElementById('thtml').value=(html);
	document.getElementById('opcount').value=j;
};

function snote() {
	document.getElementById('tlabel').innerHTML = '<br/>Target Keyword(s):&nbsp;&nbsp;'; 
	document.getElementById('tnote').innerHTML = 
	   '<br/><div style="width: 300px; border: solid #cccccc 1px; background-color: #E4E4E4; padding: 10px;">\
	   <span style="color: #CC0000"><u>Please Note</u>:</span> The keyword content must specify the options <strong>1</strong>, <strong>2</strong>, <strong>3</strong>.. up to the number of outbound trigger options.\
	   </div>';	
}
//
function tsend(m) {
	if(m) {
		document.getElementById('qs').style.display = /msie/i.test(navigator.userAgent) ? 'inline' : 'table-row';
		/* disable all time fields */
		for(var i=1; i<=25; i++) {
			if(document.getElementById('date'+i)) {
				document.getElementById('date'+i).disabled = true;
			}
			if(document.getElementById('hr'+i)) {
				document.getElementById('hr'+i).disabled = true;
			}
			if(document.getElementById('min'+i)) {
				document.getElementById('min'+i).disabled = true;
			}	
		}
	}
	else {
		document.getElementById('qs').style.display = 'none';
		/* enable all time fields */
		for(var i=1; i<=25; i++) {
			if(document.getElementById('date'+i)) {
				document.getElementById('date'+i).disabled = false;
			}
			if(document.getElementById('hr'+i)) {
				document.getElementById('hr'+i).disabled = false;
			}
			if(document.getElementById('min'+i)) {
				document.getElementById('min'+i).disabled = false;
			}	
		}
	}
};

function single(m) {
	if(m) {
		document.getElementById('kr').style.display = /msie/i.test(navigator.userAgent) ? 'inline' : 'table-row';
	}
	else {
		document.getElementById('kr').style.display = 'none';
	}
};

function slogic() { 
	with(document.getElementById('logicd').style) {
		display = 'inline';
		position = 'absolute';
	}
	document.getElementById('vb').disabled=false;
	//document.getElementById('cb').disabled=false;	
};

function hlogic() {
	with(document.getElementById('logicd').style) {
		display = 'none';
		position = 'relative';
	}
};

function sedit(l) {
	if(!document.getElementById(l)) {
		return;
	}
	with(document.getElementById(l).style) {
		display = 'inline';
		position = 'absolute';
	}	
};

function hedit(l) {
	if(!document.getElementById(l)) {
		return;
	}
	with(document.getElementById(l).style) {
		display = 'none';
		position = 'relative';
	}	
};


//function setla() {
//	var qlist = document.getElementById('logicl');
//	var alist = document.getElementById('logica');
//	/* remove all from answer list */
//	for(var i=alist.options.length-1; i>0; i--) {
//		alist.remove(i);
//	}	
//	if(qlist.selectedIndex==0) {
//		return;
//	}
//	var n = qlist[qlist.selectedIndex].value;
//	var aopts = msfields['q'+n];
//	for(var i=0; i<aopts.length; i++) {
//		try {
//			alist.add(new Option(aopts[i].label, aopts[i].value), null);
//		}
//		catch (e) {
//			alist.add(new Option(aopts[i].label, aopts[i].value));
//		}
//	}
//};

//function setqa() {
//	var qlist = document.getElementById('logicl');
//	var alist = document.getElementById('logica');
//	var n = qlist[qlist.selectedIndex].value;
//	if(alist.selectedIndex == 0) {
//		document.getElementById('q'+n+'ans').innerHTML = '-';
//		document.getElementById('field'+n).value = '';
//		return;
//	};	
//	var label = alist.options[alist.selectedIndex].text;
//	document.getElementById('q'+n+'ans').innerHTML = label;
//	document.getElementById('field'+n).value = label;
//};
//
function clogic() { 
	with(document.getElementById('_clogic').style) {
		display = 'inline';
		position = 'absolute';
	}
	document.getElementById('vb').disabled=true;
	//document.getElementById('cb').disabled=true;
};

function hclogic() {
	with(document.getElementById('_clogic').style) {
		display = 'none';
		position = 'relative';
	}
	document.getElementById('vb').disabled=false;
	//document.getElementById('cb').disabled=false;
};

function en_ansf(f) {
	if(!document.getElementById(f)) {
		return;
	}
	if(document.getElementById(f).disabled) {
		document.getElementById(f).disabled = false;
		return;
	}
	document.getElementById(f).selectedIndex = 0;
	document.getElementById(f).disabled = true;
};

function dr(i, s) { 
	if(page.locked) return;
	window.scrollBy(0, -1 * document.body.scrollHeight);
	wait('Getting results\'s full details..');
	var r = createHttpRequest();
	var u = 'action.php?action=mresult&i='+i+'&start='+s+'&t='+new Date().getTime(); //alert(u); return;
	r.open('GET', u);
	r.onreadystatechange = function () {
		if(r.readyState==4) {
			hidewait();
			var t = r.responseText; //alert(t);
			if(/<div/.test(t)){
				var p = document.createElement('div');
				with(p.style) {
					position = 'absolute';
					width = '650px';
					//height = '450px'; 
					backgroundColor = '#FFFFFF';
					border = 'solid #EEB7C6 2px';
					padding = '15px';
					top = '200px';
					left = '300px';
					overflow = 'auto';
				}
				lockpage();
				p.innerHTML = t;
				document.getElementsByTagName("body").item(0).appendChild(p);			
			}
			else {
				var rt = eval('('+t+')'); 
				if(rt.error){ 
					showmsg('<span style="color: #FF0000">Error Getting Survey Result</span>');
				}
			}
			//window.scrollBy(0, -1 * document.body.scrollHeight);
		}
	}
	r.send(null);
};

function _editinfo(i) { 
	var w = 780, h = 500;
    var args = 'width='+w+',height='+h+',scrollbars=no,resizable=no,copyhistory=no,modal=yes';
	var win = window.open('editinfo.php?userId='+i, 'useri', args);
	win.moveTo((window.screen.width - w) /2, (window.screen.height - h) /2);
};

function editinfo(i, p, m) { 
	if(page.locked) return;
	wait('Getting User Information..');
	var r = createHttpRequest();
	var u = 'action.php?action=_ui&_ui='+i+'&u='+encodeURIComponent(p)+'&misdn='+m+'&t='+new Date().getTime(); //alert(u); return;
	r.open('GET', u);
	r.onreadystatechange = function () {
		if(r.readyState==4) {
			hidewait();
			var t = r.responseText; //alert(t);
			if(/<div/.test(t)){
				var p = document.createElement('div');
				with(p.style) {
					position = 'absolute';
					width = '650px';
					height = '600px';
					backgroundColor = '#FFFFFF';
					border = 'solid #EEB7C6 2px';
					padding = '15px';
					top = '200px';
					left = '300px';
					overflow = 'auto';
				}
				lockpage();
				p.innerHTML = t;
				document.getElementsByTagName("body").item(0).appendChild(p);			
			}
			else {
				var rt = eval('('+t+')'); 
				if(rt.error){ 
					showmsg('<span style="color: #FF0000">Error Getting User Information</span>');
				}
			}
			window.scrollBy(0, -1 * document.body.scrollHeight);
		}
	}
	r.send(null);
};

function addwltgrp() { 
	if(page.locked) return;
	wait('Getting User Groups..');
	var r = createHttpRequest();
	var u = 'action.php?action=getgrps&t='+new Date().getTime(); 
	r.open('GET', u);
	r.onreadystatechange = function () {
		if(r.readyState==4) {
			hidewait();
			var t = r.responseText; //alert(t);
			if(/<div/.test(t)){
				var p = document.createElement('div');
				with(p.style) {
					position = 'absolute';
					width = '550px';
					height = '250px';
					backgroundColor = '#FFFFFF';
					border = 'solid #EEB7C6 2px';
					padding = '15px';
					top = '200px';
					left = '300px';
					overflow = 'auto';
				}
				lockpage();
				p.innerHTML = t;
				document.getElementsByTagName("body").item(0).appendChild(p);			
			}
			else {
				var rt = eval('('+t+')'); 
				if(rt.error){ 
					showmsg('<span style="color: #FF0000">Error Getting User Groups</span>');
				}
			}
			window.scrollBy(0, -1 * document.body.scrollHeight);
		}
	}
	r.send(null);
};


function insertf() {
	var d = document.createElement('div');
	with(d.style) {
		width = '550px';
		/*height = '330px'; */
		border = 'solid #FF3300 1px';
		backgroundColor = '#fff';
		position = 'absolute';
		padding='10px';
	};
	d.setAttribute('id', 'nf');
	var opts = '<option></option>';
	for(var i=0; i<fields.length; i++) {
		opts += '<option value="'+fields[i].code+'">'+fields[i].name+'</option>';
	}
	var html = '<form method=post><table border=0 cellpadding=0 width=100%>\
	<tr><td height=40></td><td id=errors style=color:#ff0000></td></tr>\
	<tr>\
	    <td height=30 align=right>Field Position:&nbsp;&nbsp;</td>\
		<td>\
		     <select name=position id=position style=width:400px onchange=enlist()>\
			 <option value=1>BEGINING</option>\
			 <option value=2>END</option>\
			 <option value=3>INSERT AFTER FIELD</option>\
			 </select>\
	    </td>\
	</tr>\
	<tr>\
	    <td height=30 align=right>AFTER FIELD:&nbsp;&nbsp;</td>\
		<td><select style=width:400px name=afterfield id=afterfield disabled=disabled>'+opts+'</select></td>\
	</tr>\
	<tr>\
	    <td height=30 align=right>Field:&nbsp;&nbsp;</td>\
		<td><input type=text name=name id=name size=62 class=input/></td>\
	</tr>\
    <tr>\
	    <td height=30 align=right>Type:&nbsp;&nbsp;</td>\
		<td><select style=width:400px name=type id=type onchange=tfoptions()>\
		<option value=Data>DATA</option>\
		<option value=Image>IMAGE</option>\
		<option value=Checkbox>CHECKBOX</option>\
		<option value=Radio>RADIO</option>\
		<option value=Menu>MENU</option>\
		<option value=Numeric>NUMERIC</option>\
		</select></td>\
	</tr>\
	<tr id=tr style=display:none>\
	    <td height=30 align=right>Feild Options:&nbsp;&nbsp;</td>\
		<td><textarea name=options id=options style="width: 395px;height:80px" class=input></textarea>\
		<br/><span style=font-size:10px>(One per Line)</span></td>\
	</tr>\
	<tr>\
	    <td height=50></td>\
		<td><input name=_sinsert type=submit value="Add Field" class=button />\
		<input type=button value="Cancel" class=button onclick="rmnf();" /></td>\
	</tr>\
   </table></form>';
   d.innerHTML = html;
   document.getElementById('_ifield').appendChild(d);
   _insert_r();
};

function _insert_r() {
	//alert(_form.position);
	//alert(_form.type);
	//alert(_form.name);
	var l = document.getElementById('position'); 
	for(var i=0; i<l.options.length; i++) {
		if(l.options[i].value == _form.position) {
			//alert('setting index for: ' + l.selectedIndex+' to '+_form.position);
			//l.selectedIndex = parseInt(_form.position);
			l.options[i].selected = true;
			break;
		}
	}
	if(_form.afterfield.length > 0) {
		l = document.getElementById('afterfield');
		l.disabled = false;
		for(var i=0; i<l.options.length; i++) {
			if(l.options[i].value == _form.afterfield) {
				l.options[i].selected = true;
				//l.selectedIndex = parseInt(_form.afterfield);
				break;
			}
		}
	}
	l = document.getElementById('type');
	for(var i=0; i<l.options.length; i++) {
		if(l.options[i].value == _form.type) {
			//l.selectedIndex = parseInt(_form.type);
			l.options[i].selected = true;
			break;
		}
	}	
	document.getElementById('name').value = _form.name;
	tfoptions();
	if(_form.options.length > 0) {
		document.getElementById('options').value = _form.options.replace(/\|/g, "\n");
	}
	document.getElementById('errors').innerHTML = _form.errors;
};

function tfoptions() {
	var l = document.getElementById('type');
	var t = l.options[l.selectedIndex].value;
	if(t=='Data'||t=='Image'||t=='Numeric') {
		document.getElementById('tr').style.display='none';
	}
	else{
		document.getElementById('tr').style.display=/msie/i.test(navigator.userAgent) ? 'inline' : 'table-row';
	}
};

function enlist() {
	var p = document.getElementById('position');
	if(p.selectedIndex != 2) {
		document.getElementById('afterfield').selectedIndex=0;
		document.getElementById('afterfield').disabled=true;
	}
	else {
		document.getElementById('afterfield').disabled=false;
	}
};

function rmnf() {
	document.getElementById('_ifield').removeChild(document.getElementById('nf'));
};

function filter() {
	var d = document.createElement('div');
	with(d.style) {
		width = '500px';
		top = '250px';
		left = '340px';
		/*height = '330px'; */
		border = 'solid #FF3300 1px';
		backgroundColor = '#fff';
		position = 'absolute';
		padding='10px';
	};
	d.setAttribute('id', 'ff');
	var html = '<form method=post><table border=0 cellpadding=0 width=100%>\
	<tr><td height=40></td><td id=errors style=color:#ff0000></td></tr>\
	<tr>\
	    <td height=30 align=right>Date Range:&nbsp;&nbsp;</td>\
		<td>\
		   <table border=0>\
		     <tr>\
			 <td><img src=images/datepicker.gif style=cursor:pointer onclick="displayDatePicker(\'from\');">\
			 </td><td><input name=from id=from type=text class=input size=16 readonly=readonly style=cursor:pointer \
			 onclick="displayDatePicker(\'from\');"/></td>\
			 <td><img src=images/datepicker.gif style=cursor:pointer onclick="displayDatePicker(\'to\');"></td>\
			 <td><input type=text name=to id=to class=input size=16 readonly=readonly style=cursor:pointer \
			 onclick="displayDatePicker(\'to\');"/></td>\
		   </tr>\
		   </table>\
	    </td>\
	</tr>\
    <tr>\
	    <td height=30 align=right>Type of Results:&nbsp;&nbsp;</td>\
		<td><select style=width:300px name=type id=type>\
		<option></option>\
		<option value=1>All Results Excluding Test Results</option>\
		<option value=2>All Results</option>\
		<option value=3>Test Results</option>\
		</select></td>\
	</tr>\
	<tr>\
	</tr>\
	   <td height=30 align=right>Location:&nbsp;&nbsp;</td>\
	   <td><select style=width:300px name=location id=location>\
	   <option></option>\
	   <option value=Mbale>Mbale</option>\
	   <option value=Bushenyi>Bushenyi</option>\
	   <option value=location_unknown>Unkown</option>\
	   </select></td>\
	</tr>\
	<tr>\
	    <td height=30></td>\
		<td><input type=checkbox name=xduplicates id=xduplicates>&nbsp;Exclude duplicate results</td>\
	</tr>\
	<tr>\
	<tr>\
	    <td height=30></td>\
		<td><input type=checkbox name=categorize id=categorize>&nbsp;Categorize By Week</td>\
	</tr>\
	<tr>\
	    <td height=40></td>\
		<td><input name=filter type=submit value="Submit Filter" class=button />\
		<input type=button value="Cancel" class=button onclick="rmff();" /></td>\
	</tr>\
   </table></form>';
   d.innerHTML = html; 
   document.getElementsByTagName("body").item(0).appendChild(d);
   setfflds();
};

function rmff() {
	if(!document.getElementById('ff')) return;
	document.getElementsByTagName('body').item(0).removeChild(document.getElementById('ff'));
};

function setfflds() {
       document.getElementById('from').value = ffields.from; 
       document.getElementById('to').value = ffields.to;
       var tl=document.getElementById('type');
       for(var i=0; i<tl.options.length; i++) {
            if(tl.options[i].value == ffields.type) {
                tl.selectedIndex = i;
                break;
            }
       }
       var ll=document.getElementById('location');
       for(var i=0; i<ll.options.length; i++) {
            if(ll.options[i].value == ffields.location) {
                ll.selectedIndex = i;
                break;
            }
       }
			 document.getElementById('xduplicates').checked = ffields.xduplicates;
       document.getElementById('categorize').checked = ffields.categorize;     
};

function iDownload(surveyId, start, limit) {
	var d = document.createElement('div');
	with(d.style) {
		width = '300px';
		top = '250px';
		left = '340px';
		/*height = '330px'; */
		border = 'solid #FF3300 1px';
		backgroundColor = '#fff';
		position = 'absolute';
		padding='10px';
	};
	d.setAttribute('id', 'iDownload');
	var html = '\
	<p><img src="images/Imagen-PNG-32x32.png" align="ABSMIDDLE"/>&nbsp;<a href="downloadimages.php?start='+start+'&limit='+limit+'" target="_blank" style="color: #000000; text-decoration: underline" onclick="rmdd(); return true;" title="Click To Download All Images For Currently Displayed Results">Download Images for Displayed Results</a></p>\
	<p><img src="images/Imagen-PNG-32x32.png" align="ABSMIDDLE"/>&nbsp;<a href="downloadimages.php?all_pics_for_filter=1" target="_blank" style="color: #000000; text-decoration: underline" onclick="rmdd(); return true;" title="Click To Download All Images For Current Filter">Download All Images for Current Filter</a></p>\
	<p><img src="images/Imagen-PNG-32x32.png" align="ABSMIDDLE"/>&nbsp;<a href="downloadimages.php?surveyId='+surveyId+'&all=1" target="_blank" style="color: #000000; text-decoration: underline" onclick="rmdd(); return true; "title="Click To Download All Images For All Survey Results Including TEST results">Download All Image for All Survey Results</a></p>\
	<p><input type="button" value="Cancel" onclick="rmdd(); return false" /></p>';
   d.innerHTML = html; 
   document.getElementsByTagName("body").item(0).appendChild(d);
};

function rmdd() {
	if(!document.getElementById('iDownload')) return;
	document.getElementsByTagName('body').item(0).removeChild(document.getElementById('iDownload'));
};

function shB_f(){
	var d = document.createElement('div');
	with(d.style) {
		width = '250px';
		top = '160px';
		left = '480px';
		/*height = '330px'; */
		border = 'solid #FF3300 1px';
		backgroundColor = '#fff';
		position = 'absolute';
		padding='10px';
	};
	d.setAttribute('id', 'show_b');
	bvalue = document.getElementById('newBnumber').value;
	var html = '\
		<p style="background-color:#E4E4E4; \
		border:solid #CCCCCC 1px; \
		cursor:pointer; \
		text-align: justify;\
		font-size:10px;\
		font-family: Verdana, Arial, Helvetica, sans-serif;">If you wish for the <b>BNUMBER</b> to be re-written when forwarding the SMS, specify here the <b>new</b> BNUMBER. Otherwise, the received BNUMBER shall be used when forwarding the SMS</p>\
		<p><input type="text" value="'+bvalue+'" id="b_shown" size="35" class="input"/></p>\
		<p><input type="button" class="button" value="Set Bnumber" onclick="setB_no(\'ok\')">&nbsp;<input type="button" class="button" value="Cancel" onclick="setB_no(\'cancel\')"><p>';
		d.innerHTML = html;
		document.getElementsByTagName("body").item(0).appendChild(d);
};

function setB_no(value){
	var b_no = document.getElementById('b_shown').value;
	if(value=="ok"){
		if(isNaN(b_no)){
			alert("The Bnumber must consist of numbers only");
			return false;
		}
		document.getElementById('newBnumber').value = b_no;
		rmB_f();
	}else{
		rmB_f()
	}
};

function rmB_f(){
	if(!document.getElementById('show_b')) return;
	document.getElementsByTagName('body').item(0).removeChild(document.getElementById('show_b'));
}

function set_sc() {
     var dl = document.getElementById('districtId');
	 var j = dl.options[dl.selectedIndex].value;
	 for(var i=0; i<districts.length; i++) {
		 if(districts[i].id==j) {
			 var ht = '<select name="subcountyId" class=input style="width:274px"><option></option>';
			 var scl = districts[i].subcounties;
			 for(var k=0; k<scl.length; k++) {
				 ht += '<option value="'+scl[k].id+'"'+(scl[k].selected ? 'selected="selected"' : '')+'>'+scl[k].name+'</option>';
			 }
			 ht += '</select>';
			 document.getElementById('subc').innerHTML = ht;
			 return;
		 }
	 }
	 document.getElementById('subc').innerHTML = 
	 '<select name="subcountyId" class=input style="width:274px"><option></option></select>';
};

//
function createHttpRequest(){
  var request;
  try{request = new ActiveXObject("Msxml2.XMLHTTP"); }
  catch(e){
   try{request = new ActiveXObject("Microsoft.XMLHTTP");}
   catch(e){request = new XMLHttpRequest(); }
 }
 return request; 
};

Object.prototype.deep_clone = function(){
	eval("var tmp = " + this.toJSON());
	return tmp;
}
Object.prototype.toJSON = function(){
	var json = [];
	for(var i in this){
		if(!this.hasOwnProperty(i)) continue;
		//if(typeof this[i] == "function") continue;
		json.push(
			i.toJSON() + " : " +
			((this[i] != null) ? this[i].toJSON() : "null")
		)
	}
	return "{\n " + json.join(",\n ") + "\n}";
}
Array.prototype.toJSON = function(){
	for(var i=0,json=[];i<this.length;i++)
		json[i] = (this[i] != null) ? this[i].toJSON() : "null";
	return "["+json.join(", ")+"]"
}

String.prototype.toJSON = function(){
	return '"' +
		this.replace(/(\\|\")/g,"\\$1")
		.replace(/\n|\r|\t/g,function(){
			var a = arguments[0];
			return  (a == '\n') ? '\\n':
					(a == '\r') ? '\\r':
					(a == '\t') ? '\\t': ""
		}) +
		'"'
}
Boolean.prototype.toJSON = function(){return this}
Function.prototype.toJSON = function(){return this}
Number.prototype.toJSON = function(){return this}
RegExp.prototype.toJSON = function(){return this}

// strict but slow
String.prototype.toJSON = function(){
	var tmp = this.split("");
	for(var i=0;i<tmp.length;i++){
		var c = tmp[i];
		(c >= ' ') ?
			(c == '\\') ? (tmp[i] = '\\\\'):
			(c == '"')  ? (tmp[i] = '\\"' ): 0 :
		(tmp[i] = 
			(c == '\n') ? '\\n' :
			(c == '\r') ? '\\r' :
			(c == '\t') ? '\\t' :
			(c == '\b') ? '\\b' :
			(c == '\f') ? '\\f' :
			(c = c.charCodeAt(),('\\u00' + ((c>15)?1:0)+(c%16)))
		)
	}
	return '"' + tmp.join("") + '"';
};
