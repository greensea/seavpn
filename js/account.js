function debug(obj) {
	var str = "";
	for (i in obj) {
		if (obj[i].length > 100) continue;
		
		str += i + " = " + obj[i] + "\n";
	}
	
	alert(str);
}


function passinput_blur(id) {
	var passinput = $("#passinput_" + id);
	
	if (passinput.val() == "" || passinput.val() == $("#pass_" + id).text()) {
		passwd_ui_revert(id);
		return false;
	}
	
	passinput.attr("disabled", "disabled");
	passinput.css("background", "url('imgs/loading_16.gif') right center no-repeat");
	
	var data = null;
	$.ajax({
		url : "vpn_passwd.php?id=" + id + "&passwd=" + passinput.val(),
		dataType : 'json',
		success : function (ret) {
			if (ret.success != 1) {
				alert(ret.error);
			}
			else {
				$("#pass_" + id).text(passinput.val())
			}
			passwd_ui_revert(id);
		},
		fail : function (ret) {
			alert("Could not change password, please contact us for help.\n无法修改密码，请联系我们解决此问题。");
			passwd_ui_revert(id);
		}
	});
}

function passinput_keypress(evt, id) {
	if (evt.which == 13) {
		$("#passinput_" + id).blur();
	}
}

function passwd(id) {
	var passinput = $("#passinput_" + id);
	var passinfo = $("#passinfo_" + id);
	var pass = $("#pass_" + id);
	var changepass = $("#changepass_" + id);
	
	
	passinput.val(pass.text());
	
	passinput.keypress(function (event) {
		passinput_keypress(event, id);
	});
	
	passwd_ui_set(id);
	
	passinput.focus();
	passinput.select();
}


function passwd_ui_set(id) {
	var passinput = $("#passinput_" + id);
	var passinfo = $("#passinfo_" + id);
	var pass = $("#pass_" + id);
	var changepass = $("#changepass_" + id);
		
	passinput.val(pass.text());

	passinfo.css("display", "none");
	changepass.css("display", "");
}


function passwd_ui_revert(id) {
	var passinput = $("#passinput_" + id);
	var passinfo = $("#passinfo_" + id);
	var pass = $("#pass_" + id);
	var changepass = $("#changepass_" + id);
	
	passinput.removeAttr("disabled");
	passinput.css("background", "");
	
	passinfo.css("display", "");
	changepass.css("display", "none");
}
