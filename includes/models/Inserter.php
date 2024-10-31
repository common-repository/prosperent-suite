<?php
require_once(PROSPER_MODEL . '/Base.php');
/**
 * Search Model
 *
 * @package Model
 */
class Model_Inserter extends Model_Base
{
	protected $_shortcode = 'compare';
	protected $_insertArray = array();
	protected $_insertCount = 0;

	public $_options;

	public function __construct()
	{
		$this->_options = $this->getOptions();
	}

	public function qTagsInsert()
	{
		$id 	 = 'productInsert';
		$display = 'ProsperInsert';
		$arg1 	 = '[prosperInsert q="QUERY" b="BRAND" m="MERCHANT" l="LIMIT" v="GRID, LIST OR PC for price comparison"]';
		$arg2 	 = '[/prosperInsert]';

		$this->qTagsProsper($id, $display, $arg1, $arg2);
	}

	public function newQueries($atts, $content = null)
	{
		return;
	}

	public function contentInserter($text)
	{
		$currentId = get_the_ID();
		$storeId = (int) get_option('prosperent_store_pageId');

	    if (!$this->_options['PICIAct'] || ($this->_options['PSAct'] && $currentId === $storeId))
	    {
	        return $text;
	    }

	    if (($this->_options['prosper_inserter_posts'] && is_singular('post')) || ($this->_options['prosper_inserter_pages'] && (is_page() || (is_plugin_active('woocommerce/woocommerce.php') ? is_product() : ''))))
	    {
	        if (preg_match('/\[prosperNewQuery (.+)\]/i', $text, $regs) || preg_match('/\[contentInsert (.+)\](.*)?\[\/contentInsert\]/i', $text, $regs))
    		{
    			if (preg_match('/noShow="on"/', $regs[1]))
    			{
    			    return trim($text);
    			}

    			$insert = '<p>[prosperInsert ' . $regs[1] . '][/prosperInsert]</p>';
    		}
	        else
	        {
        		$newTitle = get_the_title();

        		if ($this->_options['prosper_inserter_negTitles'])
        		{
        			if(function_exists('prosper_negatives') === false)
        			{
        				function prosper_negatives($negative)
        				{
        					return '/\b' . trim($negative) . '\b/i';
        				}
        			}

        			$exclude = array_map(
        				"prosper_negatives",
        				explode(',', $this->_options['prosper_inserter_negTitles'])
        			);

        			$newTitle = preg_replace($exclude, '', $newTitle);
        		}

        		if (!$newTitle)
        		{
        			return trim($text);
        		}

        		if ($this->_options['contentAnalyzer'])
        		{
        			$settings = array(
        				'url' => 'http://' . $_SERVER['HTTP_HOST'] . filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL)
        			);

        			$url = $this->apiCall($settings, 'fetchAnalyzer');
        			$allData = $this->singleCurlCall($url, 86400, $settings);

        			foreach ($allData['data'] as $newKeyword)
        			{
        				$newKeywords[] = $newKeyword['phrase'];
        			}
        		}

        		$insert = '<p>[prosperInsert imgt="' . ($this->_options['prosper_imageType'] ? $this->_options['prosper_imageType'] : 'original') . '" q="' . $newTitle . '" l="' . ($this->_options['PI_Limit'] ? $this->_options['PI_Limit'] : 1) . '" vst="' . ($this->_options['CIVisitStoreButton'] ? $this->_options['CIVisitStoreButton'] : 'Visit Store') . '" v="' . ($this->_options['prosper_insertView'] ? $this->_options['prosper_insertView'] : 'list') . '" gtm="' . ($this->_options['Link_to_Merc'] ? 1 : 0) . '"][/prosperInsert]</p>';
	        }

    		if ('top' == $this->_options['prosper_inserter'])
    		{
    			$content = $insert . $text;
    		}
    		else
    		{
    			$content = $text . $insert;
    		}

    		$text = $content;
	    }

		return trim($text);
	}

	public function inserterCount ($text)
	{
		$this->_insertCount = substr_count($text, '[/compare]') + substr_count($text, '[/prosperInsert]');
		return $text;
	}

	public function inserterShortcode($atts)
	{
		if (!$this->_options['PICIAct'])
		{
			return;
		}

		$pieces = $this->shortCodeExtract($atts, $this->_shortcode);
		$pieces = array_filter($pieces);

		wp_register_script('productInsert', PROSPER_JS . '/productInsert.js', array('jquery', 'json2'), $this->getVersion(), 0);
		wp_enqueue_script( 'productInsert');

		$pageId   = get_option('prosperent_store_pageId');
		$page 	  = get_post($pageId);

		$target     = $this->_options['Target'] ? '_blank' : '_self';
		$base 	    = $page->post_name;
		$homeUrl    = is_ssl() ? home_url('', 'https') : home_url('', 'http');
		$currentUrl = $homeUrl . ltrim(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL), '/');
		$page       = get_page_by_path($base);

		$curlUrls = $id = $settings = array();

		$fetch = $pieces['ft'];

		if (strpos($pieces['id'], '~'))
		{
			$id = explode('~', rtrim($pieces['id'], '~'));
		}

		$limit = 1;
		if ($pieces['cl'] && $pieces['cl'] > $pieces['l'])
		{
			$limit = $pieces['cl'];
		}
		elseif ($pieces['l'] > 1)
		{
			$limit = $pieces['l'];
		}
		elseif ($id)
		{
			$limit = count($id);
		}

		if ($fetch === 'fetchProducts')
		{
			$currency = 'USD';
			$recordId 	= 'catalogId';
			$mainQuery 		  = $pieces['q'] ? trim(strip_tags($pieces['q'])) : '';
			$merchantFilter   = $pieces['m'] ? str_replace(',', '|', $pieces['m']) : '';
			$merchantIdFilter = $pieces['mid'] ? str_replace(',', '|', $pieces['mid']) : '';
			$brandFilter	  = $pieces['b'] ? str_replace(',', '|', $pieces['b']) : '';
			$priceSaleFilter  = $pieces['sale'] ? ($pieces['pr'] ? $pieces['pr'] : '0.01,') : '';
			$priceFilter 	  = $pieces['sale'] ? '' : ($pieces['pr'] ? $pieces['pr'] : '');

			if ($pieces['v'] == 'pc')
			{
				$idFilter = array();
				if (strlen($pieces['id']) == 32 && strpos($pieces['id'], ' '))
				{
					$idFilter = array('filterProductId' => $pieces['id']);
				}
				elseif ($pieces['id'])
				{
					$idFilter = array('query' => rtrim(str_replace('_', ' ', $pieces['id']), '~'));
				}

				$settings = array(
					'curlCall'		     => 'single-productPC',
					'query'              => (!$pieces['id'] ? $mainQuery : ''),
					'imageSize'		     => '250x250',
					'groupBy'            => 'merchant',
					'limit'              => 5,
					'v'			  		 => $pieces['v'] ? $pieces['v'] : 'list'
				);

				$curlUrls[0] = $this->apiCall(array_merge($settings, $idFilter), $fetch);
			}
			elseif (count($id) && $pieces['ni'])
			{
				$settings = array(
					'curlCall'		=> 'single-product',
					'interface'		=> 'insert',
					'imageSize'		=> '250x250',
					'limit'     	=> $limit,
					'filterKeyword' => str_replace('_', ' ', implode('|', $id))
				);

				$curlUrls[0] = $this->apiCall($settings, $fetch);
			}
			elseif (count($id))
			{
				foreach ($id as $i => $apart)
				{
					$settings[$i] = array(
							'curlCall'	=> 'single-product',
							'interface'	=> 'insert',
							'imageSize'	=> '250x250',
							'limit'     => 1,
							'query'     => html_entity_decode(preg_replace('/\s+/', ' ', str_replace('_', ' ', $apart))),
							'relevancyThreshold' => 0.85
					);

					$curlUrls[$i] = $this->apiCall($settings[$i], $fetch);
				}

				$settings = array(
						'curlCall'		  => 'single-product',
						'interface'		  => 'insert',
						'imageSize'		  => '250x250',
						'limit'           => $limit,
						'query'           => $mainQuery,
						'filterMerchant'  => $merchantFilter,
						'filterBrand'	  => $brandFilter,
						'filterPriceSale' => $priceSaleFilter,
						'filterPrice' 	  => $priceFilter,
						'filterKeyword'   => implode('|', $id[$ni]),
						'v'			   	  => $pieces['v'] ? $pieces['v'] : 'list'
				);
			}
			else
			{
				$settings = array(
					'curlCall'		   => 'single-product',
					'interface'		   => 'insert',
					'imageSize'		   => '250x250',
					'limit'            => $limit,
					'query'            => $mainQuery,
					'filterMerchant'   => $merchantFilter,
					'filterMerchantId' => $merchantIdFilter,
					'filterBrand'	   => $brandFilter,
					'filterPriceSale'  => $priceSaleFilter,
					'filterPrice' 	   => $priceFilter,
					'v'				   => $pieces['v'] ? $pieces['v'] : 'list'
				);

				$curlUrls[0] = $this->apiCall($settings, $fetch);
			}
		}
		elseif ($fetch === 'fetchMerchant')
		{
			$filterType = 'MerchantId';
			$recordId 	 = 'merchantId';
			if (!$id)
			{
				$id = explode(',', rtrim($pieces['id'], ','));
			}

			$settings = array(
				'curlCall'		   => 'single-merchant',
				'imageSize'		   => '120x60',
				'interface'		   => 'insert',
				'limit'            => $limit,
				'filterMerchant'   => (!$id ? str_replace(',', '|', $pieces['m']) : ''),
				'filterMerchantId' => $id,
				'filterCategory'   => !$id && $pieces['cat'] ? '*' . $pieces['cat'] . '*' : '',
				'imageType'		   => $pieces['imgt'] ? $pieces['imgt'] : 'original'
			);

			$curlUrls[0] = $this->apiCall($settings, $fetch);
		}

		$allData = $this->multiCurlCall($curlUrls, PROSPER_CACHE_PRODS, $settings);

		$prodSubmit = (is_ssl() ? home_url('/', 'https') : home_url('/', 'http')) . $base;

		$insertProd = PROSPER_VIEW . '/prosperinsert/insertProd.php';

		// Inserter PHTML file
		if ($this->_options['Set_Theme'] != 'Default' && $this->_options['Set_Theme'])
		{
			$dir = PROSPER_THEME . '/' . $this->_options['Set_Theme'];
			if($newTheme = glob($dir . "/*.php"))
			{
				foreach ($newTheme as $theme)
				{
					if (preg_match('/insertProd.php/i', $theme))
					{
						$insertProd = $theme;
					}
				}
			}
		}

		$everything = array();
		if ($pieces['v'] == 'pc' || (count($allData) == 1))
		{

			$everything = $allData[0];
		}
		else
		{
			foreach ($allData as $i => $record)
			{
				if ($record['errors'])
				{
					continue;
				}

				if ($record['data'])
				{
					$everything['data'][$i] = $record['data'][0];
				}
			}
		}

		$results = $everything['data'];

		ob_start();
		require($insertProd);
		$insert = ob_get_clean();
		return $insert;
	}
}