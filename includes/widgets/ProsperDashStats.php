<?php
class Widget_ProsperDashStats {

    /**
     * Hook to wp_dashboard_setup to add the widget.
     */
    public static function init() {
        //Register the widget...
        wp_add_dashboard_widget(
            'prosper_dash_stats',                                  //A unique slug/ID
            'Prosperent Stats',                             //Visible name for the widget
            array('Widget_ProsperDashStats', 'prosperGetStats')
        );
    }

    public static function prosperGetStats( )
    {
        $options = get_option('prosperSuite');

        if ($accessKey = $options['prosperAccess'])
        {
            require_once(PROSPER_MODEL . '/Search.php');
            $modelSearch = new Model_Search();
            $fetch = 'fetchClicks';
            $fetch2 = 'fetchCommissions';

            switch ($_GET['prosperDate'])
            {
                case 'yesterday':
                    $startDate = date('Ymd', strtotime('-1 days'));
                    $endDate = date('Ymd');
                    $timeFrame = 'yesterday';
                    break;
                case 'week':
                    $startDate = date('Ymd', strtotime('-7 days'));
                    $endDate = date('Ymd');
                    $timeFrame = 'the last 7 days';
                    break;
                default:
                    $startDate = date('Ymd', strtotime('-30 days'));
                    $endDate = date('Ymd');
                    $timeFrame = 'the last 30 days';
                    break;
            }

            $settings = array(
                'accessKey'      => $accessKey,
                'filterHttpHost' => $_SERVER['HTTP_HOST'],
                'limit'          => 1000
            );

            $clickGroup = 'clickDateYmd';
            $curlUrls['clickData'] = $modelSearch->apiCall(array_merge($settings, array(
                'groupBy'         => $clickGroup,
                'filterClickDate' => $startDate . ',' . $endDate
            )), $fetch);

            $commissionGroup = 'commissionDateYmd';
            $curlUrls['commissionData'] = $modelSearch->apiCall(array_merge($settings, array(
                'filterCommissionDate' => $startDate . ',' . $endDate,
                'filterCommissionType' => 'mine',
                'groupBy'              => $commissionGroup
            )), $fetch2);

            $everything = $modelSearch->multiCurlCall($curlUrls, PROSPER_CACHE_PRODS, array_merge($settings, array('date' => $startDate . ',' . $endDate)));

            $range = new DatePeriod(
                DateTime::createFromFormat('Ymd', $startDate),
                new DateInterval('P1D'),
                DateTime::createFromFormat('Ymd', $endDate)
            );

            $dateRange = array();
            foreach($range as $i => $date)
            {
                $dateRange[(string) $date->format('Ymd')] = array(
                    'x' => (strtotime($date->format('Y-m-d')) - 54000) * 1000,
                    'y' => 0
                );
            }

            $totalClicks = 0;
            $initialClicks = array();
            if ($everything['clickData']['data'])
            {
	            foreach ($everything['clickData']['data'] as $clicks)
	            {
	                $initialClicks[(string) $clicks[$clickGroup]] = array(
	                    'x' => (strtotime(DateTime::createFromFormat('Ymd', $clicks[$clickGroup])->format('Y/m/d')) - 54000) * 1000,
	                    'y' => $clicks['groupCount']
	                );

	                $totalClicks += $clicks['groupCount'];
	            }
            }

            $initialClicks += $dateRange;
            sort($initialClicks);

            $totalCommissions = 0;
            $totalPaymentAmount = 0;
            $initialCommissions = array();
            if ($everything['commissionData']['data'])
            {
	            foreach ($everything['commissionData']['data'] as $commissions)
	            {
	                $initialCommissions[(int) $commissions[$commissionGroup]] = array(
	                    'x' => (strtotime(DateTime::createFromFormat('Ymd', $commissions[$commissionGroup])->format('Y/m/d')) - 54000) * 1000,
	                    'y' => (float) $commissions['totalPaymentAmount']
	                );

	                $totalCommissions += $commissions['groupCount'];
	                $totalPaymentAmount += (float) $commissions['totalPaymentAmount'];
	            }
            }

            $initialCommissions += $dateRange;
            sort($initialCommissions);

            if (($initialCommissions || $initialClicks) || $_GET['prosperDate']) :
            ?>
                <table style="width:100%;">
                    <tr style="float:right;">
                        <td><a style="vertical-align:baseline;" class="button-secondary" href="<?php echo admin_url( 'index.php?prosperDate=week#prosper_dash_stats'); ?>">Last 7 Days</a></td>
                        <td><a style="margin-left:2px; vertical-align:baseline;" class="button-secondary" href="<?php echo admin_url( 'index.php?prosperDate=month#prosper_dash_stats'); ?>">Last 30 Days</a></td>
                    </tr>
                </table>
            <?php
            if (!$everything['commissionData']['data'] && !$everything['clickData']['data'])
            {
                echo '<div><span style="font-size:16px;font-weight:bold;display:block;padding:8px 0;">No stats for ' . $timeFrame . '.</span><span style="font-size:14px;">Please select a different range.</span></div>';
                return;
            }
            ?>
            <script type="text/javascript" src="<?php echo PROSPER_JS;?>/canvasjs.min.js"></script>
            <script type="text/javascript">

            window.onload = function(){
                document.getElementById("prosperClickContainer").style.display = "inline-block";
                var prosperClicks = new CanvasJS.Chart("prosperClickContainer",
                {
                  title:{
                    text: "Clicks"
                  },
                  animationEnabled: true,
                  axisX:{
                      valueFormatString: "MMM DD" ,
                      labelAngle: -50
                  },
                  axisY :{
                    includeZero: true,
                    valueFormatString: "#,###"
                  },
                  toolTip: {
                    shared: "true"
                  },
                  data: [
                  {
                	toolTipContent: "<strong>{x}</strong> - {y} Click(s)",
                    type: "splineArea",
                    xValueType: "dateTime",
                    dataPoints: <?php echo json_encode($initialClicks); ?>
                  }]
                });

                prosperClicks.render();

                document.getElementById("prosperCommissionContainer").style.display = "inline-block";
                var prosperCommissions = new CanvasJS.Chart("prosperCommissionContainer",
                {
                  title:{
                    text: "Commissions"
                  },
                  animationEnabled: true,
                  axisX:{
                      valueFormatString: "MMM DD" ,
                      labelAngle: -50
                  },
                  axisY :{
                    includeZero: true,
                    valueFormatString: "#,###.00",
                    prefix: "$"
                  },
                  toolTip: {
                    shared: "true"
                  },
                  data: [
                  {
                	toolTipContent: "<strong>{x}</strong> - {y}",
                    type: "splineArea",
                    xValueType: "dateTime",
                    yValueFormatString: "$####0.00",
                    name: 'Commissions',
                    dataPoints: <?php echo json_encode($initialCommissions); ?>
                  }]
                });

                prosperCommissions.render();
            }
              </script>

              <style>.prosperTable{width:100%}.prosperTable td{border:2px solid #bbb;margin:8px;text-align:center;padding:4px;width:49%}.prosperTableHeader{font-size:16px;border-bottom:2px solid #ddd;font-weight:700}.prosperTableContent{font-size:14px}</style>
              <?php if ($totalClicks > 0) :?>
              <div id="prosperClickContainer" style="display:none;height: 300px; width: 100%;"></div>
              <?php endif; ?>
              <div id="clickBoxes">
                  <table class="prosperTable" style="width:50%;margin:auto;">
                      <tr>
                          <td><div class="prosperTableHeader">Total Clicks</div><div class="prosperTableContent"><?=number_format($totalClicks)?></div></td>
                      </tr>
                  </table>
              </div>
              <?php if ($totalCommissions > 0) :?>
              <div id="prosperCommissionContainer" style="display:none;height: 300px; width: 100%;"></div>
              <div id="commissionBoxes">
                  <table class="prosperTable">
                      <tr>
                          <td><div class="prosperTableHeader">Commissions</div><div class="prosperTableContent"><?=number_format($totalCommissions)?></div></td>
                          <td><div class="prosperTableHeader">Total Earnings</div><div class="prosperTableContent">$<?=number_format($totalPaymentAmount, 2)?></div></td>
                      </tr>
                      <tr>
                          <td><div class="prosperTableHeader">Avg. Commission</div><div class="prosperTableContent">$<?=number_format($totalPaymentAmount/$totalCommissions, 2)?></div></td>
                          <td><div class="prosperTableHeader">EPC</div><div class="prosperTableContent">$<?=number_format($totalPaymentAmount/$totalClicks, 2)?></div></td>
                      </tr>

                  </table>
              </div>
            <?php
            else:
            ?>
            <div id="commissionBoxes">
                  <table class="prosperTable" style="width:50%;margin:auto;">
                      <tr>
                          <td><div class="prosperTableHeader">Commissions</div><div class="prosperTableContent"><?=number_format($totalCommissions)?></div></td>
                      </tr>
                  </table>
              </div>
            <?php
            endif;
            else:
            echo '<div><span style="font-size:16px;font-weight:bold;display:block;padding-bottom:6px">No stats to report.</span><span style="font-size:14px;">If you need any help, let us know.</span></div>';
            endif;
        }
        else
        {
        	if ($_POST['prosperAccess'])
        	{
        		$genOpts = get_option('prosperSuite');
        		$genOpts['prosperAccess'] = $_POST['prosperAccess'];
        		update_option('prosperSuite', $genOpts);
        		header("Location:". admin_url());
        		exit;
        	}
        	echo '<script src="' . PROSPER_JS . '/getDetails.js"></script>';
        	if ($_GET['connected'])
        	{        		echo '<script type="text/javascript">prosperConnect();</script>';
        	}
        	echo '<form action="' . admin_url() . '" method="post" id="prosper-conf"><input type="hidden" id="hidden_prosperAccess" name="prosperAccess"></form>';
            echo '<div><span style="font-size:16px;font-weight:bold;display:block;padding-bottom:6px">Opps.</span><span style="font-size:14px;">Your Prosperent Access Key is not set. <a style="font-weight:bold;" onClick="prosperConnect(this);return false;" href="https://prosperent.com/account/index/index/message/Sign%20in%20to%20connect%20the%20WordPress%20Plugin/connect/1/redir/' . urlencode(admin_url('index.php?connected=true')) . '" target="_self">Connect</a> to Prosperent to set it and then you will be able to see your Clicks and Commissions right here.</span></div>';
        }

    }
}