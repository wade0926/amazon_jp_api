<?php
//依分類查商品
function amazon_item_search($arr)
{
	$arr['operation'] = 'ItemSearch';	
		
	switch($arr['search_index'])
	{				
		case 'DVD':			
			//$arr['browse_node'] = '2189356051';
			$arr['browse_node'] = '561958';
			//$arr['keywords'] = 'アダルト';
			//					
		break;
		
		case 'Books':
			$arr['browse_node'] = '10667121';
			//$arr['browse_node'] = '465392';
		break;
		
		case 'adult_anime_DVD':
			$arr['search_index'] = 'DVD';			
			$arr['browse_node'] = '2191254051';					
		break;
		
		//test
		case 'for_test':		
			$arr['search_index'] = 'Software';
			$arr['keywords'] = 'アダルト';
			$arr['browse_node'] = '637392,16245011';
		break;
	}	
		
	return amazon_product_api($arr);
}

//查商品詳細資訊
function amazon_item_lookup($arr)
{
	$arr['operation'] = 'ItemLookup';
	
	//for test
	//$arr['operation'];
		
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
			$parameters['ResponseGroup'] = 'Small,Images,OfferFull,BrowseNodes';
			$parameters['MerchantId'] = 'Amazon';
			
			//搜尋標題
			if($arr['title'] != '')
			{
				$parameters['Title'] = $arr['title'];
			}
			
			//搜尋標題
			if($arr['keywords'] != '')
			{
				$parameters['Keywords'] = $arr['keywords'];
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
			
			//brand
			if($arr['brand'] != '')
			{
				$parameters['Brand'] = $arr['brand'];
			}								
		break;
		
		//查商品詳細資訊
		case 'ItemLookup':			
			$parameters['ItemId'] = $arr['asin'];
			$parameters['ResponseGroup'] = 'Small,Images,OfferFull,ItemAttributes,BrowseNodes';			
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
	echo $res_url;
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
	
	//取得內容介紹	
	preg_match('/<div id=\"productDescription\" class=\"a-section a-spacing-small\">(.*?)<\/div>/si',urldecode($res),$res_match);
	$goods_detail = $res_match[1];
	
	//刪掉文字：内容紹介
	$goods_detail = str_replace('<h3>&#20869;&#23481;&#32057;&#20171;</h3>','',$goods_detail);
	
	//刪掉文字：商品紹介
	$goods_detail = str_replace('<h3>商品紹介</h3>','',$goods_detail);
	
	//刪掉連結：商品の説明をすべて表示する	
	preg_match('/<a class=\"a-link-normal\" href=\".*?\">.*?商品の説明をすべて表示する<\/a>/si',$goods_detail,$res_match);
	$goods_detail = str_replace($res_match[0],'',$goods_detail);
				
	return $goods_detail;
}
?>