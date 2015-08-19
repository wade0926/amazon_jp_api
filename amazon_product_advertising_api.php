<?php
include_once('../pd/amazon_api.php');

//選功能
$which_api_act = 2;
$item = item_search($which_api_act);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<X-Frame-Options: Deny>
<meta name="title" content="測試頁" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="js/jquery.min.js"></script>
<title>測試頁</title>
</head>
<body>
<?php
//(for test)
if(0)
{
	echo '<pre>';
	//print_r($item);
	//print_r($item[1]['BrowseNode']);
	print_r($item[1]['Item']);
	echo '</pre>';
	exit;
	//var_dump($item[1]['Item'][0]['ItemAttributes']);exit;
}

switch($which_api_act)
{
	//ItemSearch
	case 2:
		?>
        <div style="margin-bottom:20px;">
            ．共
            <span style="color:red">
                <?php echo $item[1]['TotalResults'];?>
            </span>
            &nbsp;項商品
        </div>  
        
        <table>
        <tr>
            <th>項次</th>
            <th>圖片</th>
            <th>ASIN</th>        
            <th>title</th>
            <th>價格</th>
        </tr>   
        <?php
        $i = 1;	
            
        foreach($item[1]['Item'] as $row)
        {		
            ?>
            <tr>
                <td><?php echo $i;?></td>
                <td>
                    <a href="<?php echo $row['DetailPageURL'];?>" target="_blank">
                        <img src="<?php echo $row['MediumImage']['URL'];?>">
                    </a>
                </td>
                <td><?php echo $row['ASIN'];?></td>
                <td>
                    <a href="<?php echo $row['DetailPageURL'];?>" target="_blank">
                        <?php echo $row['ItemAttributes']['Title'];?>
                    </a>
                </td>                
                <td><?php echo '￥ '.$row['Offers']['Offer']['OfferListing']['Price']['Amount'];?></td>			
            </tr>
            <?php
            $i++;
        }
        ?>
        </table>
        <?php
	break;
	
	//ItemLookup
	case 3:
		?>
        1. 商品標題：<?php echo $item[1]['Item']['ItemAttributes']['Title'];?>
        <br />
        2. 商品詳情：
        <br />
        3. 商品圖片：<img src="<?php echo $item[1]['Item']['MediumImage']['URL'];?>">
        <br />
        4. 商品價格：<?php echo $item[1]['Item']['Offers']['Offer']['OfferListing']['Price']['Amount'];?>
        <br />
        5. 商品庫存：<?php echo $item[1]['Item']['Offers']['Offer']['OfferListing']['Availability'];?>
        <br />
        <br />
        ------ 以下暫時不要 ------
        <br />
        ASIN：<?php echo $item[1]['Item']['ASIN'];?>
        <br />
        URL：
        <a href="<?php echo $item[1]['Item']['DetailPageURL'];?>" target="_new">
            Amazon 商品連結
        </a>  
        <br />    
        
        product group：<?php echo $item[1]['Item']['ItemAttributes']['ProductGroup'];?>
        <?php
	break;
}

//========== function area op ==========
function item_search($which_api_act)
{
	//AWS Access Key ID
	$access_key_id = 'AKIAIRPFPHNQRQVJUQWA';
	
	//AWS Secret Access Key
	$secret_access_key = secret_access_key;
	
	//設定參數
	$parameters['Service'] = 'AWSECommerceService';
	$parameters['Version'] = '2011-08-01';
	$parameters['AssociateTag'] = 'test_tag';
		
	//選功能
	switch($which_api_act)
	{
		case 1:
			$parameters['Operation'] = 'BrowseNodeLookup';		
			$parameters['BrowseNodeId'] = '2191254051';			
			$parameters['ResponseGroup'] = 'BrowseNodeInfo';
		break;
		
		case 2:		
			$parameters['Operation'] = 'ItemSearch';			
			$parameters['SearchIndex'] = 'DVD';
			$parameters['BrowseNode'] = '2191254051';		
			//$parameters['Keywords'] = '巨乳';
			
			//--- ResponseGroup ---
			//$parameters['ResponseGroup'] = 'Small,Images,Offers,OfferFull,OfferSummary';
			//$parameters['ResponseGroup'] = 'Offers,OfferFull,OfferSummary';
			$parameters['ResponseGroup'] = 'Large';
			
			//--- Condition ---			
			//$parameters['Condition'] = 'All';
			//$parameters['Condition'] = 'Used';
			
			//$parameters['ItemPage'] = 2;
			//$parameters['MinimumPrice'] = '3000';
			//$parameters['MaximumPrice'] = '4000';
			$parameters['MerchantId'] = 'Amazon';
						
			//--- Sort ---
			$parameters['Sort'] = 'price';
			
			//--- 其它 ---
			//$parameters['Availability'] = 'Available';
			//$parameters['IncludeReviewsSummary'] = 'true';
			
		break;
		
		case 3:
			$parameters['Operation'] = 'ItemLookup';
			//$parameters['ItemId'] = 'B00F28BU40';			
			//$parameters['ItemId'] = 'B00F28BU40';
			//$parameters['ItemId'] = '059035342X';		
			$parameters['ItemId'] = 'B00WQM3TIK';			
			//$parameters['ResponseGroup'] = 'Small,Images,OfferFull';
			$parameters['ResponseGroup'] = 'ItemAttributes';
		break;
	}	
			
	$parameters['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
	$parameters['AWSAccessKeyId'] = $access_key_id;
	
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
	$res_url = 'http://webservices.amazon.co.jp/onca/xml?'.$canonical_string.'&Signature='.str_replace('%7E','~',rawurlencode($signature));	
	 
	//(for test)   
	//echo $res_url;exit;
	
	$amazon_xml = simplexml_load_string(@file_get_contents($res_url));
		
	//利用json功能先把物件轉成陣列
	foreach($amazon_xml as $value) 
	{			
		$res_api[] = json_decode(json_encode($value),true);		
	}	
	
	return $res_api;
}
//========== function area ed ==========
?>

</body>
</html>   