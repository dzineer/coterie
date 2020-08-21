// JavaScript Document

//Changes the active character
function change_char(root) {
	char_id = document.getElementById("selected_char").value;
	$.post(root+"ajax/change_char.php",{id:char_id},function(data,status){
		alert(status);
		location.reload();
	});
}

//Confirms adding a new character
function confirm_add() {
	val = confirm("This will add a new character to the database. Are you sure?");
	return val;
}

function create_xml(txt) {
	if (window.DOMParser){ //parse XML results
		parser=new DOMParser();
		xmlDoc=parser.parseFromString(txt,"text/xml");
	} else { // Internet Explorer
		xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async=false;
		xmlDoc.loadXML(txt); 
	}
	
	return xmlDoc;
}

function tag(xmlDoc,tag) {
	node = null
	if (xmlDoc.getElementsByTagName(tag).length&&xmlDoc.getElementsByTagName(tag)[0].childNodes.length) {
		 node = xmlDoc.getElementsByTagName(tag)[0].childNodes[0].nodeValue
	}
	if (node == "NULL"){
		node = null
	}
	return node
}