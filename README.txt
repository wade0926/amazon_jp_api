1. 技術文件：
 (1) Amazon Product Advertising API(入門Guide)：
     http://docs.aws.amazon.com/AWSECommerceService/latest/GSG/Welcome.html
 (2) Amazon Product Advertising API(Developer Guide)：
     http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_OperationListAlphabetical.html

2. 參考資料：
 (1) Forum：
     https://forums.aws.amazon.com/forum.jspa?forumID=9
 (2) Amazon.co.jpのBrowse Node - スピリッツオブゼロ@blog：
     http://park8.wakwak.com/~da101/nikky/archives/000057.html


1. Blended Searches(search index)：
   http://docs.aws.amazon.com/zh_cn/AWSECommerceService/latest/DG/BlendedSearches.html
2. Locale Information for the JP Marketplace：
   http://docs.aws.amazon.com/zh_cn/AWSECommerceService/latest/DG/LocaleJP.html
3. 找出規格的node id，如：
   http://www.amazon.co.jp/s/ref=sr_nr_p_n_binding_browse-b_0?fst=as:off&rh=n:561958,n:!562002,n:896246,p_n_binding_browse-bin:622812011|644356011&bbn=896246&sort=relevancerank&ie=UTF8&qid=1440129561&rnid=622811011
   經測試後得知'p_n_binding_browse-bin:' 後面接的是規格的node id，得出622812011 是藍光規格
4. 出現這種'feature 的node 會比較準，如：
   http://www.amazon.co.jp/s/ref=amb_link_82662236_22?ie=UTF8&rh=n:896246,p_n_feature_seven_browse-bin:2191254051&pf_rd_m=AN1VRQENFRJN5&pf_rd_s=merchandised-search-leftnav&pf_rd_r=1HAHMYHNYCY9GPDQP1HF&pf_rd_t=101&pf_rd_p=215295889&pf_rd_i=896246
5. 測試用連結：
 (1) item search：
     http://127.0.0.1/github/amazon_jp_api/amazon_product_advertising_api.php?act=item_search&search_index=for_test&page=1&asin=
 (2) item lookup：
     http://127.0.0.1/github/amazon_jp_api/amazon_product_advertising_api.php?act=item_lookup&search_index=&page=1&asin=B00YZ7528O
 (3) browse node lookup：
     http://127.0.0.1/github/amazon_jp_api/amazon_product_advertising_api.php?act=browse_node_lookup&browse_node_id=2189356051
