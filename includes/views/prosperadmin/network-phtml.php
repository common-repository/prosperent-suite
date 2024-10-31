<style>
.tzCheckBox{background:url('<?php echo PROSPER_IMG; ?>/adminImg/background.png') right bottom no-repeat;display:inline-block;min-width:45px;height:33px;white-space:nowrap;position:relative;cursor:pointer;margin-left:14px;float:right}.tzCheckBox.checked{background-position:top left;margin:0 14px 0 0}.linkOpt .tzCheckBox .tzCBContent{font-size:12px}.linkOpt .tzCheckBox.checked{margin:-6px 23px 0 0}.tzCheckBox .tzCBContent{color:#fff;line-height:31px;padding-right:38px;text-align:right;font-size:16px}.tzCheckBox.checked .tzCBContent{text-align:left;padding:0 0 0 38px}.tzCBPart{background:url('<?php echo PROSPER_IMG; ?>/adminImg/background.png') left bottom no-repeat;width:14px;position:absolute;top:0;left:-14px;height:33px;overflow:hidden}.tzCheckBox.checked .tzCBPart{background-position:top right;left:auto;right:-14px}.toolCards{background-color:#fff;padding:12px;box-shadow:0 1px 1px 0 rgba(0,0,0,.1);-webkit-box-shadow:0 1px 1px 0 rgba(0,0,0,.1)}
</style>

<script src="<?php echo PROSPER_JS; ?>/jquery.tzCheckbox.js"></script>
<script src="<?php echo PROSPER_JS; ?>/script.js"></script>

<?php
require_once(PROSPER_MODEL . '/Admin.php');
$prosperAdmin = new Model_Admin();

$prosperAdmin->adminHeader( __( 'MultiSite Settings', 'prosperent-suite' ), true, 'prosperent_multisite_options', 'prosper_multisite');
$options = get_site_option('prosper_multisite');

echo $prosperAdmin->select( 'MultiSite_User', __( '<strong style="font-size:14px;">MultiSite User Access</strong>', 'prosperent-suite' ), array( 'admin' => __( 'Site Admins (default)', 'prosperent-suite' ), 'superadmin' => __( 'Super Admins Only', 'prosperent-suite' ) ) );
echo '<p class="prosper_desc">' . __( "", 'prosperent-suite' ) . '</p>';

?>
<div class="toolCards" style="display:inline-block;width:100%;max-width:876px;margin-bottom:15px;vertical-align:top;">
<div style="padding:0 8px;display:block;margin-bottom:8px;"><img style="width:32px;padding-right:4px;vertical-align:bottom;" src="<?php echo PROSPER_IMG . '/adminImg/ProsperInsert Settings.png'; ?>"/><span style="font-size:24px;">ProsperInsert</span><input type="checkbox" class="prosperLights" id="PICIAct" name="prosper_multisite[SuperPICIAct]" <?php echo $options['SuperPICIAct'] ? 'checked' : ''; ?> data-on="On" data-off="Off" /></div>
    <h3>Your network <?php echo ($options['SuperPICIAct'] ? 'now has' : '<span style="color:red;">does not have</span>');?> the ability to add products or merchants into their content.</h3>
</div>

<div class="toolCards" style="display:inline-block;width:100%;max-width:876px;margin-bottom:15px;">
    <div style="padding:0 8px;display:block;margin-bottom:8px;"><img style="width:32px;padding-right:4px;vertical-align:bottom;" src="<?php echo PROSPER_IMG . '/adminImg/ProsperLinks Settings.png'; ?>"/><span style="font-size:24px;">ProsperLinks</span><input type="checkbox" class="prosperLights" id="PLAct" name="prosper_multisite[SuperPLAct]" <?php echo $options['SuperPLAct'] ? 'checked' : ''; ?> data-on="On" data-off="Off" /></div>
    <h3>Ordinary links in your network's pages and posts are <?php echo ($options['SuperPLAct'] ? 'now' : '<span style="color:red;">not</span>'); ?> being automatically converted into links.</h3>
</div>

<div class="toolCards" style="display:inline-block;margin-bottom:15px;width:100%;max-width:876px;">
    <div style="padding:0 8px;margin-bottom:8px;"><img style="width:32px;padding-right:4px;vertical-align:bottom;" src="<?php echo PROSPER_IMG . '/adminImg/ProsperShop Settings.png'; ?>"/><span style="font-size:24px;">ProsperShop</span><input type="checkbox" class="prosperLights" id="PSAct" name="prosper_multisite[SuperPSAct]" <?php echo $options['SuperPSAct'] ? 'checked' : ''; ?> data-on="On" data-off="Off" /></div>
    <h3>Your network <?php echo ($options['SuperPSAct'] ? 'now has' : '<span style="color:red;">does not have</span>');?> a shop with millions of products from thousands of merchants so visitors can search for products.</h3>
</div>

<?php

echo $prosperAdmin->checkbox( 'autoMinorUpdates', __( '<strong style="font-size:14px">Automatic Minor Updates</strong>', 'prosperent-suite' ), true);
echo '<p class="prosper_desc">' . __( "", 'prosperent-suite' ) . '</p><br>';

$prosperAdmin->adminFooter();
