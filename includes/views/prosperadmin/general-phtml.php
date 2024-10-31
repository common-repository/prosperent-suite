<style>
.tzCheckBox{background:url('<?php echo PROSPER_IMG; ?>/adminImg/background.png') right bottom no-repeat;display:inline-block;min-width:45px;height:33px;white-space:nowrap;position:relative;cursor:pointer;margin-left:14px;float:right}.tzCheckBox.checked{background-position:top left;margin:0 14px 0 0}.linkOpt .tzCheckBox .tzCBContent{font-size:12px}.linkOpt .tzCheckBox.checked{margin:-6px 23px 0 0}.tzCheckBox .tzCBContent{color:#fff;line-height:31px;padding-right:38px;text-align:right;font-size:16px}.tzCheckBox.checked .tzCBContent{text-align:left;padding:0 0 0 38px}.tzCBPart{background:url('<?php echo PROSPER_IMG; ?>/adminImg/background.png') left bottom no-repeat;width:14px;position:absolute;top:0;left:-14px;height:33px;overflow:hidden}.tzCheckBox.checked .tzCBPart{background-position:top right;left:auto;right:-14px}.toolCards{background-color:#fff;padding:12px;box-shadow:0 1px 1px 0 rgba(0,0,0,.1);-webkit-box-shadow:0 1px 1px 0 rgba(0,0,0,.1)}
</style>

<script src="<?php echo PROSPER_JS; ?>/jquery.tzCheckbox.js"></script>
<script src="<?php echo PROSPER_JS; ?>/script.js"></script>
<?php
require_once(PROSPER_MODEL . '/Admin.php');
$prosperAdmin = new Model_Admin();

$options = get_option('prosperSuite');
$networkOptions = get_site_option('prosper_multisite');
$pageId   = get_option('prosperent_store_pageId');
$page 	  = get_post($pageId);

if (file_exists(WP_CONTENT_DIR . '/prosperentPrivateNetwork.php') && !$options['prosperPrivateNet'])
{
    $options['prosperPrivateNet'] = true;
    update_option('prosperSuite', $options);
}
elseif ($options['prosperPrivateNet'] && !file_exists(WP_CONTENT_DIR . '/prosperentPrivateNetwork.php'))
{
    $options['prosperPrivateNet'] = false;
    update_option('prosperSuite', $options);
}

$prosperAdmin->adminHeader( __( 'General Settings', 'prosperent-suite' ), true, 'prosperent_options', 'prosperSuite' );

if (!$options['Api_Key'] || strlen($options['Api_Key']) != 32)
{
	echo $prosperAdmin->hidden( 'PSAct' );
	echo $prosperAdmin->hidden( 'PICIAct' );
	echo $prosperAdmin->hidden( 'PLAct' );
	echo $prosperAdmin->hidden( 'Target' );
	echo $prosperAdmin->hidden( 'Enable_Caching' );
	echo $prosperAdmin->hidden( 'autoMinorUpdates' );
	echo $prosperAdmin->hidden( 'anonymousData' );

	echo '<script src="' . PROSPER_JS . '/getDetails.js"></script>';
	if ($_GET['disconnected'])
	{
		echo '<div style="max-width:900px;margin-bottom:24px;" class="update-nag">';
		echo '<span style="font-size:14px; padding-left:10px;font-weight:bold;">You Have Been Disconnected.</span>';
		echo '</div>';
	}

	if ($_GET['connected'])
	{
		echo '<script type="text/javascript">prosperConnect();</script>';
	}

	echo '<div style="margin-bottom:16px;font-size:24px;" id="prosperSignUpLogin"><a onClick="prosperConnect(this);return false;" href="https://prosperent.com/account/index/index/message/Sign%20in%20to%20connect%20the%20WordPress%20Plugin/connect/1/redir/' . urlencode(admin_url('admin.php?page=prosper_general&connected=true')) . '" target="_self">Connect</a> to Prosperent.</div>';
    echo '<div style="font-size:18px;">
    	      Or <a href="http://prosperent.com/join?utm_source=' . urlencode(preg_replace('/^https?:\/\/(www\\.)?/i', '', home_url())) . '&utm_medium=referral&utm_campaign=wp-suite-signup" target="_blank">Sign Up (It\'s free)</a> for a Prosperent account if you don\'t have one already.
          </div>';

    echo '<div style="margin-top:30px;font-size:14px;"><span id="prosperSignUpLogin" style="visibility:visible;"><a onClick="prosperConnectManual();return false;" href="javascript:void(0);">Manually Connect</a> to Prosperent.</span></div>';

    echo '<div id="prosperManualEntry" style="visibility:hidden;">';
    echo '<p class="prosper_desc" style="padding:0 0 0 20px;font-size:18px;font-weight:700;">' . __( 'Your Keys can be found <a href="https://prosperent.com/account/wordpress" target="_blank">here</a>!', 'prosperent-suite' ) . '</p>';
    echo $prosperAdmin->textinput( 'Api_Key', __( '<strong style="font-size:14px;">WordPress Key</strong>', 'prosperent-suite' ), '');
    echo '<p class="prosper_desc">' . __( '', 'prosperent-suite' ) . '</p><br>';

    echo $prosperAdmin->textinput( 'prosperAccess', __( '<strong style="font-size:14px;">Access Key</strong>', 'prosperent-suite' ), '');
    echo '<p class="prosper_desc">' . __( 'This is for the dashboard widget that will show your Clicks and Commissions earned from this domain.', 'prosperent-suite' ) . '</p>';

    echo $prosperAdmin->textinput( 'userId', __( '<strong style="font-size:14px;">Prosperent UserId</strong>', 'prosperent-suite' ), '');
    echo '<p class="prosper_desc">' . __( 'This is for Intercom, allows you to speak to us from your WordPress Prosperent settings.<br>Your userId can be found in the upper right hand corner when you are logged in to Prosperent, it is the 6 digit number inside the parenthesis after your user-name.', 'prosperent-suite' ) . '</p>';

    $prosperAdmin->adminFooter(true);
    echo '</div>';
}
else
{
	$widgets = wp_get_sidebars_widgets();
	$widgetArray = array();
	/*
	 * prosper_top_products
	 * prosperent_store
	 * prosperproductinsert
	 */
	foreach ($widgets as $widget)
	{
		if ($widget[0])
		{
			foreach ($widget as $part)
			{
				if (preg_match('/(prosper_top_products|prosperent_store|prosperproductinsert)-?(\d)?/i', $part, $match))
				{
					$widgetArray[$match[1]][$part] = $match[2];
				}
			}
		}
		else
		{
			if (preg_match('/prosper/i', $part, $matches))
			{
				$widgetArray[] = $widget;
			}
		}
	}

	if (is_multisite() && $networkOptions['SuperPICIAct'] || !is_multisite())
	{
		ksort($widgetArray['prosperproductinsert']);
?>

		<div class="toolCards" style="display:inline-block;width:100%;max-width:876px;margin-bottom:15px;vertical-align:top;">
		    <div style="padding:0 8px;display:block;margin-bottom:8px;"><img style="width:32px;padding-right:4px;vertical-align:bottom;" src="<?php echo PROSPER_IMG . '/adminImg/ProsperInsert Settings.png'; ?>"/><span style="font-size:24px;">ProsperInsert</span><input type="checkbox" class="prosperLights" id="PICIAct" name="prosperSuite[PICIAct]" <?php echo $options['PICIAct'] ? 'checked' : ''; ?> data-on="On" data-off="Off" /></div>
		    <h3>You <?php echo ($options['PICIAct'] ? 'now' : '<span style="color:red;">do not</span>');?> have the ability to add products or merchants into your content.</h3>
		    <div style="font-size:1em;margin-left:3em;"><ul style="list-style: disc"><li><a style="font-size:16px;" href="<?php echo admin_url('admin.php?page=prosper_createPI') ?>">Search For Products to Insert into Posts</a> </li><li>Look for the <i style="font-size:18px;padding:0 4px;" class="mce-ico mce-i-prosperent"></i> when editing to add products or merchants into your content.</li><li>Use the ProsperInsert Settings to add products to every page or post automatically.</li><li>Add the ProsperInsert Widget to your sidebar to showcase products.</li></ul></div>
		    <div style="text-align:left;"><a href="<?php echo admin_url('edit.php') ?>"><input style="font-size:13px;" type="button" value="Edit Posts"></a><a style="padding:0 8px;" href="<?php echo admin_url( 'admin.php?page=prosper_autoComparer'); ?>"><input style="font-size:13px;" type="button" value="ProsperInsert Settings"></a><a href="<?php echo admin_url( 'widgets.php?editwidget=prosperproductinsert&sidebar=sidebar-1&addnew=1&base=prosperproductinsert&num=' . ((int) array_pop($widgetArray['prosperproductinsert']) + 1)); ?>"><input style="font-size:13px;" type="button" value="Add ProsperInsert Widget"></a></div>
		</div>
<?php
	}

    if (is_multisite() && $networkOptions['SuperPLAct'] || !is_multisite())
    {
?>
		<div class="toolCards" style="display:inline-block;width:100%;max-width:876px;margin-bottom:15px;">
		    <div style="padding:0 8px;display:block;margin-bottom:8px;"><img style="width:32px;padding-right:4px;vertical-align:bottom;" src="<?php echo PROSPER_IMG . '/adminImg/ProsperLinks Settings.png'; ?>"/><span style="font-size:24px;">ProsperLinks</span><input type="checkbox" class="prosperLights" id="PLAct" name="prosperSuite[PLAct]" <?php echo $options['PLAct'] ? 'checked' : ''; ?> data-on="On" data-off="Off" /></div>
		    <h3>Ordinary links in your pages and posts are <?php echo ($options['PLAct'] ? 'now' : '<span style="color:red;">not</span>'); ?> being automatically converted into links that make you money.</h3>
		    <div style="font-size:1em;margin-left:3em;"><ul style="list-style: disc"><li>Look for the <i style="font-size:18px;padding:0 4px;" class="mce-ico mce-i-prosperent"></i> when editing to add product or merchant links to phrases or images.</li></ul></div>
		    <div style="text-align:left;"><a href="<?php echo admin_url('edit.php') ?>"><input type="button" value="Edit Posts"></a></div>
		</div>
<?php
    }

    if (is_multisite() && $networkOptions['SuperPSAct'] || !is_multisite())
    {
    	ksort($widgetArray['prosperent_store']);
    	ksort($widgetArray['prosper_top_products']);
?>
		<div class="toolCards" style="display:inline-block;margin-bottom:15px;width:100%;max-width:876px;">
		    <div style="padding:0 8px;margin-bottom:8px;"><img style="width:32px;padding-right:4px;vertical-align:bottom;" src="<?php echo PROSPER_IMG . '/adminImg/ProsperShop Settings.png'; ?>"/><span style="font-size:24px;">ProsperShop</span><input type="checkbox" class="prosperLights" id="PSAct" name="prosperSuite[PSAct]" <?php echo $options['PSAct'] ? 'checked' : ''; ?> data-on="On" data-off="Off" /></div>
		    <h4 style="color:red">The shop potentially adds thousands of pages to your blog. This helps provide additional traffic, but it can slow down blogs on shared hosting accounts. Please keep this in mind when activating the feature.</h4>
            <h3>You <?php echo ($options['PSAct'] ? 'now' : '<span style="color:red;">do not</span>');?> have a shop with millions of products from thousands of merchants so your visitors can search for products.</h3>
		    <div style="font-size:1em;margin-left:3em"><ul style="list-style: disc"><li>Control the shop with a few simple settings by clicking "Edit Your Shop" below.</li><li>Add the Popular Products and Search Bar Widgets to your sidebar so your visitors have easier access to the shop.</li><li>Add links to phrases in your posts/pages that point back to your shop to help get your visitors to your shop.</li></ul></div>
		    <div style="text-align:left"><a style="padding-right:8px;" target="BLANK" href="<?php echo (is_ssl() ? home_url('/', 'https') : home_url('/', 'http')) . $page->post_name; ?>"><input type="button" value="View Your Shop"></a><a style="padding-right:8px;" href="<?php echo admin_url( 'admin.php?page=prosper_productSearch'); ?>"><input type="button" value="Edit Your Shop"></a><a style="padding-right:8px;" href="<?php echo admin_url( 'widgets.php?editwidget=prosper_top_products&sidebar=sidebar-1&addnew=1&base=prosper_top_products&num=' . ((int) array_pop($widgetArray['prosper_top_products']) +1)); ?>"><input type="button" value="Add Popular Products Widget"></a><a style="padding-right:8px;" href="<?php echo admin_url( 'widgets.php?editwidget=prosperent_store&sidebar=sidebar-1&addnew=1&base=prosperent_store&num=' . ((int) array_pop($widgetArray['prosperent_store']) +1)); ?>"><input type="button" value="Add Search Bar Widget"></a><a style="padding-right:8px;" href="<?php echo admin_url( 'admin.php?page=prosper_advanced#prosperThemes'); ?>"><input type="button" value="Manage Shop Theme"></a></div>
		</div>
<?php
    }
?>
<div style="clear:both;margin-bottom:15px;"></div>

<?php
    if ($options['PSAct'] || $options['PICIAct'])
    {
        echo $prosperAdmin->checkbox( 'Target', __( '<strong style="font-size:14px">Open Links in New Window</strong>', 'prosperent-suite' ), true, '', 'Will Not Change ProsperLinks');
        echo '<p class="prosper_desc">' . __( "", 'prosperent-suite' ) . '</p><br>';

        echo $prosperAdmin->checkbox( 'Enable_Caching', __( '<strong style="font-size:14px">Caching</strong', 'prosperent-suite' ), true);
        if ($options['Enable_Caching'] &&  extension_loaded('memcache'))
        {
            echo '<a style="margin:10px 0 6px 35px; vertical-align:baseline;" class="button-secondary" href="' . admin_url( 'admin.php?page=prosper_general&clearCache&nonce='. wp_create_nonce( 'prosper_clear_cache' )) . '">' . __( 'Clear Memcache', 'prosperent-suite' ) . '</a>';
        }
        echo '<p class="prosper_desc">' . __( '', 'prosperent-suite' ) . '</p>';
    }
    else
    {
        echo $prosperAdmin->hidden( 'Enable_Caching' );
        echo $prosperAdmin->hidden( 'Target' );
    }

	if (!is_multisite())
	{
	    echo $prosperAdmin->checkbox( 'autoMinorUpdates', __( '<strong style="font-size:14px">Automatic Minor Updates</strong>', 'prosperent-suite' ), true);
	    echo '<p class="prosper_desc">' . __( "", 'prosperent-suite' ) . '</p>';
	}

//     echo $prosperAdmin->checkbox( 'anonymousData', __( '<strong style="font-size:14px">Send Usage Data Back to Us</strong>', 'prosperent-suite' ), true);
//     echo '<p class="prosper_desc">' . __( "This will help us better serve you by knowing which features are used the most and helping with support when needed.", 'prosperent-suite' ) . '</p>';
}

echo $prosperAdmin->hidden( 'Api_Key' );
echo $prosperAdmin->hidden( 'prosperAccess' );
echo $prosperAdmin->hidden( 'userId' );
//echo $prosperAdmin->textinput( 'Api_Key', __( '<strong style="font-size:14px;">Prosperent Key</strong>', 'prosperent-suite' ), '');
//echo '<p class="prosper_desc">' . __( '', 'prosperent-suite' ) . '</p><br>';

//echo $prosperAdmin->textinput( 'prosperAccess', __( '<strong style="font-size:14px;">Prosperent AccessKey</strong>', 'prosperent-suite' ), '');
//echo '<p class="prosper_desc">' . __( 'This is for the dashboard widget that will show your Clicks and Commissions earned from this domain.', 'prosperent-suite' ) . '</p>';

echo $prosperAdmin->hidden( 'shortCodesAccessed' );
echo $prosperAdmin->hidden( 'prosperNoOptions' );
echo $prosperAdmin->hidden( 'dismissOpenMessage' );
echo $prosperAdmin->hidden( 'prosperPrivateNet' );

if (($options['Api_Key'] || strlen($options['Api_Key']) == 32))
{
	$prosperAdmin->adminFooter(true, true);
}