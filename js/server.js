var initts = 99999999;

function servers_refresh(ts) {
	$.ajax({
		url : "server.php?json",
		dataType : "json",
		success : function (data) {
			for(i = 0; i < data.length; i++) {
				server_update(data[i]);
			}
			
			if (ts <= initts) {
				initts = ts;
			}
			else {
				ts = initts;
			}
			setTimeout("servers_refresh(" + ts + ")", ts);
		},
		error : function () {
			ts = ts + 5;
			if (ts > 60) {
				ts = 60;
			}
			setTimeout("servers_refresh(" + ts + ")", ts);
		}
	});
	
	
	
}

function server_update(data) {
	var tr = null;
	var trs = $("table tr");

	for (k = 0; k < trs.length; k++) {
		var tds = $(trs[k]).find("td");
		
		//alert($(tds[0]).text() + " | " + data.address);
		
		if ($(tds[0]).text() != data.address) {
			continue;
		}
		else {
			tr =$(trs[k]);
			break;
		}
	}
	
	tr.find("[name='rtrate']").text(data.rtratestr);
	tr.find("[name='uptime']").text(data.uptimestr);
	
	if (data.isonline == 1) {
		tr.removeClass("offline_bg").addClass("online_bg");
		tr.find("[name='online']").css("display", "");
		tr.find("[name='offline']").css("display", "none");
	}
	else {
		tr.removeClass("online_bg").addClass("offline_bg");
		tr.find("[name='online']").css("display", "none");
		tr.find("[name='offline']").css("display", "");
	}
	
	
	return true;
}
