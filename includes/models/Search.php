<?php
require_once(PROSPER_MODEL . '/Base.php');
/**
 * Search Model
 *
 * @package Model
 */
class Model_Search extends Model_Base
{
	public $_options;
	public $_storeResults;
	public $_cidResults;
	public $_params;
	public $_storeParams;

	public function init()
	{
		$this->_options = $this->getOptions();
	}

	public function prosperShopVars()
	{
	    $keys = array(
	        'api' => $this->_options['Api_Key'],
	        'img' => PROSPER_IMG,
	        'imk' => $this->_options['ImageCname'],
	        'cmk' => $this->_options['ClickCname'],
	        'vbt' => $this->_options['VisitStoreButton'],
	    	'sdp' => $this->_options['stopDetailsPopup']
	    );

	    echo '<script type="text/javascript">var _prosperShop = ' . json_encode($keys) . '</script>';
	}

	public function qTagsStore()
	{
		$id 	 = 'prosperStore';
		$display = 'ProsperShop';
		$arg1 	 = '[prosper_store]';
		$arg2 	 = '[/prosper_store]';

		$this->qTagsProsper($id, $display, $arg1, $arg2);
	}

	public function qTagsSearch()
	{
		$id 	 = 'prosperSearch';
		$display = 'Prosper Search Bar';
		$arg1 	 = '[prosper_search w="WIDTH" css="ADDITIONAL CSS"]';
		$arg2 	 = '[/prosper_search]';

		$this->qTagsProsper($id, $display, $arg1, $arg2);
	}

	public function getUrlParams()
	{
	    $params = explode('/', rawurldecode(strtolower(get_query_var('queryParams'))));

		$sendParams = array();
		foreach ($params as $k => $p)
		{
			//if the number is even, grab the next index value
			if (!($k & 1))
			{
				$sendParams[$p] = trim(str_ireplace(',SL,', '/', $params[$k + 1]));
			}
		}

		//unset($sendParams['type']);
		return $sendParams;
	}

	public function getPostVars($postArray, $data)
	{
	    $filteredPostArray = array_filter($postArray);
		$newUrl = $data['url'];
		if (preg_match('/\/\?gclid=.+/i', $newUrl))
		{
			$newUrl = preg_replace('/\/\?gclid=.+/i', '', $newUrl);
		}

		$postArray = array();
		foreach ($filteredPostArray as $i => $post)
		{
			$post = trim(strtolower(htmlentities(rawurlencode(str_replace('/', ',sl,',$post)))));
			$postArray[$i] = filter_var($post, FILTER_SANITIZE_STRING);
		}

		$newUrl = str_replace(array(
    		    '/pR/' . rawurlencode($data['params']['pR']),
    		    '/dR/' . rawurlencode($data['params']['dR']),
				'/pr/' . rawurlencode($data['params']['pr']),
				'/dr/' . rawurlencode($data['params']['dr']),
    		    '/city/' . rawurlencode($data['params']['city']),
    		    '/state/' . rawurlencode($data['params']['state']),
    		    '/zip/' . $data['params']['zip'],
    		    '/page/' . $data['params']['page'],
    		    '/celebrity/' . $data['params']['celebrity'],
    		    '/sort/' . rawurlencode($data['params']['sort']),
    		    '/celebQuery/' .  rawurlencode($data['params']['celebQuery']),
    		    '/cid/' . $data['params']['cid'],
    		    '/type/' . $data['params']['type']
    		), '', $newUrl
		);

		while (current($postArray))
		{
			$newUrl = str_replace('/' . key($postArray) . '/' . strtolower(htmlentities(rawurlencode(str_replace('/', ',SL,', $data['params'][key($postArray)])))), '', $newUrl);
			$newUrl = $newUrl . '/' . key($postArray) . '/' . current($postArray);
			next($postArray);
		}

		header('Location: ' . rtrim($newUrl, '/') . '/');
		exit;
	}

	public function getBrands($brand = null)
	{
        $brands = array();

		if ($brand)
		{
			$brands = explode('~', strtolower($brand));
			$brands = array_combine($brands, $brands);
		}

		return array('appliedFilters' => $brands);
	}

	public function getMerchants($merchant = null)
	{
		$filterMerchants = array();
	    $merchants = array();

		if ($merchant)
		{
			$merchants = explode('~', strtolower($merchant));
			$merchants = array_combine($merchants, $merchants);
		}

		if ($this->_options['PositiveMerchant'])
		{
		    $postMercOpts = rtrim($this->_options['PositiveMerchant'], '|');
			$filterMerchants = array_map('trim', explode('|', $postMercOpts));
		}

		if ($this->_options['NegativeMerchant'])
		{
		    $negMercOpts = '!' . str_replace('|', '|!', rtrim($this->_options['NegativeMerchant'], '|'));
			$filterMerchants = array_merge($filterMerchants, array_map('trim', explode('|', $negMercOpts)));
		}

		return array('appliedFilters' => $merchants, 'allFilters' => $filterMerchants);
	}

	public function getCategories($category = null)
	{
		$filterCategory = array();
		$categories = array();

		if ($category)
		{
		    $categories = explode('~', '*' . strtolower($category). '*');
		    $categories = array_combine($categories, $categories);
		}

		if ($this->_options['ProsperCategories'])
		{
		    $catOpts = rtrim(str_replace(array('_', '|'), array(' ', '*|'), $this->_options['ProsperCategories']), '|');
		    $filterCategory = array_map('trim', explode('|', $catOpts));
		}

		return array('appliedFilters' => $categories, 'allFilters' => $filterCategory);
	}

	public function buildFacets($facets, $params, $filters, $url)
	{
		if (preg_match('/\/\?gclid=.+/i', $url))
		{
			$url = preg_replace('/\/\?gclid=.+/i', '', $url);
		}

		$activeFilters = array();
		$facetsNew = array('brand' => array(), 'merchant' => array());
		$facetsPicked = array();
		foreach ($facets as $i => $facetArray)
		{
			$activeFilters[$i] = $filters[$i]['appliedFilters'];
			$lowerEncodeParam = strtolower(rawurlencode($params[$i]));
			foreach ($facetArray as $facet)
			{
				$lowFacetValue = strtolower($facet['value']);
				if (isset($filters[$i]['appliedFilters'][$lowFacetValue]))
				{
					if (count($filters[$i]['appliedFilters']) > 1)
					{
						$newFilters = $filters[$i]['appliedFilters'];
						unset($newFilters[$lowFacetValue]);
						$facetsNew[$i][$lowFacetValue] = '<li class="prosperActive"><a href="' . (str_replace(array('/cid/' . $params['cid'], '/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/' . $i . '/' . rawurlencode(implode('~', $newFilters))) . '/"' . ($this->_options['noFollowFacets'] ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i class="fa fa-times"></i><span>' . $facet['value'] . '</span></a></li>';
						$facetsPicked[] = '<span class="activeFilters"><a href="' . (str_replace(array('/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/' . $i . '/' . rawurlencode(implode('~', $newFilters))) . '/"' . ($this->_options['noFollowFacets'] ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i style="padding-right:3px;" class="fa fa-times"></i>' . $facet['value'] . '</a></span>';
					}
					else
					{
						$facetsNew[$i][$lowFacetValue] = '<li class="prosperActive"><a href="' . str_replace(array('/cid/' . $params['cid'], '/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/"' . ($this->_options['noFollowFacets'] ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i class="fa fa-times"></i><span>' . $facet['value'] . '</span></a></li>';
						$facetsPicked[] = '<span class="activeFilters"><a href="' . str_replace(array('/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/"' . ($this->_options['noFollowFacets'] ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i style="padding-right:3px;" class="fa fa-times"></i>' . $facet['value'] . '</a></span>';
					}
					unset($activeFilters[$i][$lowFacetValue]);
				}
				elseif ($facet['value'])
				{
					$facetsNew[$i][$lowFacetValue] = '<li class="prosperFilter"><a href="' . (str_replace(array('/cid/' . $params['cid'], '/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/' . $i . '/' . rawurlencode(str_replace('/', ',SL,', $lowFacetValue) . ($params[$i] ? '~' .  $params[$i] : ''))) . '/"' . (isset($this->_options['noFollowFacets']) ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i class="fa fa-times"></i><span>' . $facet['value'] . '</span></a></li>';
				}
			}

			if (isset($activeFilters[$i]))
			{
				foreach ($activeFilters[$i] as $filter)
				{
					if (count($filters[$i]['appliedFilters']) > 1)
					{
						$newFilters = $filters[$i]['appliedFilters'];
						unset($newFilters[$filter]);
						$facetsNew[$i][ucwords($filter)] = '<li class="prosperActive"><a href="' . (str_replace(array('/cid/' . $params['cid'], '/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/' . $i . '/' . rawurlencode(implode('~', $newFilters))) . '/"' . ($this->_options['noFollowFacets'] ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i class="fa fa-times"></i><span>' . ucwords($filter) . '</span></a></li>';
						$facetsPicked[] = '<span class="activeFilters"><a href="' . (str_replace(array('/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/' . $i . '/' . rawurlencode(implode('~', $newFilters))) . '/"' . ($this->_options['noFollowFacets'] ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i style="padding-right:3px;" class="fa fa-times"></i>' . ucwords($filter) . '</a></span>';
					}
					else
					{
						$facetsNew[$i][ucwords($filter)] = '<li class="prosperActive"><a href="' . str_replace(array('/cid/' . $params['cid'], '/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/"' . ($this->_options['noFollowFacets'] ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i class="fa fa-times"></i><span>' . ucwords($filter) . '</span></a></li>';
						$facetsPicked[] = '<span class="activeFilters"><a href="' . str_replace(array('/page/' . $params['page'], '/' . $i . '/' . $lowerEncodeParam),  '', $url) . '/"' . ($this->_options['noFollowFacets'] ? ' rel="nofollow,nolink"' : ' rel="nolink"') . '><i style="padding-right:3px;" class="fa fa-times"></i>' . ucwords($filter) . '</a></span>';
					}
				}
			}
		}

		return array('picked' => $facetsPicked, 'all' => $facetsNew);
	}

	public function _doStoreApiCalls ($data = array())
	{
		$imageSize = '250x250';
		$fetch 	   = 'fetchProducts';
		$currentId = get_the_ID();
		$storeId   = (int) get_option('prosperent_store_pageId');
		$options   = $this->_options;
		if (!$data)
		{
			$data = $this->storeSearch();
		}
		$filters   = $data['filters'];
		$params    = $data['params'];
		$query     = $params['query'] ? $params['query'] : ($this->_options['Starting_Query'] && !$params['brand'] && !$params['category'] && !$params['merchant'] ? $this->_options['Starting_Query'] : '');

		if (get_query_var('cid'))
		{
			$prosperPage = get_query_var('prosperPage');
			$imageSize = '250x250';
			$settings = array(
				'limit' 	      => 1,
				'imageSize'       => $imageSize,
				'curlCall'	      => 'single-productPage-' . $prosperPage,
				'filterCatalogId' => get_query_var('cid')
			);

			$curlUrl = $this->apiCall($settings, $fetch);

			$allData = $this->singleCurlCall($curlUrl, 0, $settings);

			$this->_cidResults = $allData;
			if ($prosperPage = get_query_var('prosperPage'))
			{
				return;
			}
		}

		$discountRange 	   = $params['pr'];
		$priceRange 	   = $params['dr'];
		$brandFilters 	   = implode('|', ($filters['brand']['appliedFilters'] ? $filters['brand']['appliedFilters'] : $filters['brand']['allFilters']));
		$merchantFilters   = implode('|', $filters['merchant']['appliedFilters']);
		$merchantIdFilters = $merchantFilters ? '' : implode('|', $filters['merchant']['allFilters']);
		$categoryFilters   = implode('|', ($filters['category']['appliedFilters'] ? $filters['category']['appliedFilters'] : $filters['category']['allFilters']));
		$priceFilter 	   = $discountRange ? 'filterPriceSale' : 'filterPrice';

		if ($query || $brandFilters || $merchantFilters || $merchantIdFilters || $filters['category']['appliedFilters'])
		{
			$settings = array(
				'limit'			  		=> $options['Pagination_Limit'],
				'imageSize'		   		=> $imageSize,
				'curlCall'		   		=> 'multi-product',
				'page'			   		=> $params['page'],
				'query'            		=> $query,
				'sortBy'	       		=> $params['sort'] != 'rel' ? $params['sort'] : '',
				'filterBrand'      		=> $brandFilters,
				'filterMerchant'   		=> $merchantFilters,
				'filterMerchantId' 		=> $merchantIdFilters,
				'filterCategory'   		=> $categoryFilters,
				$priceFilter	   		=> $priceRange,
				'filterPercentOff' 	    => $discountRange,
				'enableQuerySuggestion' => true
			);

			$curlUrls['results'] = $this->apiCall($settings, $fetch);
		}

		if ($options['Enable_Facets'] && ($query || $brandFilters || $merchantFilters || $merchantIdFilters || $filters['category']['appliedFilters']))
		{
			$merchantFacetSettings = array(
				'enableFullData'   => 'FALSE',
				'imageSize'        => '75x75',
				'query'            => $query,
				'enableFacets'     => 'merchant',
				'limit'			   => 1,
				'filterMerchantId' => $filters['merchant']['allFilters'],
				'filterMerchant'   => (($filters['merchant']['appliedFilters'] && !$query) ? $filters['merchant']['appliedFilters'] : ''),
				'filterCategory'   => $categoryFilters,
				'filterBrand'	   => $brandFilters,
				$priceFilter	   => $priceRange,
				'filterPercentOff' => $discountRange
			);

			$curlUrls['merchants'] = $this->apiCall($merchantFacetSettings, $fetch);

			$brandFacetSettings = array(
				'imageSize'        => '75x75',
				'enableFullData'   => 'FALSE',
				'query'            => $query,
				'enableFacets'     => 'brand',
				'limit'			   => 1,
				'filterBrand'      => (($filters['brand']['appliedFilters'] && !$query && !$filters['merchant']['appliedFilters'] && !$filters['merchant']['allFilters']) ? implode('|', $filters['brand']['appliedFilters']) : ''),
				'filterMerchant'   => $merchantFilters,
				'filterMerchantId' => $merchantIdFilters,
				'filterCategory'   => $categoryFilters,
				$priceFilter	   => $priceRange,
				'filterPercentOff' => $discountRange
			);

			$curlUrls['brands'] = $this->apiCall($brandFacetSettings, $fetch);
		}

		$everything = $this->multiCurlCall($curlUrls, PROSPER_CACHE_PRODS, $settings);

		if (!$everything['results']['data'] && count($data['params']) > 1 && $data['params']['query'])
		{
			$qParam = $data['params']['query'];
			$queryParam = 'query/' . rawurlencode($qParam) . '/';
			set_query_var('queryParams', $queryParam);
			$data['params'] = array('query' => $qParam);
			$data['url'] = $homeUrl . '/' . $data['base'] . '/' . rtrim($queryParam, '/');
			$data['related'] = $this->_params;
			unset($data['filters']['brand']['appliedFilters'], $data['filters']['merchant']['appliedFilters']);
			$this->_storeParams = $data;

			if (!$options['stopRelatedProducts'])
			{
				?>
				<script type="text/javascript">window.history.replaceState('Related', '', "/<?php echo $data['base'] . '/' . $queryParam; ?>");</script>
				<?php

				return $this->_doStoreApiCalls($data);
			}
		}

		$this->_storeResults = $everything;
		return $this->_storeResults;
	}

	public function getSearchPhtml()
	{
		$phtml[0] = PROSPER_VIEW . '/prospersearch/themes/Default/product.php';
		$phtml[1] = PROSPER_VIEW . '/prospersearch/productPage.php';

		// Product Search CSS for results and search
		if ($this->_options['Set_Theme'] == 'Default' || !$this->_options['Set_Theme'])
		{
			wp_register_script('productPhp', PROSPER_JS . '/productPHP.js', array('jquery', 'json2'), $this->getVersion(), 0);
			wp_enqueue_script( 'productPhp');
		}
		elseif ($this->_options['Set_Theme'] != 'Default')
		{
			$dir = PROSPER_THEME . '/' . $this->_options['Set_Theme'];
			if (file_exists($dir))
			{
				$newTheme = glob($dir . "/*.php");
			}
			else
			{
				$newTheme = glob(PROSPER_VIEW . '/prospersearch/themes/' . $this->_options['Set_Theme'] . "/*.php");
			}

			foreach ($newTheme as $theme)
			{
				if (preg_match('/product.php/i', $theme))
				{
					$phtml[0] = $theme;
				}
				elseif (preg_match('/productPage.php/i', $theme))
				{
					$phtml[1] = $theme;
				}
			}

			if ($this->_options['Set_Theme'] == 'SingleFile')
			{
				wp_register_script('Beta', '', array('jquery', 'json2', 'jquery-ui-widget', 'jquery-ui-dialog', 'jquery-ui-tooltip', 'jquery-ui-autocomplete') );
				wp_enqueue_script( 'Beta' );
				wp_enqueue_style('BetaCSS', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css');
			}
		}

		return $phtml;
	}

	public function prosperPagination($totalAvailable = '', $paged)
	{
		$limit = $this->_options['Pagination_Limit'] ? $this->_options['Pagination_Limit'] : 10;
		$pages = round($totalAvailable / $limit, 0);
		if(empty($paged)) $paged = 1;

		if (is_front_page())
		{
			$pageId  = get_option('prosperent_store_pageId');
			$page 	 = get_post($pageId);
			$baseUrl = $page->post_name;
			$newPage = (is_ssl() ? home_url('/', 'https') : home_url('/', 'http')) . $baseUrl . '/page/';
		}

		if(1 != $pages)
		{
			echo '<nav class="prosperPagination">';
			echo '<ul class="page-numbers">';
			if($paged > 2 && $paged <= $pages) echo '<li><a href="' . (!$newPage ? get_pagenum_link(1) : $newPage . 1) . '"><button><i style="color:inherit" class="fa fa-angle-left"></i> First</button></a></li>';
			if($paged > 1) echo '<li><a href="' . (!$newPage ? get_pagenum_link($paged - 1) : $newPage . ($paged - 1)) . '"><button><i style="color:inherit" class="fa fa-angle-double-left"></i> Previous</button></a></li>';

			for ($i = 1; $i <= $pages; $i++)
			{
				if (1 != $pages && ( !($i >= $paged+3 || $i <= $paged-3)))
				{
					echo ($paged == $i)? '<li><button class="currentPage">' . $i . '</button></li>' : '<li><a href="' . (!isset($newPage) ? get_pagenum_link($i) : $newPage . $i) . '"><button>' . $i . '</button></a></li>';
				}
			}

			if ($paged < $pages) echo '<li><a href="' . (!isset($newPage) ? get_pagenum_link($paged + 1) : $newPage . ($paged + 1)) . '"><button>Next <i style="color:inherit" class="fa fa-angle-right"></i></button></a></li>';
			if ($paged < $pages && $paged < $pages-1) echo '<li><a href="' . (!isset($newPage) ? get_pagenum_link($pages) : $newPage . $pages) . '"><button>Last <i style="color:inherit" class="fa fa-angle-double-right"></i></button></a></li>';
			echo '</ul>';
			echo '</nav>';
		}
	}

	public function searchShortcode($atts, $content = null)
	{
		$options = $this->_options;

		if (!$options['PSAct'])
		{
		    return;
		}

		$pieces = $this->shortCodeExtract($atts, $this->_shortcode);

		if(get_query_var('queryParams'))
		{
			$params = $this->getUrlParams();
			$query = $params['query'];
		}

		$storeId   = get_option('prosperent_store_pageId');
		$page 	   = get_post($storeId);

		$base = $page->post_name;
		$homeUrl = (is_ssl() ? home_url('/', 'https') : home_url('/', 'http'));
		$url = $homeUrl . $base;

		if (!is_page($base))
		{
			$action = $base;
		}

		$queryString = '';
		if ($query = (trim($_POST['q'] ? $_POST['q'] : $options['Starting_Query'])))
		{
			$queryString = '/query/' . strtolower(htmlentities(rawurlencode(str_replace('/', ',SL,', $query))));
		}

		if (is_page($storeId) && isset($_POST['q']))
		{
			$url = $homeUrl . rtrim(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL), '/');
			$newQuery = str_replace(array('/query/' . $query, '/query/' . rawurlencode($query)), '', $url);
			header('Location: ' . $newQuery . $queryString);
			exit;
		}
		elseif (isset($_POST['q']))
		{
			header('Location: ' . $url . $queryString);
			exit;
		}

		ob_start();
		require_once(PROSPER_VIEW . '/prospersearch/searchShort.php');
		$search = ob_get_clean();
		return $search;
	}

	public function ogMeta()
	{
	    $currentId = get_the_ID();
	    $storeId = (int) get_option('prosperent_store_pageId');

	    if ($currentId === $storeId)
	    {
	    	$this->_doStoreApiCalls();
	    	$allData = $this->_cidResults ? $this->_cidResults : $this->_storeResults['results'];
    		$record = $allData['data'];

    		if ($record)
    		{
        		$priceSale = $record[0]['priceSale'] ? $record[0]['priceSale'] : $record[0]['price_sale'];
        		// Open Graph: FaceBook/Pintrest
				$protocol = (is_ssl() ? 'https' : 'http');
				echo '<meta property="og:url" content="' . $protocol . '://' . $_SERVER['HTTP_HOST'] . filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL) . '" />';
        		echo '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />';
        		echo '<meta property="og:type" content="product" />';
        		echo '<meta property="og:image" content="' . preg_replace('/\/\/(.*?)\/images\/[0-9]{3,4}x[0-9]{3,4}/', '//$1/images/600x315', $record[0]['image_url']) . '" />';
        		echo '<meta property="og:image:width" content="600" />';
        		echo '<meta property="og:image:height" content="315" />';
        		echo '<meta property="og:description" content="' . htmlentities($record[0]['description']) . '" />';
        		echo '<meta property="product:availability" content="instock" />';
        		echo '<meta property="product:category" content="' . htmlentities($record[0]['category']) . '" />';
        		echo '<meta property="product:brand" content="' . htmlentities($record[0]['brand']) . '" />';
        		echo '<meta property="product:retailer_title" content="' . htmlentities($record[0]['merchant']) . '" />';
        		echo '<meta property="og:title" content="' . htmlentities(strip_tags($record[0]['keyword'])) . '" />';
        		echo '<meta property="product:price:amount" content="' . $record[0]['price'] . '" />';
                echo '<meta property="product:price:currency" content="USD" />';
                echo $priceSale ? '<meta property="product:sale_price:amount" content="' . $priceSale . '" />' : '';
                echo $priceSale ? '<meta property="product:sale_price:currency" content="USD" />' : '';

        		// Twitter Cards
        		if ($this->_options['Twitter_Site'])
        		{
                    if(!preg_match('/^@/', $this->_options['Twitter_Site']))
                    {
                        $this->_options['Twitter_Site'] = '@' . $this->_options['Twitter_Site'];
                    }
                    if(!preg_match('/^@/', $this->_options['Twitter_Creator']))
                    {
                        $this->_options['Twitter_Creator'] = '@' . $this->_options['Twitter_Creator'];
                    }
            		echo '<meta name="twitter:card" content="summary_large_image">';
            		echo '<meta name="twitter:site" content="' . $this->_options['Twitter_Site'] . '" />';
            		echo '<meta name="twitter:creator" content="' . $this->_options['Twitter_Creator'] . '"/>';
            		echo '<meta name="twitter:image" content="' . preg_replace('/\/\/(.*?)\/images\/[0-9]{3,4}x[0-9]{3,4}/', '//$1/images/512x256', $record[0]['image_url']) . '" />';
            		echo '<meta name="twitter:description" content="' . htmlentities($record[0]['description']) . '" />';
            		echo '<meta name="twitter:title" content="' . htmlentities(strip_tags($record[0]['keyword'])) . '" />';
        		}
    		}
	    }
	}

	public function storeChecker()
	{
		$currentId = get_the_ID();
		$storeId   = get_option('prosperent_store_pageId');
		$page 	   = get_post($storeId);

		if ($currentId == $storeId && !is_front_page())
		{
			return;
		}

		if (($currentId != $storeId || $page->post_name != get_post()->post_name || get_option('prosperent_store_page_name') != get_post()->post_name) && !is_front_page())
		{
		    wp_trash_post($storeId);
		    delete_option("prosperentStoreProductsTitle");
		    delete_option("prosperentStoreProsperent SearchName");
		    delete_option("prosperent_store_page_title");
		    delete_option("prosperent_store_page_name");
		    delete_option("prosperent_store_page_id");
		    delete_option('prosperent_store_pageId');

		    add_option('prosperent_store_page_title', get_post()->post_title);
		    add_option('prosperent_store_page_name', get_post()->post_name);
		    add_option('prosperent_store_pageId', $currentId);

		    $this->prosperReroutes();
		}
		elseif (is_front_page() && $currentId == $storeId)
		{
			delete_option("prosperent_store_page_id");
			delete_option('prosperent_store_pageId');
			delete_option("prosperentStoreProductsTitle");
			delete_option("prosperentStoreProsperent SearchName");
			delete_option("prosperent_store_page_title");
			delete_option("prosperent_store_page_name");

			// the menu entry...
			add_option("prosperent_store_page_title", 'ProsperShop', '', 'yes');
			// the slug...
			add_option("prosperent_store_page_name", 'Prosperent Search', '', 'yes');
			// the id...
			add_option("prosperent_store_pageId", '0', '', 'yes');

			// Create post object
			$proserStore = array(
					'post_title'     => 'ProsperShop',
					'post_content'   => '[prosper_store][/prosper_store]',
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
					'ping_status'    => 'closed'
			);

			// Insert the post into the database
			$pageId = wp_insert_post($proserStore);

			delete_option('prosperent_store_pageId');
			add_option('prosperent_store_pageId', $pageId);

			$this->prosperReroutes();
		}
	}

	public function prosperTitle($title)
	{
		if ( is_feed() )
		{
			return $title;
		}

		$storeId   = get_option('prosperent_store_pageId');
		$fullPage  = get_post($storeId);
		$page 	   = $fullPage->post_name;

		if (is_page($page))
		{
			if (get_query_var('cid'))
			{
				$query = preg_replace('/\(.+\)/i', '', rawurldecode(get_query_var('keyword')));
			}
			elseif (get_query_var('queryParams') || $startQuery = $this->_options['Starting_Query'])
			{
				$params   = $this->getUrlParams();

				$brand    = implode(' or ', array_map('ucwords', explode('~', $params['brand'])));
				$merchant = implode(' or ', array_map('ucwords', explode('~', $params['merchant'])));
				$type 	  = $params['type'];
				$page_num = $params['page'] ? ' Page ' . $params['page'] : '';
				$query 	  = $params['query'] ? $params['query'] : (($startQuery && !$brand && !$merchant) ? $startQuery : '');
			}

			$sep       = ' ' . (!$this->_options['Title_Sep'] ? !$sep ? '-' : trim($sep) : trim($this->_options['Title_Sep'])) . ' ';
			$pagename  = get_the_title();
			$blogname  = get_bloginfo();
			$query 	   = ucwords(str_replace(array(',SL,', '+'), array('/',' ') , $query));

			switch ( $this->_options['Title_Structure'] )
			{
				case '0':
					return;
					break;
				case '1':
					$title =  $blogname . $sep . $pagename . (($query || $brand || $merchant) ? $sep : '') . ($query ? $query : '') . ($query && $brand ? ' - ' : '') . ($brand ? $brand : '') . (($query && $merchant || $merchant && $brand) ? ' From ' : '') . ($merchant ? $merchant : '') . $page_num;
					break;
				case '2':
					$title = ($query ? $query : '') . ($query && $brand ? ' - ' : '') . ($brand ? $brand : '') . (($query && $merchant || $merchant && $brand) ? ' - ' : '') . ($merchant ? $merchant : '') . (($query || $brand || $merchant) ?  $page_num . $sep : '') . $pagename . $sep . $blogname;
					break;
				case '3':
					$title =  ($query ? $query : '') . ($brand ?  ' - ' . $brand : '') . ($merchant ? ' - ' . $merchant : '') . $page_num . $sep . $blogname;
					break;
				case '4':
					$title =  $pagename . $sep . $blogname;
					break;
			}
			return esc_html($title);
		}

		return;
	}

	public function storeSearch($related = false)
	{
		$storeId = get_option('prosperent_store_pageId');
		$page 	 = get_post($storeId);
	    $base    = $page->post_name;

	    $relatedParams = array();
	    if ($related)
	    {
	    	$relatedParams = $this->_params;
	    }

	    if($queryString = str_replace('|', '~', get_query_var('queryParams')))
	    {
	    	$this->_params = $params = $this->getUrlParams();
	    }

	    $view = $params['view'] ? $params['view'] : $this->_options['Product_View'] ;
	    unset($params['view']);

		$homeUrl = is_ssl() ? home_url('/', 'https') : home_url('/', 'http');

		if(is_front_page())
		{
			$url = $homeUrl . $base;
		}
		else
		{
			$url = $homeUrl . $base . '/' . $queryString;
			$url = preg_replace('/\/$/', '', $url);
		}

		$brand    	  = isset($params['brand']) ? str_replace('|', '~', stripslashes($params['brand'])) : '';
		$merchant 	  = isset($params['merchant']) ? str_replace('|', '~', stripslashes($params['merchant'])) : '';
		$category 	  = isset($params['category']) ? str_replace('|', '~', stripslashes($params['category'])) : '';

		return $this->_storeParams = array(
			'filters'	   => array(
				'brand' 	=> $this->getBrands($brand),
				'merchant'  => $this->getMerchants($merchant),
				'category'	=> $this->getCategories($category)
			),
			'params'	   => $params,
			'url'		   => $url,
			'view'		   => $view,
			'base'		   => $base,
			'related'	   => $relatedParams
		);
	}
}
