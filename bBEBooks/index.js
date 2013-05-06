$(window).load(function() {
	$('#submitter').bind('click', function() {
		$("#loading").show();
		//ISBN will always take precedence
		var keyword = jQuery.trim($("#isbn").val());
		var formats = [];
		$("input:checked").each(function(item) {
			formats.push($("input:checked")[item].value);
		})
		if (keyword != "") {
			$.getJSON('search.php?keyword=' + keyword + '&format=' + formats.join(", "), function(data) {
				if (data.Completed) {
					handleSingleResult(data.Items);
				} else {
					handleMultipleResults(data.Items);
				}
			});
		} else {
			alert("Please enter an ISBN.");
		}
	});
});

function handleSingleResult(items) {
	$("#loading").hide();
	//$("#searchbox").hide();
	$("#singleresult").show();
	$("#multipleresults").hide();
	$("#title").text(items[0].Title);
	$("#imagelink").val(items[0].ImgurImage);
	$("#thumbnail").attr("src", items[0].ImgurImage);
	$("#tags").val(items[0].TagString);
	$("#bbcode").val(items[0].BBCode);
}

function search(keyword) {
	//$("#searchbox").hide();
	$("#singleresult").hide();
	$("#multipleresults").hide();
	$("#loading").show();
	$("#keyword").val(keyword);
	$("#submitter").click();
	return false;
}

function handleMultipleResults(items) {
	//$("#searchbox").hide();
	$("#singleresult").hide();
	$("#multipleresults").show();
	var rows = [];
	$.each(items, function(key, val) {
		var id = val.ISBN;
		var idstring = val.ISBN;
		if (id.length == 0) {
			id = val.ASIN;
			idstring = "ASIN: " + val.ASIN;
		}
	    rows.push('<tr id="' + key + '"><td><button id="' + id + '" onclick="return search(this.id);">View</button></td><td>' + val.Title + ' - ' +
	              val.Author + ' (' + val.PublicationDate + ' - ' + id + ')</td></tr>');
	 });
	$('<table/>', {
	    'class': 'my-new-list',
	    html: rows.join('')
	  }).appendTo('#multipleresults_inside');
	$("#loading").hide();
}

function searchAgain() {
	//$("#searchbox").show();
	$("#singleresult").hide();
	$("#multipleresults").hide();
	$("#multipleresults_inside").text("");
	$("#loading").hide();
}
function selectAll(id) {
	document.getElementById(id).focus();
	document.getElementById(id).select();
}
