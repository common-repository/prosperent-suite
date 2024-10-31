<?php
$url = "http://" . $_SERVER['HTTP_HOST'] . filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
$result = preg_replace('/wp-content.*/i', '', $url);
$mainURL = preg_replace('/views.+/', '' , $url);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Create a Link</title>
		<link rel="stylesheet" href="<?php echo $mainURL . 'css/prosperMCE.css?v=4.1'; ?>">
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">
		<script data-cfasync="false" type="text/javascript" src="<?php echo $result . 'wp-includes/js/jquery/jquery.js'; ?>"></script>
		<script data-cfasync="false" type="text/javascript" src="<?php echo $result . 'wp-includes/js/tinymce/tiny_mce_popup.js'; ?>"></script>
		<script data-cfasync="false" type="text/javascript" src="<?php echo $result . 'wp-includes/js/tinymce/utils/mctabs.js'; ?>"></script>
		<script data-cfasync="false" type="text/javascript" src="<?php echo $mainURL . 'js/prosperMCE.js?v=4.3.61'; ?>"></script>
		<script type="text/javascript">
		jQuery(function(){document.getElementById("apiKey").value=parent.prosperSuiteVars.apiKey;var a=top.tinymce.activeEditor.windowManager.getParams();a&&(a=jQuery("<i "+a+">").attr("sl"),document.getElementById("edit").value=!0,"undefined"!=typeof a&&null!==a&&(document.getElementById("storeLink").value=a));jQuery(window).keydown(function(a){if(13==a.keyCode)return a.preventDefault(),!1})});var t;
		function isAffiliateUrl(){jQuery(".noAffiliate").hide();jQuery(".affiliate").hide();jQuery(".noURL").hide();jQuery(".checking").hide();clearTimeout(t);t=setTimeout(function(){try{if(0<jQuery("#storeLink").val().length){var a=jQuery("#storeLink").val().replace(/(^https?:?\/?\/?)/,""),a=a.replace(/\/.+/,""),b=a.replace(/^www\./,""),b=b.replace(/\/.+/,"");jQuery(".checking").show();0<a.length&&0<b.length?jQuery.ajax({type:"POST",url:"http://api.prosperent.com/api/merchant",
		data:{api_key:parent.prosperSuiteVars.apiKey,filterDomain:a+"|"+b,limit:1,filterDeepLinking:1,enableFullData:0},contentType:"application/json; charset=utf-8",dataType:"jsonp",success:function(a){a.data[0]?(jQuery(".affiliate").show(),jQuery(".noAffiliate").hide()):(jQuery(".noAffiliate").show(),jQuery(".affiliate").hide());jQuery(".noURL").hide();jQuery(".checking").hide()},error:function(){console.log("Failed to load data.")}}):(jQuery(".noURL").show(),jQuery(".noAffiliate").hide(),jQuery(".affiliate").hide(),
		jQuery(".checking").hide())}}catch(c){}},500)}function setFocus(){document.getElementById("storeLink").focus()};
        </script>
    </head>

    <base target="_self" />
    <body id="linker" role="application" aria-labelledby="app_label" onload="setFocus();isAffiliateUrl();">
        <div id="mainFormDiv" style="display:block;position:relative;z-index:1;width:100%;">
        	<form action="/" method="get" id="prosperSCForm">
                <input type="hidden" id="apiKey" name="apiKey"/>
				<input type="hidden" id="edit" name="edit"/>
                <input type="hidden" id="prosperSC" name="prosperSC" value="createLink"/>

   			    <div class="tabs" style="height:0px;">

				</div>
				<div class="panel_wrapper" style="padding: 5px 10px;height:80px;">
					<table style="width:100%">
						<tr>
							<td style="width:16%">
								<label style="width:100%"><strong>Store Link:</strong></label>
							</td>
							<td style="width:76%">
								<input style="display:inline;width:100%" class="prosperMainTextSC" tabindex="1" type="text" name="lk" id="storeLink" onKeyUp="isAffiliateUrl();" placeholder="Please Enter a Retailer or Product URL"/>
							</td>
							<td>
								<input style="float:right;display:inline;height:27px;line-height:27px;" tabindex="11" type="submit" value="Submit" class="button-primary" id="prosperMCE_submit" onClick="javascript:shortCode.insert(shortCode.local_ed);"/>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<div id="prosperInfo" style="font-weight:600;float:left;font-size:16px;margin-top:4px;">
									<span class="affiliate" style="color:green;"><i class="fa fa-check"></i> Congratulations, this URL is affiliatable!</span>
									<span class="noAffiliate" style="color:red;"><i class="fa fa-exclamation"></i> This URL is not a merchant we work with, yet.</br>This link will become affiliated once we start working with this merchant.</span>
									<span class="noURL" style="color:red;"><i class="fa fa-exclamation"></i> Please Enter a complete URL.</span>
                                    <span class="checking"><i class="fa fa-exclamation"></i> Checking your URL...</span>
								</div>
							</td>
							<td></td>
						</tr>
					</table>
                </div>
		    </form>
		</div>
    </body>
</html>

