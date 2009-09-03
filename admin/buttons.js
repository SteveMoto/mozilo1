/* 
* 
* $Revision: 74 $
* $LastChangedDate: 2009-01-08 19:43:18 +0100 (Do, 08 Jan 2009) $
* $Author: arvid $
*
*/

function insert(aTag, eTag, keepSelectedText) {
  var input = document.forms['form'].elements['pagecontent'];
  var scrolltop = input.scrollTop;
  input.focus();
  /* f�r Internet Explorer */
  if(typeof document.selection != 'undefined') {
    /* Einf�gen des Formatierungscodes */
    var range = document.selection.createRange();
    var insText = range.text;
    if (keepSelectedText == true) {
    	range.text = aTag + insText + eTag;
    } else {
    	range.text = aTag + eTag;
    }
    /* Anpassen der Cursorposition */
    range = document.selection.createRange();
    if ((insText.length == 0) || (keepSelectedText == false)) {
      range.move('character', -eTag.length);
    } else {
      range.moveStart('character', aTag.length + insText.length + eTag.length);      
    }
    range.select();
  }
  /* f�r neuere auf Gecko basierende Browser */
  else if(typeof input.selectionStart != 'undefined')
  {
    /* Einf�gen des Formatierungscodes */
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    if (keepSelectedText == true) {
	    input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
	  } else {
	  	input.value = input.value.substr(0, start) + aTag + eTag + input.value.substr(end);
	  }
    /* Anpassen der Cursorposition */
    var pos;
    if ((insText.length == 0) || (keepSelectedText == false)) {
      pos = start + aTag.length;
    } else {
      pos = start + aTag.length + insText.length + eTag.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  /* f�r die �brigen Browser */
  else
  {
    /* Abfrage der Einf�geposition */
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    while(!re.test(pos)) {
      pos = prompt("Einf�gen an Position (0.." + input.value.length + "):", "0");
    }
    if(pos > input.value.length) {
      pos = input.value.length;
    }
    /* Einf�gen des Formatierungscodes */
    var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
  }
  input.scrollTop = scrolltop;
}

function insertAndResetSelectbox(selectbox) {
	if (selectbox.selectedIndex > 0) {
		insert(selectbox.options[selectbox.selectedIndex].value, '', false);
		selectbox.selectedIndex = 0;
	}
}

function insertTagAndResetSelectbox(selectbox) {
	if (selectbox.selectedIndex > 0) {
		insert('['+selectbox.options[selectbox.selectedIndex].value+'|', ']', true);
		selectbox.selectedIndex = 0;
	}
}