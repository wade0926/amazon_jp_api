<?php
require_once('../pd/amazon_api.php');
require_once('func/api_amazon.php');

$act = $_GET['act'];
$search_index = $_GET['search_index'];
$asin = $_GET['asin'];
$page = $_GET['page'];

if($act != '')
{
	$arr_api['act'] = $act;		
	
	switch($arr_api['act'])
	{
		case 'item_search':
			$arr_api['search_index'] = $search_index;
			$arr_api['page'] = $page;
			//$arr_api['title'] = '学園';
			//$arr_api['minimum_price'] = '4000';
			//$arr_api['maximum_price'] = '5000';
			$arr_api['sort'] = 'price';
			$item = amazon_item_search($arr_api);
		break;
		
		case 'item_lookup':
			$arr_api['asin'] = $asin;		
			$item = amazon_item_lookup($arr_api);
		break;		
	}		
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<title>Amazon API測試頁</title>
</head>
<body style="margin-left:30px;">
<div style="margin:20px">
	<form method="get" onsubmit="return check_form();">
    	<span>
            請選擇功能：
            <select name="act" id="act" onchange="act_change();">
                <option value="">請選擇</option>
                <option value="item_search">查分類樹下商品</option>
                <option value="item_lookup">取得商品詳細資訊</option>
            </select>
        </span>
        
        <span id="span_search_index" style="margin-left:20px;">
            選擇分類樹：
            <select name="search_index" id="search_index">
                <option value="">請選擇</option>
                <option value="DVD">DVD</option>
                <option value="Books">Books</option>
                <option value="adult_anime_DVD">成人動漫DVD</option>
            </select>
        </span>
        
        <span id="span_page" style="margin-left:20px;">
        	頁數：
            <select name="page" style="margin-bottom:20px;">
                <?php
                for($i = 1;$i <= 10;$i++)
                {
                    ?>
                    <option value="<?php echo $i;?>"><?php echo $i;?></option>
                    <?php
                }
                ?>            
            </select>  
        </span>
        
        <span id="span_asin" style="margin-left:20px;">
            ASIN：
            <input name="asin" id="asin" />
        </span>        
        
        <span id="btn" style="margin-left:20px;">
        	<button type="submit">送出</button>
        </span>
    </form>
</div>

<?php
if($act != '')
{
	//依分類查商品
	if($act == 'item_search')
	{
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
					<td><?php echo $row['Offers']['Offer']['OfferListing']['Price']['Amount'].' ￥';?></td>			
				</tr>
				<?php
				$i++;
			}
			?>
		</table>
		<?php
	}
	//查商品詳細資訊
	elseif($act == 'item_lookup')
	{	
		?>
        1. 商品標題：<?php echo $item[1]['Item']['ItemAttributes']['Title'];?>
        <br />
        2. 商品詳情：
        <div style="margin-left:15px;font-weight:bold">        	
        	<?php
            if($item[1]['Item']['ItemAttributes']['Actor'] != '')
			{
				?>
                ．出演：
                <?php
				if(is_array($item[1]['Item']['ItemAttributes']['Actor']) == true)
				{
					foreach($item[1]['Item']['ItemAttributes']['Actor'] as $row)
					{
						$item_detail_actor .= $row.',';				
					}
					
					echo substr($item_detail_actor,0,-1);
				}
				else
				{
					echo $item[1]['Item']['ItemAttributes']['Actor'];
				}				
				?>
                <br />
                <?php
			}
			
			if($item[1]['Item']['ItemAttributes']['Actor'] != '')
			{
				?>
                ．監督:
                <?php
				if(is_array($item[1]['Item']['ItemAttributes']['Director']) == true)
				{
					foreach($item[1]['Item']['ItemAttributes']['Director'] as $row)
					{
						$item_detail_director .= $row.',';				
					}
					
					echo substr($item_detail_director,0,-1);
				}
				else
				{
					echo $item[1]['Item']['ItemAttributes']['Director'];
				}
				?>
                <br />
                <?php
			}
			?>
            ．DVD區域碼: <?php echo $item[1]['Item']['ItemAttributes']['RegionCode'];?>
            <br />
            ．光碟數量： <?php echo $item[1]['Item']['ItemAttributes']['NumberOfDiscs'];?>
            <br />
            ．發布日期： <?php echo $item[1]['Item']['ItemAttributes']['ReleaseDate'];?>
            <br />
            ．片長： <?php echo $item[1]['Item']['ItemAttributes']['RunningTime']. '分';?>
            <br />
            ．內容介紹：
            <div style="margin-left:15px;color:#F30">
				<?php
                //用正規表示式截出商品介紹
                echo get_product_description($item[1]['Item']['ASIN']);
                ?>
            </div>
        </div>             
        3. 商品圖片：        
        <div style="margin-left:15px;">
            <a href="<?php echo $item[1]['Item']['DetailPageURL'];?>" target="_blank">
                <img src="<?php echo $item[1]['Item']['LargeImage']['URL'];?>">
            </a>
        </div>        
        4. 商品價格：<?php echo $item[1]['Item']['Offers']['Offer']['OfferListing']['Price']['Amount'].' ￥';?>
        <br />
        5. 商品庫存：<?php echo $item[1]['Item']['Offers']['Offer']['OfferListing']['Availability'];?>        
		<?php
	}
}
?>
</body>
</html>

<script>
$(function()
{
	hide_default()
});

function hide_default()
{
	$('#span_search_index').hide();
	$('#span_asin').hide();
	$('#span_page').hide();
	$('#btn').hide();
}

function act_change()
{
	hide_default();
	
	if($('#act').val() == 'item_search')
	{
		$('#span_search_index').show();
		$('#span_page').show();
	}
	
	if($('#act').val() == 'item_lookup')
	{
		$('#span_asin').show();
	}
	
	if($('#act').val() != '')
	{
		$('#btn').show();
	}
}

function check_form()
{
	if($('#act').val() == 'item_search')
	{
		if($('#search_index').val() == '')
		{
			alert('分類樹未選');
			return false;
		}
	}
	
	if($('#act').val() == 'item_lookup')
	{
		if($('#asin').val() == '')
		{
			alert('ASIN 未填');
			return false;
		}
	}
}
</script>   