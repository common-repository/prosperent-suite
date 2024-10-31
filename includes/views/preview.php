<style>
.prosperDetails{border:none;text-align:left;display:inline-block;width:100%;margin-left:7px}.prosperDetails .prosperDetContent{display:table-row}.prosperDetails .pDetailsImage{float:left;display:table-cell}.pDetailsAll{vertical-align:top;padding:6px;display:table-cell}.prosperDetails .pDetailsKeyword{font-size:18px;font-weight:700;margin-bottom:8px}.prosperDets{margin:6px;display:table;width:100%}.prosperDetsContain{margin-top:0;width:98%!important;border:1px solid #bebebe;box-shadow:0 2px 1px rgba(0,0,0,.1),0 0 1px rgba(0,0,0,.1);webkit-box-shadow:0 2px 1px rgba(0,0,0,.1),0 0 1px rgba(0,0,0,.1)}table.productResults,table.productResults td,table.productResults tr{border:none}.prosperDetails .pDetailsDesc{overflow:hidden;text-overflow:ellipsis;font-size:13px;display:table-row}
</style>
<script type="text/javascript">
function prosperProdDetails(a){resultWidth=jQuery("#prodresultsGoHere").width()-8;window.parentId=a.id;a=parentId;var b=jQuery("#"+a).attr("data-lirow");if(!jQuery("#"+a).attr("data-lirow"))return closeProsperDetails(),!1;e=b.match(/-(.*)/);b=b.match(/(.*)-/);e=parseInt(Math.floor(e[1]/b[1])*b[1])+5;b=jQuery("*[data-lirow]").length;parent=a;jQuery(".prosperDetails").remove();jQuery(".prosperpointer").remove();jQuery("#productList li:nth-child("+(b>e?e:b)+")").after('<li class="prosperDetails"></li>');
jQuery(".prosperDetails").get(0).scrollIntoView({block:"start",behavior:"smooth"});window.fixedQuery=a.replace(/_/g," ");jQuery.ajax({type:"POST",url:"http://api.prosperent.com/api/search",data:{api_key:apiKey.value?apiKey.value:apiKey,query:fixedQuery,imageSize:"250x250",limit:1,enableFullData:0},contentType:"application/json; charset=utf-8",dataType:"jsonp",success:function(a){0<a.data.length?jQuery.each(a.data,function(a,b){if(b.description){var c=b.description;if(250<c.length)var d=c.substr(0,
250),c=c.substr(249,c.length-250),c=d+'<span class="moreellipses">... </span><span class="morecontent"><span>'+c+'</span><a href="javascript:void(0);" class="morelink" onClick="moreDesc(this);">More</a></span>'}var f=document.getElementById(parentId),d=jQuery(f).offset().left,g=jQuery("#productList").offset().left,f=jQuery(f).outerWidth(!0),d=Math.floor(d-g+f/2-16)+"px";jQuery(".prosperDetails").html('<div><div class="prosperpointer" style="left:'+d+';height:14px;margin-top:10px;margin-bottom:-1px;position:relative;width:20px;z-index:1;"><img src="'+
imgLoc+'/arrow.png"/></div><div class="prosperDetsContain"><div style="position:relative;padding:4px 4px 0 0;"><a href="javascript:void(0);" onClick="closeProsperDetails();"><i style="color:red;display:inline-block;float:right;font-size:16px;" class="fa fa-times"></i></a></div><div class="prosperDets"><div class="prosperDetContent"><div class="pDetailsImage"><img style="width:175px;height:175px;" src="'+b.image_url+'" alt="'+b.keyword+'" title="'+b.keyword+'" /></div><div class="pDetailsAll"><div class="pDetailsKeyword">'+
b.keyword+'</div><div class="pDetailsDesc">'+c+'</div><table class="productResults"></table><div style="display:table-row;float:right;"><input tabindex="11" type="submit" value="Show More Like This" class="button-primary" style="margin-right:4px;" onClick="shoMoYo();return false;" id="prosperMCE_submit"><input tabindex="11" type="submit" style="margin-right:4px;" value="Add This Product" class="button-primary" id="prosperMCE_submit" onClick="getIdofItem('+parentId+');return false;"></div></div></div></div></div></div></div>');
jQuery.ajax({type:"POST",url:"http://api.prosperent.com/api/search",data:{api_key:apiKey.value?apiKey.value:apiKey,filterProductId:b.productId,groupBy:"merchant",limit:10,enableFullData:0,imageSize:"75x75"},contentType:"application/json; charset=utf-8",dataType:"jsonp",success:function(a){jQuery.each(a.data,function(a,b){jQuery(".productResults").append('<tr><td><img style="width:80px;height:40px;" src="http://images.prosperentcdn.com/images/logo/merchant/original/120x60/'+b.merchantId+".jpg?prosp=&m="+
b.merchant+'"/></td><td style="vertical-align:middle;"><strong>$'+(b.price_sale?b.price_sale:b.price)+"</strong></td></tr>")})},error:function(){}})}):(jQuery(".prosperDetails").remove(),jQuery(".prosperpointer").remove())},error:function(){}})}function closeProsperDetails(){jQuery(".prosperDetails").remove()}
function moreDesc(a){jQuery(a).hasClass("less")?(jQuery(a).removeClass("less"),jQuery(a).html("More")):(jQuery(a).addClass("less"),jQuery(a).html("Less"));jQuery(a).parent().prev().toggle();jQuery(a).prev().toggle();return a}function shoMoYo(){jQuery("#prodquery").val(fixedQuery);showValues();return!1};</script>
<?php
error_reporting(0);
$params = array_filter($_GET);
$type = $params['type'];
$endPoints = array(
	'fetchMerchant'	   => 'http://api.prosperent.com/api/merchant?',
	'fetchProducts'	   => 'http://api.prosperent.com/api/search?'
);

if ($type == 'merchant')
{
	$fetch = 'fetchMerchant';
	$merchants = array_map('trim', explode(',', urldecode($params['merchantm'])));
	$settings = array(
		'filterMerchant' =>  $merchants[0] ? '*' . $merchants[0] . '*' : '',
	    'filterCategory' => $params['merchantcat'] ? '*' . $params['merchantcat'] . '*' : '',
	    'imageType'      => ($params['merchantImageType'] ? trim($params['merchantImageType']) : 'original'),
		'imageSize'		 => '120x60'
	);
}
else
{
	$fetch = 'fetchProducts';

	$merchantIds = explode(',', $params['prodd']);
	$brands      = explode(',', $params['prodb']);

	$settings = array(
		'query'            => trim($params['prodq']),
		'filterMerchantId' => $merchantIds,
		'filterBrand'      => $brands,
		'imageSize'		   => '250x250',
		'groupBy'	       => 'productId',
		'filterPriceSale'  => $params['onSale'] ? ($params['pricerangea'] || $params['pricerangeb'] ? $params['pricerangea'] . ',' . $params['pricerangeb'] : '0.01,') : '',
		'filterPrice' 	   => $params['onSale'] ? '' : ($params['pricerangea'] || $params['pricerangeb'] ? $params['pricerangea'] . ',' . $params['pricerangeb'] : '')
	);
}

$settings = array_merge(array(
	'api_key'        => $params['apiKey'],
	'limit'          => 80,
	'enableFacets'	 => 'FALSE'
), $settings);

$settings = array_filter($settings);
// Set the URL
$url = $endPoints[$fetch] . http_build_query ($settings);

$curl = curl_init();

// Set options
curl_setopt_array($curl, array(
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_URL => $url,
	CURLOPT_CONNECTTIMEOUT => 30,
	CURLOPT_TIMEOUT => 30
));

// Send the request
$response = curl_exec($curl);

// Close request
curl_close($curl);

// Convert the json response to an array
$response = json_decode($response, true);

// Check for errors
if (count($response['errors']))
{
	//throw new Exception(implode('; ', $response['errors']));
}

if ($results = $response['data'])
{
	?>
	<div id="productList">
    	<ul style="display:inline-block;padding:0;margin:0;">
        	<?php
        	if ($type == 'merchant')
        	{
        		foreach ($results as $record)
        		{
        		    $prosperId = $record['merchantId'];
    				?>
    				<li id="<?php echo $prosperId; ?>" onClick="getIdofItem(this);" class="productSCFull" style="overflow:hidden;list-style:none;margin:6px;float:left;height:86px!important;width:136px!important;background-color:<?php echo ($params['imageType'] == 'white' ? $record['color1'] : '#fff'); ?>">
        				<div class="listBlock">
        					<div class="prodImage" style="text-align:center;">
        					    <div style="float:right;" onClick="return false();"><a href="http://<?php echo $record['domain']; ?>" target="_blank"><i style="font-size:14px;" class="fa fa-search"></i></a><span id="prosperCheckbox" style="position:relative;color:<?php echo ($params['imageType'] == 'white' ? '#000!important' : '#fff'); ?>"></span></div>
    				        	<span title="<?php echo $record['merchant']; ?>"><img class="newImage" style="height:60px!important;width:120px!important;" src='<?php echo $record['logoUrl']; ?>'  alt='<?php echo $record['merchant']; ?>' title='<?php echo $record['merchant']; ?>'/></span>
    				        </div>
			            </div>
    				</li>
    				<?php
        		}
        	}
			else
			{
		        foreach ($results as $i => $record)
		        {
	                $groupCount = $record['groupCount'];
		            $priceSale = $record['priceSale'] ? $record['priceSale'] : $record['price_sale'];
		            $price 	   = $priceSale ? $priceSale : $record['price'];
		            $prosperId = str_replace(' ', '_', $record['keyword']);
		            ?>
    				<li data-lirow="5-<?=$i?>" id="<?php echo $prosperId; ?>" data-prodid="<?php echo $record['productId']; ?>" onClick="getIdofItem(this);" class="productSCFull" style="overflow:hidden;list-style:none;margin:4px;float:left;height:240px!important;width:170px!important;background-color:white;">
        				<div class="listBlock">
        					<div class="prodImage" style="text-align:center;">
        					    <div style="float:right;"><a style="margin-right:4px;" id="testing" onClick="return prosperProdDetails(<?php echo $prosperId; ?>)"><i title="View Product Details" style="font-size:14px;" class="fa fa-plus"></i></a><a id="testing" href="<?php echo $record['affiliate_url']; ?>" target="_blank"><i title="View on Merchant Site" style="font-size:14px;" class="fa fa-search"></i></a><span id="prosperCheckbox" ></span></div>
    				        	<span title="<?php echo $record['keyword']; ?>"><img class="newImage" src='<?php echo ($record['logoUrl'] ? $record['logoUrl'] : $record['image_url'] ); ?>' alt="<?php echo $record['keyword']; ?>" title="<?php echo $record['keyword']; ?>" style="width:100%!important;max-width:100%"/></span>
    				        </div>
    				        <div class="prodContent" style="font-size:15px;text-overflow:ellipsis;white-space:nowrap;-webkit-hyphens:auto;-moz-hyphens:auto;hyphens:auto;word-wrap:break-word;overflow:hidden;vertical-align:top;"">
    				            <?php echo ($record['brand'] ? $record['brand'] : '&nbsp;'); ?>
        						<div class="prodTitle">
        						    <div class="prodPrice">
            						    <?php if ($priceSale): ?>
            						        <span style="color:#666;font-size:14px;text-decoration:line-through;">$<?php echo number_format($record['price'], 2); ?></span>
            						    <?php endif;
            						    if ($record['groupCount'] > 1):?>
            						        <span class="prosperPrice">$<?php echo number_format($record['minPrice'], 2); ?></span><?php echo '<span class="prosperExtra" style="display:inline-block;color:#666;font-size:14px;font-weight:normal;"> <span style="color:#666;font-size:12px;font-weight:normal;">&nbsp;from </span>' . $groupCount . ' stores</span>'; ?>
            						    <?php else: ?>
            							    <span class="prosperPrice">$<?php echo number_format($price, 2); ?></span><?php if ($record['merchant']){echo '<span class="prosperExtra" style="display:inline-block;color:#666;font-size:14px;font-weight:normal;text-overflow:ellipsis;white-space:nowrap;-webkit-hyphens:auto;-moz-hyphens:auto;hyphens:auto;word-wrap:break-word;overflow:hidden;vertical-align:top;"> <span style="color:#666;font-size:12px;font-weight:normal;">&nbsp;from </span>' . $record['merchant'] . '</span>'; } ?>
             							<?php endif; ?>
        							</div>
        						</div>
        					</div>
			            </div>
    				</li>
    				<?php
		        }
        	}
        	?>
	    </ul>
	</div>
	<?php
}
else
{
	echo '<h2 style="margin-left:12px;">No Results From Prosperent</h2>';
    echo '<h2 style="font-size:20px;margin-left:12px;">Please Try Another Search</h2>';

	if ($_GET['prosperSC'] == 'linker')
	{
	   echo '<div style="color:white;margin-left:12px;" class="noResults-secondary">- or -</div>';
	   echo '<div style="font-size:18px;margin-left:12px;color:white">Enter the Product Url From the Merchant';
	   echo '<span></span>';
	   echo '<input class="prosperMainTextSC" tabindex="1" type="text" name="prosperHeldURL" id="prosperHeldURL" value="http://"/></div>';
	   echo '<div style="margin-left:12px;color:white;font-size:14px;">If ProsperLinks is active and we get this merchant in the future, this link will be automatically affiliated for you.</div>';
	}
}

?>
<script type="text/javascript">
	var a = getNewCurrent();
	if (jQuery("#"+a+"id").val())
	{
		var ids = (jQuery("#prodid").val()).split("~");
		jQuery.each(ids, function(c, d) {
			var id = d.replace(' ', '_');
			if (document.getElementById(id))
			{
				jQuery(document.getElementById(id)).addClass("highlight");
			};
		});
	};

	jQuery("a#testing").click(function(e) {
	   e.cancelBubble = true;
	   e.stopPropagation();  // to prevent the default action of anchor elements
	});
</script>
