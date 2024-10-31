<?php
/**
 * ProsperSearch Controller
 *
 * @package
 * @subpackage
 */
class ProsperSearchController
{
	public $searchModel;
    /**
     * the class constructor
     *
     * @package
     * @subpackage
     *
     */
    public function __construct()
    {
		require_once(PROSPER_MODEL . '/Search.php');
		$this->searchModel = new Model_Search();

		$this->searchModel->init();

		if (!$this->searchModel->_options['PSAct'])
		{
		    return;
		}

		add_action('wp_head', array($this->searchModel, 'ogMeta'), 2);
		add_filter('pre_get_document_title', array($this->searchModel, 'prosperTitle'), 10, 2);

		add_shortcode('prosper_store', array($this, 'storecode'));
		add_shortcode('prosper_search', array($this->searchModel, 'searchShortcode'));

		if (is_admin())
		{
			add_action('admin_print_footer_scripts', array($this->searchModel, 'qTagsStore'));
			add_action('admin_print_footer_scripts', array($this->searchModel, 'qTagsSearch'));
		}

		add_action( 'wp_enqueue_scripts', array($this->searchModel, 'prosperShopVars' ));
    }

	public function storecode()
	{
        ob_start();
		$this->storeShortcode();
		$store = ob_get_clean();
		return $store;
	}

	public function storeShortcode()
	{
		$options 	 = $this->searchModel->_options;
		$phtml 		 = $this->searchModel->getSearchPhtml();
		$searchPage  = $phtml[0];
		$productPage = $phtml[1];

		define('DONOTCACHEPAGE', true);
		$this->searchModel->storeChecker();
		if (!$this->searchModel->_storeResults)
		{
			$this->searchModel->_doStoreApiCalls();
		}
		$data = $this->searchModel->_storeParams;

		$params = $data['params'];
		$homeUrl = home_url('', 'http');
		if (is_ssl())
		{
			$homeUrl = home_url('', 'https');
		}

		$postArray = array(
		    'query' 	=> stripslashes($_POST['q']),
		    'dr' 	 	=> ($_POST['priceSliderMin'] || $_POST['priceSliderMax'] ? str_replace('$', '' , str_replace(',', '', $_POST['priceSliderMin']) . ',' . str_replace(',', '', $_POST['priceSliderMax'])) : ''),
		    'pr' 	 	=> ($_POST['percentSliderMin'] || $_POST['percentSliderMax'] ? str_replace('%', '' , $_POST['percentSliderMin'] . ',' . $_POST['percentSliderMax']) :''),
		    'merchant'  => stripslashes($_POST['merchant'])
		);

		if ($postArray = array_filter($postArray))
		{
			if (get_query_var('cid'))
			{
				$pageId      = get_option('prosperent_store_pageId');
				$page 	     = get_post($pageId);
				$data['url'] = $homeUrl . '/' . $page->post_name;
			}

		    if ($postArray['query'])
			{
				$recentOptions = get_option('prosper_productSearch');
				$recentOptions['recentSearches'][] = $postArray['query'];
				if (count($recentOptions['recentSearches']) > $recentOptions['numRecentSearch'])
				{
					$remove = array_shift($recentOptions['recentSearches']);
				}

				update_option('prosper_productSearch', $recentOptions);
			}

			$this->searchModel->getPostVars($postArray, $data);
		}

		if (get_query_var('cid'))
		{
			wp_dequeue_script( 'productPhp' );
			$this->productPageAction($data, $homeUrl, $productPage, $options);
			return;
		}

		$this->productAction($data, $homeUrl, 'product', $searchPage, $options);
	}

	public function productAction($data, $homeUrl, $type, $searchPage, $options)
	{
	    $view         = $data['view'];
		$filters 	  = $data['filters'];
		$params 	  = $data['params'];
		$related	  = $data['related'];
		$typeSelector = $data['typeSelector'];
		$target 	  = isset($options['Target']) ? '_blank' : '_self';
		$pickedFacets = array();
		$curlUrls	  = array();
		$url		  = $data['url'];
		$visitButton  = $options['VisitStoreButton'] ? $options['VisitStoreButton'] : 'Visit Store';
		$mainHomeUrl  = home_url('', (is_ssl() ? 'https' : 'http'));
		$currentUrl   = rtrim($mainHomeUrl . filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL), '/') . '/';

		$pageId       = get_option('prosperent_store_pageId');
		$page 	      = get_post($pageId);
		$beginUrl	  = $mainHomeUrl . '/' . $page->post_name;

		if ($options['Starting_Query'] && !$params['query'] && !$params['brand'] && !$params['category'] && !$params['merchant'])
		{
			$url .= '/query/' . htmlentities($options['Starting_Query']);
		}

		$query = stripslashes($params['query'] ? $params['query'] : ($options['Starting_Query'] && !$params['brand'] && !$params['category'] && !$params['merchant'] ? $options['Starting_Query'] : null));

		/*
		 * Backwards compatibility for old endpoints
		 */
		if (!$query)
		{
		    if ($params['celebrity'])
		    {
		        $query = $params['celebrity'];
		    }
		    elseif ($params['state'])
		    {
		        $query = $params['state'];
		    }
		    elseif ($params['city'])
		    {
		        $query = $params['city'];
		    }
		}

		/*
		 * Get title for results line
		 */
		if ($query)
		{
			$title = ucwords(strlen($query) > 60 ? (substr($query, 0, 30) . '...') : $query);
		}
		elseif ($params['merchant'])
		{
			$title =  implode(' or ', array_map('ucwords', explode('~', $params['merchant'])));
		}
		elseif ($params['brand'])
		{
			$title = implode(' or ', array_map('ucwords', explode('~', $params['brand'])));
		}
		elseif ($params['category'])
		{
			$title = ucwords(str_replace(',SL,', '/', $params['category']));
		}
		else
		{
			$title = 'Browse Products';
		}

		$dir = 'asc';
		$icon = '<i class="fa fa-sort"></i>';
		$sortedParam = 'rel';

		if ($params['sort'])
		{
		    $sortedParam           = str_replace(array('asc', 'desc', ' '), '', $params['sort']);
		    $sortedDir             = str_replace(array('price', 'merchant', ' '), '', $params['sort']);
		    ${dir . $sortedParam}  = $sortedDir == 'asc' ? 'desc' : 'asc';
            ${icon . $sortedParam} = '<i class="fa fa-sort-' . ($sortedParam == 'price' ? 'numeric-' : 'alpha-') . $sortedDir . '"></i>';
            $sortUrl               = rtrim(str_replace('/sort/' . rawurlencode($params['sort']), '', $currentUrl), '/');
		}

		$sortArray = array(
			'Relevance'			                               	      => 'rel',
			'Price ' . (isset($iconprice) ? $iconprice : $icon)       => 'price ' . ($dirprice ? $dirprice : $dir),
			'Store ' . (isset($iconmerchant) ? $iconmerchant : $icon) => 'merchant ' . ($dirmerchant ? $dirmerchant : $dir)
		);

		$everything = $this->searchModel->_storeResults;

		if ($everything['brands']['facets'] || $everything['merchants']['facets'])
		{
			$allFilters = array_merge((array) $everything['brands']['facets'], (array) $everything['merchants']['facets']);
			$filterArray = $this->searchModel->buildFacets($allFilters, $params, $filters, strtolower($url));
			$dRangeUrl = $pRangeUrl = $url;

			$pickedFacets = $filterArray['picked'];
			if ($params['dR'] || $params['dr'])
			{
				$drange 		= $params['dR'] ? $params['dR'] : $params['dr'];
				$priceSlider    = explode(',', $drange);
				$pickedFacets[] = '<span class="activeFilters"><a href="' . str_replace(array('/dR/' . rawurlencode($drange), '/dr/' . strtolower(rawurlencode($drange))), '', $url) . '/"><i style="padding-right:3px;" class="fa fa-times"></i>' . ($drange == '0,25' ? 'Under $25' : ($drange == '250,' ? '$250 and Above' : '$' . implode(' - $', $priceSlider))) . '</a></span>';
				$dRangeUrl      = rtrim(str_replace(array('/dR/' . rawurlencode($drange), '/dr/' . rawurlencode($drange)), '', $url), '/');
			}
			if ($params['pR'] || $params['pr'])
			{
				$prange 		= $params['pR'] ? $params['pR'] : $params['pr'];
				$percentSlider  = explode(',', $prange);
				$pickedFacets[] = '<span class="activeFilters"><a href="' . str_replace(array('/pR/' . rawurlencode($prange), '/pr/' . strtolower(rawurlencode($prange))), '', $url) . '/"><i style="padding-right:3px;" class="fa fa-times"></i>' . ($prange == '75,' ? '75% Off or More' : ($prange == '1,' ? 'All On Sale' : implode('% - ', $percentSlider) . ' % Off')) . '</a></span>';
				$pRangeUrl      = rtrim(str_replace(array('/pR/' . rawurlencode($prange), '/pr/' . rawurlencode($prange)), '', $url), '/');
			}

			ksort($filterArray['all']['brand']);
			ksort($filterArray['all']['merchant']);

			$mainFilters = array('brand' => $filterArray['all']['brand'], 'seller' => $filterArray['all']['merchant'] );
		}

		if ($results = $everything['results']['data'])
		{
			$totalFound = $everything['results']['totalRecordsFound'];
			$totalAvailable = $everything['results']['totalRecordsAvailable'];
		}
		else
		{
			$querySuggestion = $everything['results']['querySuggestion'];
			$noResults = true;
			$trend     = 'Popular Products';
		}

		require_once($searchPage);
	}

	public function productPageAction ($data, $homeUrl, $productPage, $options)
	{
		$params 	 = $data['params'];
		$prosperPage = get_query_var('prosperPage');
		$keyword 	 = rawurldecode(get_query_var('keyword'));
		$keyword 	 = str_replace(array(',sl,', ',SL,'), '/', $keyword);
		$target 	 = isset($options['Target']) ? '_blank' : '_self';
		$type   	 = 'product';
		$curlUrls    = array();
		$visitButton = $options['VisitStoreButton'] ? $options['VisitStoreButton'] : 'Visit Store';
		$fetch       = 'fetchProducts';
		$filter  	 = 'filterCatalogId';
		$group   	 = 'productId';
		$urltype 	 = 'prod';
		$expiration  = PROSPER_CACHE_PRODS;
		$pageId      = get_option('prosperent_store_pageId');
		$page 	     = get_post($pageId);

		$matchingUrl = $homeUrl . '/' . $page->post_name;
		$match 		 = '/' . str_replace('/', '\/', $matchingUrl) . '/i';
		if (preg_match($match, $_SERVER['HTTP_REFERER']) || preg_match('/type\/' . $urltype . '/i', $_SERVER['HTTP_REFERER']))
		{
			$returnUrl = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$returnUrl = $matchingUrl . '/query/' . get_query_var('keyword');
		}

		/*
		/  MAIN RECORD
		*/
		$mainRecord = $this->searchModel->_cidResults['data'];

		if (empty($mainRecord))
		{
			header('Location: ' . $matchingUrl . '/query/' . get_query_var('keyword'));
			exit;
		}

		/*
		/  GROUPED RESULTS
		*/
		$settings2 = array(
			'limit'           => 10,
			'filterProductId' => $settings['filterProductId'] = $mainRecord[0]['productId'],
			'enableFullData'  => 'FALSE'
		);

		$curlUrls['groupedResult'] = $this->searchModel->apiCall($settings2, $fetch);

		/*
		/  ALL RESULTS
		*/
		$settings3 = array(
			'limit'           => 10,
			'filterProductId' => $settings['filterProductId'] = $mainRecord[0]['productId'],
			'enableFullData'  => 'FALSE'
		);

		$curlUrls['results'] = $this->searchModel->apiCall($settings3, $fetch);

		/*
		/  SIMILAR
		*/
		$settings4 = array(
			'limit'              => 6,
			'query'		         => $settings['query'] = $mainRecord[0]['keyword'],
			'enableFullData'     => 'FALSE',
			'imageSize'		     => '250x250',
		    'relevancyThreshold' => $settings['relevancyThreshold'] = '1'
		);

		$curlUrls['similar'] = $this->searchModel->apiCall($settings4, $fetch);

		$settings['curlCall'] = 'multi-prodPage';

		$allData = $this->searchModel->multiCurlCall($curlUrls, $expiration, $settings);

		$groupedResult = $allData['groupedResult']['data'];
		$results 	   = $allData['results']['data'];
		$similar 	   = $allData['similar']['data'];

		require_once($productPage);
	}
}

$prosperProductSearch = new ProsperSearchController;