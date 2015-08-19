<?php
//依分類查商品
function amazon_item_search($arr)
{
	$arr['operation'] = 'ItemSearch';	
	
	switch($arr['search_index'])
	{				
		case 'DVD':
			$arr['browse_node'] = '561958';					
		break;
		
		case 'Books':
			$arr['browse_node'] = '465392';
		break;
		
		case 'adult_anime_DVD':
			$arr['search_index'] = 'DVD';
			//test
			$arr['browse_node'] = '637392,289705011,927712';
			
			//$arr['browse_node'] = '2191254051';					
		break;				
	}	
		
	return amazon_product_api($arr);
}

//查商品詳細資訊
function amazon_item_lookup($arr)
{
	$arr['operation'] = 'ItemLookup';
		
	return amazon_product_api($arr);
}

//遍歷節點
function amazon_browse_node_lookup($browse_node_id)
{
	$arr['operation'] = 'BrowseNodeLookup';		
	$arr['browse_node_id'] = $browse_node_id;	
	
	return amazon_product_api($arr);
}

//使用amazon api
function amazon_product_api($arr)
{
	//========== 設定參數 op ==========
	//AWS Access Key ID		
	$access_key_id = aws_secret_key_id;
	
	//AWS Secret Access Key
	$secret_access_key = aws_secret_access_key;
	
	$parameters['Service'] = 'AWSECommerceService';
	$parameters['Version'] = '2011-08-01';
	$parameters['AssociateTag'] = 'ASSOCIATE TAG';	
	$parameters['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
	$parameters['AWSAccessKeyId'] = $access_key_id;
	$parameters['Operation'] = $arr['operation'];
		
	switch($parameters['Operation'])
	{	
		//依分類查商品
		case 'ItemSearch':					
			$parameters['SearchIndex'] = $arr['search_index'];
			$parameters['BrowseNode'] = $arr['browse_node'];
			$parameters['ItemPage'] = $arr['page'];
			$parameters['ResponseGroup'] = 'Small,Images,OfferFull';
			$parameters['MerchantId'] = 'Amazon';
			
			//搜尋標題
			if($arr['title'] != '')
			{
				$parameters['Title'] = $arr['title'];
			}
			
			//價格下限
			if($arr['minimum_price'] != '')
			{
				$parameters['MinimumPrice'] = $arr['minimum_price'];
			}
			
			//價格上限
			if($arr['maximum_price'] != '')
			{
				$parameters['MaximumPrice'] = $arr['maximum_price'];
			}
			
			//排序
			if($arr['sort'] != '')
			{
				$parameters['Sort'] = $arr['sort'];
			}
		break;
		
		//查商品詳細資訊
		case 'ItemLookup':			
			$parameters['ItemId'] = $arr['asin'];
			$parameters['ResponseGroup'] = 'Small,Images,OfferFull,ItemAttributes';			
		break;
		
		//遍歷節點
		case 'BrowseNodeLookup':
			$parameters['BrowseNodeId'] = $arr['browse_node_id'];
			$parameters['ResponseGroup'] = 'BrowseNodeInfo';
		break;
	}	
	//========== 設定參數 ed ==========
		
	//========== 建立簽名(signature) op ==========	
	//對參數陣列排序
	ksort($parameters);
	
	$canonical_string = '';
	
	foreach ($parameters as $key => $value)
	{	
		$canonical_string .= '&'.str_replace('%7E','~',rawurlencode($key)).'='.str_replace('%7E','~',rawurlencode($value));
	}
	
	//去掉第一個& 符號
	$canonical_string = substr($canonical_string,1);	
	$string_to_sign = "GET\n"."webservices.amazon.co.jp\n"."/onca/xml\n".$canonical_string;
	$signature = base64_encode(hash_hmac('sha256',$string_to_sign,$secret_access_key,TRUE));
	//========== 建立簽名(signature) ed ==========
		
	$res_url = 'http://webservices.amazon.co.jp/onca/xml?'.$canonical_string.'&Signature='.str_replace('%7E','~',rawurlencode($signature));	
	$amazon_xml = simplexml_load_string(@file_get_contents($res_url));
		
	//利用json功能先把物件轉成陣列
	foreach($amazon_xml as $value) 
	{			
		$res_api[] = json_decode(json_encode($value),true);		
	}	
		
	return $res_api;
}

//用正規表示式截出商品介紹
function get_product_description($asin)
{
	$url = 'http://www.amazon.co.jp/gp/product/black-curtain-redirect.html/375-4630128-1534949?ie=UTF8&redirect=true&redirectUrl=%2Fgp%2Fproduct%2F'.$asin;
	$res = @file_get_contents($url);	
	preg_match('/<div class=\"productDescriptionWrapper\">(.*?)<div class=\"emptyClear\">/si',urldecode($res),$res_match);
		
	return $res_match[1];
}
?>