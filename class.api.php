<?php

class artappcr_api{

  public function __construct(){
    if(!empty($_REQUEST['Module_ID'])){
      if(!empty($_REQUEST['Action_ID'])){
          if($_REQUEST['Module_ID'] == 1) $method = array(2 => 'list_news',6 => 'display_news',3 => 'cat_news',5 => 'home_news');
          elseif ($_REQUEST['Module_ID'] == 3) $method = array(2 => 'list_videos',6 => 'display_video',3 => 'cat_videos');
          elseif ($_REQUEST['Module_ID'] == 4) $method = array(2 => 'list_images',6 => 'display_image',3 => 'cat_images');
          elseif ($_REQUEST['Module_ID'] == 6) $method = array(2 => 'list_pages',6 => 'display_page');
          elseif ($_REQUEST['Module_ID'] == 62) $method = array(27 => 'display_contact');
          elseif ($_REQUEST['Module_ID'] == 8) $method = array(14 => 'display_page_plugins');
          elseif($_REQUEST['Module_ID'] == 5) $method = array(2 => 'list_products',6 => 'display_product',3 => 'cat_products');
          $method = $method[$_REQUEST['Action_ID']];
          if(method_exists($this, $method)){
            $this->$method();
          }
          else{
            $this->output('Invaild Action ID value');
          }
        }
        else{
          $this->output('Invaild Action ID value');
        }
      }
    else{
        $this->output('Invaild Module ID value');
      }
  }
  
  public function home_news(){
    $this->list_news('News_Home');
  }

  public function display_page_plugins(){
    $module_plugins = array('contact-form-7', 'nextgen-gallery', 'woocommerce', 'contus-video-gallery');
    global $wpdb;
    $tbfx = $wpdb->base_prefix;
    $json['Pages_Plugins'] = array();
    $json['Pages_List'] = array();
    if ( ! function_exists( 'get_plugins' ) ) require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugins = get_plugins();
    $sql = "SELECT * FROM ".$tbfx."posts WHERE post_type='page' AND post_status='publish' ORDER BY ID DESC";
    $pages = $wpdb->get_results($sql, 'ARRAY_A');

    $result = array();
    foreach($pages as $page){
        if(($page['post_content'] != do_shortcode($page['post_content'])) || empty($page['post_content'])) continue;
        $result['ID'] = (string) $page['ID'];
        $result['Title'] = $page['post_title'];
        $result['Action_ID'] = '26';
        $result['Content'] = do_shortcode($page['post_content']);
        $result['Title_Cat'] = '';
        $result['Dept_ID'] = '0';
        $imgresize = get_option('thumbnail_size_h').'x'.get_option('thumbnail_size_w');
        $image = get_the_post_thumbnail($page['ID'], 'full');
        if(!empty($image)){
          preg_match_all('/src="([^"]*)"/i', $image, $src);
          $ext = strtolower(substr($src[1][0], strrpos($src[1][0], '.')+1));
          $result['Img'] = str_replace('.'.$ext, '', $src[1][0]).'-'.$imgresize.'.'.$ext;
        }
        else{ $result['Img'] = ''; }
        $result['Link_Share'] = $page['guid'];
        $json['Pages_List'][] = $result;
      }

    $temp = array();
    foreach ($plugins as $key => $value) {
      $names = explode('/', $key);
      if(!in_array($names[0], $module_plugins)) continue;
      $temp['Name'] = $names[0];
      $temp['Title'] = $value['Title'];
      $temp['Pages']= array();
      foreach ($pages as $key1 => $value1) {
        if (has_shortcode($value1['post_content'],'contact-form-7') && $temp['Name'] == 'contact-form-7') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }
        elseif (has_shortcode($value1['post_content'],'nggallery') && $temp['Name'] == 'nextgen-gallery') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }
        elseif (has_shortcode($value1['post_content'],'video') && $temp['Name'] == 'contus-video-gallery') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }
        elseif (has_shortcode($value1['post_content'],'videomore') && $temp['Name'] == 'contus-video-gallery') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }
        elseif (has_shortcode($value1['post_content'],'videohome') && $temp['Name'] == 'contus-video-gallery') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }
        elseif (has_shortcode($value1['post_content'],'woocommerce_cart') && $temp['Name'] == 'woocommerce') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }
        elseif (has_shortcode($value1['post_content'],'woocommerce_order_tracking') && $temp['Name'] == 'woocommerce') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }
        elseif (has_shortcode($value1['post_content'],'woocommerce_checkout') && $temp['Name'] == 'woocommerce') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }
        elseif (has_shortcode($value1['post_content'],'woocommerce_my_account') && $temp['Name'] == 'woocommerce') {
          $temp['Pages'][] = array('ID' => $value1['ID'], 'Title' => $value1['post_title'], 'Link' => $value1['guid']);
        }

      }
      $json['Pages_Plugins'][] = $temp;
    }
    $this->output($json);
  }

  public function display_contact(){
    global $wpdb;
    $tbfx = $wpdb->base_prefix;
    $json['Email'] = get_bloginfo('admin_email');
    $json['Contact_Form'] = array();
    $sql = "SELECT * FROM ".$tbfx."cntctfrm_field";
    $gets = $wpdb->get_results($sql, 'ARRAY_A');
    $temp = array();
    foreach ($gets as $key => $value) {
      $temp[] = array('ID' => $value['id'], 'Title' => $value['name']);
    }
    $json['Contact_Form'] = $temp;
    $this->output($json);
  }
  
  public function cat_news(){
    $json['News_Depts'] = array();
    $parent = (empty($_REQUEST['havesub']))?0:$_REQUEST['havesub'];
    $args = array(
    'parent' => $parent,
    'taxonomy' => 'category'
    );
    $categories = get_categories($args);
    if(!empty($categories)){
      foreach($categories as $category){
        $result['ID'] = $category->cat_ID;
        $result['Title'] = $category->name;
        $result['Des'] = $category->description;
        $result['Visit_Num'] = '0';
        if(function_exists('z_taxonomy_image_url')){
          $cimage = z_taxonomy_image_url($category->cat_ID, true);
        }
        else{
          $cimage = '';
        }
        $result['Pic'] = (empty($cimage))?'':$cimage;
        $children = get_term_children($category->cat_ID, 'category');
        if(empty($children)){
          $Havesub = '0';
        }
        else{
          $Havesub = '1';
        }
        $result['Havesub'] = $Havesub;
        $result['Link_Share'] = get_category_link($category->cat_ID);
        $json['News_Depts'][] = $result;
      }
    }
    $this->output($json);
  }

  public function cat_products(){
    $json['Products_Depts'] = array();
    $parent = (empty($_REQUEST['havesub']))?0:$_REQUEST['havesub'];
    $args = array(
    'parent' => $parent,
    'taxonomy' => 'product_cat',
    'hide_empty' => 0
    );
    $categories = get_categories($args);
    if(!empty($categories)){
      foreach($categories as $category){
        $result['ID'] = $category->cat_ID;
        $result['Title'] = $category->name;
        $result['Des'] = $category->description;
        $result['Visit_Num'] = '0';
        if(function_exists('z_taxonomy_image_url')){
          $cimage = z_taxonomy_image_url($category->cat_ID, true);
        }
        else{
          $cimage = '';
        }
        $result['Pic'] = (empty($cimage))?'':$cimage;
        $children = get_term_children($category->cat_ID, 'category');
        if(empty($children)){
          $Havesub = '0';
        }
        else{
          $Havesub = '1';
        }
        $result['Havesub'] = $Havesub;
        $result['Link_Share'] = get_category_link($category->cat_ID);
        $json['Products_Depts'][] = $result;
      }
    }
    $this->output($json);
  }

  public function cat_videos(){
    global $wpdb;
    $tbfx = $wpdb->base_prefix;
    $json['Videos_Depts'] = array();
    $sql = "SELECT * FROM ".$tbfx."hdflvvideoshare_playlist WHERE is_publish=1 ORDER BY pid DESC";
    $gets = $wpdb->get_results($sql, 'ARRAY_A');
    $lists = array();
    $temp = array();
    foreach($gets as $row){
      $temp['ID'] = $row['pid'];
      $temp['Title'] = $row['playlist_name'];
      $temp['Visit_Num'] = '1';
      $temp['DateTime'] = "1442154480";
      $q = "SELECT image FROM ".$tbfx."hdflvvideoshare WHERE vid IN (SELECT media_id FROM ".$tbfx."hdflvvideoshare_med2play WHERE playlist_id = ".$row['pid'].")";
      $pics = $wpdb->get_row($q, 'ARRAY_A');
      if (!empty($pics)) $temp['Pic'] = $pics['image'];
      else $temp['Pic'] = '';
      $temp['Des'] = $row['playlist_desc'];
      $temp['Havesub'] = '0';
      $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
      $temp['Link_Share'] = 'http://' . $_SERVER['HTTP_HOST'] . $uri_parts[0].$temp['Title'];
      $lists[]=$temp;
    }
    $json['Videos_Depts'] = $lists;
    $this->output($json);
  }

  public function cat_images(){
    global $wpdb;
    $tbfx = $wpdb->base_prefix;
    $json['Gallery_Depts'] = array();
    $sql = "SELECT ".$tbfx."ngg_gallery.*,filename FROM ".$tbfx."ngg_gallery LEFT JOIN ".$tbfx."ngg_pictures ON pid=previewpic ORDER BY gid DESC";
    $gets = $wpdb->get_results($sql, 'ARRAY_A');
    $lists = array();
    $temp = array();
    foreach($gets as $row){
      $temp['ID'] = $row['gid'];
      $temp['Title'] = $row['title'];
      $temp['Visit_Num'] = '1';
      $temp['DateTime'] = "1442154480";
      $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
      if (!empty($row['filename'])) $temp['Pic'] = 'http://'.$_SERVER['HTTP_HOST'].$uri_parts[0].$row['path'].'/'.$row['filename'];
      else $temp['Pic'] = 'http://'.$_SERVER['HTTP_HOST'].$uri_parts[0].'wp-admin/images/wordpress-logo.png';
      $temp['Des'] = '';
      $temp['Havesub'] = '0';
      $temp['Link_Share'] = 'http://' . $_SERVER['HTTP_HOST'] . $uri_parts[0].$temp['Title'];
      $lists[]=$temp;
    }
    $json['Gallery_Depts'] = $lists;
    $this->output($json);
  }
  
  public function display_news(){
    global $wpdb;
    $table = $wpdb->prefix.'posts';
    $sql = $this->posts_query("AND $table.ID='$_REQUEST[ID]'");
    $get = $wpdb->get_row($sql, 'ARRAY_A');
    if(!$get){
      $json['News_Display'] = array();
      return $this->output('');
    }
    $imgresize = get_option('thumbnail_size_h').'x'.get_option('thumbnail_size_w');
    $image = get_the_post_thumbnail($get['ID'], 'full');
    if(!empty($image)){
      preg_match_all('/src="([^"]*)"/i', $image, $src);
      $get['featuredimage'] = $src[1][0];
      $ext = strtolower(substr($src[1][0], strrpos($src[1][0], '.')+1));
      $get['featuredthumb'] = str_replace('.'.$ext, '', $src[1][0]).'-'.$imgresize.'.'.$ext;
    }
    else{
      $get['featuredimage'] = '';
      $get['featuredthumb'] = '';
    }
    $video = array();
    preg_match_all('#(?:httpv://)?(?:www\.)?(?:youtube\.com/(?:v/|watch\?v=)|youtu\.be/)([\w-]+)(?:\S+)?#', $get['post_content'], $matchs);
    if(count($matchs[1]) > 0){
      foreach($matchs[1] AS $match){
        $vidids[] = $match;
      }
      $vidids = array_unique($vidids);
      foreach($vidids AS $vid){
        $video[]['Video'] = 'http://www.youtube.com/watch?v='.$vid;
      }
    }
    $imgs = array();
    preg_match_all('/<img(.*)src=(\'|")(.*)(\'|")/iU', $get['post_content'], $matchs);
    if(count($matchs[3]) > 0){
      foreach($matchs[3] AS $match){
        $imgs[]['Image'] = $match;
      }
    }
    $get['video'] = $video;
    $result['ID'] = $get['ID'];
    $result['Title'] = $get['post_title'];
    $result['Dept_ID'] = $get['term_taxonomy_id'];
    $result['Title_Cat'] = $get['category'];
    $result['Content'] = $get['post_content'];
    $result['DateTime'] = strtotime($get['post_modified']);
    $result['Img'] = $get['featuredthumb'];
    $result['Visit_Num'] = 0;
    $result['Link_Share'] = $get['guid'];
    $result['Data'] = array();
    $result['Images'] = $imgs;
    $result['Videos'] = $video;
    $json['News_Display'][] = $result;
    $this->output($json);
  }

  public function list_videos(){
    global $wpdb;
    $tbfx = $wpdb->base_prefix;
    $page = (empty($_REQUEST['Page'])) ? "1": $_REQUEST['Page'];
    $page--;
    $start = $page*10;
    $json['Videos_Display'] = array();
    $sql = "SELECT ".$tbfx."hdflvvideoshare.*,playlist_id FROM ".$tbfx."hdflvvideoshare INNER JOIN ".$tbfx."hdflvvideoshare_med2play ON media_id=vid LIMIT $start,10";
    if (!empty($_REQUEST['Depts_ID'])) {
      $sql = "SELECT ".$tbfx."hdflvvideoshare.*,playlist_id FROM ".$tbfx."hdflvvideoshare INNER JOIN ".$tbfx."hdflvvideoshare_med2play ON media_id=vid WHERE playlist_id = '$_REQUEST[Depts_ID]' LIMIT $start,10";
    }
    $rows = $wpdb->get_results($sql, 'ARRAY_A');
    $temp = array();
    foreach ($rows as $key => $row) {
      $temp['ID'] = $row['vid'];
      $temp['Duration'] = $row['duration'];
      $temp['Links'] = substr($row['file'], strpos($row['file'], '=')+1, strlen($row['file']));
      $temp['Dept_ID'] = $row['playlist_id'];
      $temp['Title'] = $row['name'];
      $cat = $wpdb->get_row("SELECT playlist_name FROM ".$tbfx."hdflvvideoshare_playlist WHERE pid='$row[playlist_id]'", 'ARRAY_A');
      $temp['Title_Cat'] = (!empty($cat))?$cat['playlist_name']:""; 
      $temp['DateTime'] = (string) strtotime($row['post_date']);
      $temp['Pic'] = $row['image'];
      $temp['Des'] = $row['description'];
      $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
      $temp['Link_Share'] = 'http://' . $_SERVER['HTTP_HOST'] . $uri_parts[0].$temp['Title'];
      $temp['Data'] = array();
      $json['Videos_Display'][]=$temp;
    }
    $this->output($json);
  }

  public function list_images(){
    global $wpdb;
    $tbfx = $wpdb->base_prefix;
    $page = (empty($_REQUEST['Page'])) ? "1": $_REQUEST['Page'];
    $page--;
    $start = $page*10;
    $json['Gallery_List'] = array();
    $sql = "SELECT ".$tbfx."ngg_pictures.*,path,title FROM ".$tbfx."ngg_pictures INNER JOIN ".$tbfx."ngg_gallery ON gid=galleryid ORDER BY pid DESC LIMIT $start,10";
    if (!empty($_REQUEST['Depts_ID'])) {
      $sql = "SELECT ".$tbfx."ngg_pictures.*,path,title FROM ".$tbfx."ngg_pictures INNER JOIN ".$tbfx."ngg_gallery ON gid=galleryid WHERE galleryid='$_REQUEST[Depts_ID]' ORDER BY pid DESC LIMIT $start,10";
    }
    $rows = $wpdb->get_results($sql, 'ARRAY_A');
    $temp = array();
    foreach ($rows as $key => $row) {
      $temp['ID'] = $row['pid'];
      $temp['Dept_ID'] = $row['galleryid'];
      $temp['Title'] = $row['alttext'];
      $temp['Title_Cat'] = $row['title'];
      $temp['DateTime'] = $row['updated_at'];
      $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
      $temp['Pic'] = 'http://'.$_SERVER['HTTP_HOST'].$uri_parts[0].$row['path'].'/'.$row['filename'];
      $temp['Des'] = $row['description'];
      $temp['Link_Share'] = 'http://'.$_SERVER['HTTP_HOST'].$uri_parts[0].$temp['Title'];
      $temp['Source'] = "";
      $temp['Author'] = "";
      $json['Gallery_List'][]=$temp;
    }
    $this->output($json);
  }

  function display_image() {
    global $wpdb;
    $tbfx = $wpdb->base_prefix;
    $json['Gallery_Display'] = array();
    $sql = "SELECT ".$tbfx."ngg_pictures.*,path,title FROM ".$tbfx."ngg_pictures INNER JOIN ".$tbfx."ngg_gallery ON gid=galleryid WHERE pid='$_REQUEST[ID]' ORDER BY pid DESC";
    $row = $wpdb->get_row($sql, 'ARRAY_A');
    $lists = array();
    $temp = array();
    $temp['ID'] = $row['pid'];
    $temp['Dept_ID'] = $row['galleryid'];
    $temp['Title'] = $row['alttext'];
    $temp['Title_Cat'] = $row['title'];
    $temp['DateTime'] = $row['updated_at'];
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    $temp['Images'][]['Image'] = 'http://'.$_SERVER['HTTP_HOST'].$uri_parts[0].$row['path'].'/'.$row['filename'];
    $temp['Des'] = $row['description'];
    $temp['Link_Share'] = 'http://'.$_SERVER['HTTP_HOST'].$uri_parts[0].$temp['Title'];
    $temp['Data'] = array();
    $json['Gallery_Display']=$temp;
    $this->output($json);
  }

  public function display_video(){
    global $wpdb;
    $tbfx = $wpdb->base_prefix;
    $json['Videos_Display'] = array();
    $sql = "SELECT ".$tbfx."hdflvvideoshare.*,playlist_id FROM ".$tbfx."hdflvvideoshare INNER JOIN ".$tbfx."hdflvvideoshare_med2play ON media_id=vid WHERE vid='$_REQUEST[ID]'";
    $row = $wpdb->get_row($sql, 'ARRAY_A');
    $temp = array();
    $temp['ID'] = $row['vid'];
    $temp['Duration'] = $row['duration'];
    $temp['Links'] = substr($row['file'], strpos($row['file'], '=')+1, strlen($row['file']));
    $temp['Dept_ID'] = $row['playlist_id'];
    $temp['Title'] = $row['name'];
    $cat = $wpdb->get_row("SELECT playlist_name FROM ".$tbfx."hdflvvideoshare_playlist WHERE pid='$row[playlist_id]'", 'ARRAY_A');
    $temp['Title_Cat'] = (!empty($cat))?$cat['playlist_name']:"";
    $temp['DateTime'] = (string) strtotime($row['post_date']);
    $temp['Pic'] = $row['image'];
    $temp['Des'] = $row['description'];
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    $temp['Link_Share'] = 'http://' . $_SERVER['HTTP_HOST'] . $uri_parts[0].$temp['Title'];
    $temp['Data'] = array();
    $json['Videos_Display']=$temp;
    $this->output($json);
  }
  
  public function list_news($jsontitle='News_List'){
    global $wpdb;
    $table = $wpdb->prefix.'posts';
    if(!empty($_REQUEST['Source_ID'])){
      $where = 'AND '.$wpdb->prefix."term_relationships.term_taxonomy_id='$_REQUEST[Source_ID]'";
    }
    else{
      $where = '';
    }
    $sql = $this->posts_query("$where GROUP BY $table.ID ORDER BY $table.post_date DESC");
    $gets = $wpdb->get_results($sql, 'ARRAY_A');
    if(!$gets){
      return $this->output('');
    }
    $json[$jsontitle] = array();
    foreach($gets AS $get){
      $imgresize = get_option('thumbnail_size_h').'x'.get_option('thumbnail_size_w');
      $image = get_the_post_thumbnail($get['ID'], 'full');
      if(!empty($image)){
        preg_match_all('/src="([^"]*)"/i', $image, $src);
        $get['featuredimage'] = $src[1][0];
        $ext = strtolower(substr($src[1][0], strrpos($src[1][0], '.')+1));
        $get['featuredthumb'] = str_replace('.'.$ext, '', $src[1][0]).'-'.$imgresize.'.'.$ext;
      }
      else{
        $get['featuredimage'] = '';
        $get['featuredthumb'] = '';
      }
      $result['ID'] = $get['ID'];
      $result['Dept_ID'] = $get['term_taxonomy_id'];
      $result['Title'] = $get['post_title'];
      $result['DateTime'] = (string) strtotime($get['post_modified']);
      $result['Cat'] = $get['category'];
      $result['Pic'] = $get['featuredthumb'];
      $result['Des'] = strip_tags($get['post_content']);
      $result['Link_Share'] = $get['guid'];
      $json[$jsontitle][] = $result;
    }
    $this->output($json);
  }

  public function list_pages(){
    $start = 0;
    if (!empty($_REQUEST['Page'])) { $start = ($_REQUEST['Page']-1)*10; }
    global $wpdb;
    $json['Pages_List'] = array();
    $tbfx = $wpdb->base_prefix;
    $sql = "SELECT * FROM ".$tbfx."posts WHERE post_type='page' AND post_status='publish' ORDER BY ID DESC LIMIT $start,10";
    $pages = $wpdb->get_results($sql, 'ARRAY_A');
    if(!empty($pages)){
      foreach($pages as $page){
        $result['ID'] = (string) $page['ID'];
        $result['Title'] = $page['post_title'];
        $result['Action_ID'] = '26';
        $result['Content'] = do_shortcode($page['post_content']);
        $result['Title_Cat'] = '';
        $result['Dept_ID'] = '0';
        $imgresize = get_option('thumbnail_size_h').'x'.get_option('thumbnail_size_w');
        $image = get_the_post_thumbnail($page['ID'], 'full');
        if(!empty($image)){
          preg_match_all('/src="([^"]*)"/i', $image, $src);
          $ext = strtolower(substr($src[1][0], strrpos($src[1][0], '.')+1));
          $result['Img'] = str_replace('.'.$ext, '', $src[1][0]).'-'.$imgresize.'.'.$ext;
        }
        else{ $result['Img'] = ''; }
        $result['Link_Share'] = $page['guid'];
        $json['Pages_List'][] = $result;
      }
    }
    $this->output($json);
  }

  public function list_products(){
    $start = 0;
    if (!empty($_REQUEST['Page'])) { $start = ($_REQUEST['Page']-1)*10; }
    global $wpdb;
    $json['Products_List'] = array();
    $tbfx = $wpdb->base_prefix;
    $sql = "SELECT * FROM ".$tbfx."posts WHERE post_type='product' AND post_status='publish' ORDER BY ID DESC LIMIT $start,10";
    $pages = $wpdb->get_results($sql, 'ARRAY_A');
    if(!empty($pages)){
      foreach($pages as $page){
        $result['ID'] = (string) $page['ID'];
        $result['Title'] = $page['post_title'];
        $result['Content'] = do_shortcode($page['post_content']);
        $result['Title_Cat'] = '';
        $result['Dept_ID'] = '0';
        $imgresize = get_option('thumbnail_size_h').'x'.get_option('thumbnail_size_w');
        $image = get_the_post_thumbnail($page['ID'], 'full');
        if(!empty($image)){
          preg_match_all('/src="([^"]*)"/i', $image, $src);
          $ext = strtolower(substr($src[1][0], strrpos($src[1][0], '.')+1));
          $result['Img'] = str_replace('.'.$ext, '', $src[1][0]).'-'.$imgresize.'.'.$ext;
        }
        else{ $result['Img'] = ''; }
        $result['Link_Share'] = $page['guid'];
        $json['Products_List'][] = $result;
      }
    }
    $this->output($json);
  }

  public function display_page(){
    global $wpdb;
    $json['Pages_Display'] = array();
    $tbfx = $wpdb->base_prefix;
    $sql = "SELECT * FROM ".$tbfx."posts WHERE post_type='page' AND post_status='publish' AND ID='$_REQUEST[ID]'";
    $page = $wpdb->get_row($sql, 'ARRAY_A');
    if(!empty($page)){
      $result['ID'] = (string) $page['ID'];
      $result['Title'] = $page['post_title'];
      $result['Content'] = strip_tags($page['post_content'],"<p><a>") ;
      $result['Title_Cat'] = '';
      $result['Dept_ID'] = '0';
      $imgresize = get_option('thumbnail_size_h').'x'.get_option('thumbnail_size_w');
      $image = get_the_post_thumbnail($page['ID'], 'full');
      if(!empty($image)){
        preg_match_all('/src="([^"]*)"/i', $image, $src);
        $ext = strtolower(substr($src[1][0], strrpos($src[1][0], '.')+1));
        $result['Img'] = str_replace('.'.$ext, '', $src[1][0]).'-'.$imgresize.'.'.$ext;
      }
      else{ $result['Img'] = ''; }
      $result['Link_Share'] = $page['guid'];
      $result['DateTime'] = (string) strtotime($page['post_date']);
      $result['Data'] = array();
      $json['Pages_Display'] = $result;
    }
    $this->output($json);
  }

  public function display_product(){
    global $wpdb;
    $json['Products_Display'] = array();
    $tbfx = $wpdb->base_prefix;
    $sql = "SELECT * FROM ".$tbfx."posts WHERE post_type='product' AND post_status='publish' AND ID='$_REQUEST[ID]'";
    $page = $wpdb->get_row($sql, 'ARRAY_A');
    if(!empty($page)){
      $result['ID'] = (string) $page['ID'];
      $result['Title'] = $page['post_title'];
      $result['Content'] = strip_tags($page['post_content'],"<p><a>") ;
      $result['Title_Cat'] = '';
      $result['Dept_ID'] = '0';
      $imgresize = get_option('thumbnail_size_h').'x'.get_option('thumbnail_size_w');
      $image = get_the_post_thumbnail($page['ID'], 'full');
      if(!empty($image)){
        preg_match_all('/src="([^"]*)"/i', $image, $src);
        $ext = strtolower(substr($src[1][0], strrpos($src[1][0], '.')+1));
        $result['Img'] = str_replace('.'.$ext, '', $src[1][0]).'-'.$imgresize.'.'.$ext;
      }
      else{ $result['Img'] = ''; }
      $result['Link_Share'] = $page['guid'];
      $result['DateTime'] = (string) strtotime($page['post_date']);
      $result['Data'] = array();
      $json['Products_Display'] = $result;
    }
    $this->output($json);
  }
  
  private function posts_query($where='', $inner=''){
    $start = 0;
    if (!empty($_REQUEST['Page'])) { $start = ($_REQUEST['Page']-1) * 10; }
    global $wpdb;
    $table = $wpdb->prefix.'posts';
    return "SELECT $table.ID,$table.post_title,$table.guid,$table.post_content,$table.post_author,$table.post_date,$table.post_date_gmt
    ,$table.comment_status,$table.ping_status,$table.post_name,$table.post_modified,$table.post_modified_gmt,$table.post_content_filtered
    ,$table.post_parent,$table.menu_order,$table.comment_count,".$wpdb->prefix."term_relationships.term_taxonomy_id,".$wpdb->prefix."terms.name AS category FROM $table
    INNER JOIN ".$wpdb->prefix."term_relationships ON($table.ID=".$wpdb->prefix."term_relationships.object_id)
    INNER JOIN ".$wpdb->prefix."term_taxonomy ON(".$wpdb->prefix."term_taxonomy.term_taxonomy_id=".$wpdb->prefix."term_relationships.term_taxonomy_id AND ".$wpdb->prefix."term_taxonomy.taxonomy='category')
    INNER JOIN ".$wpdb->prefix."terms ON(".$wpdb->prefix."terms.term_id=".$wpdb->prefix."term_taxonomy.term_id)
    $inner
    WHERE $table.post_status='publish' AND $table.post_type='post' $where LIMIT $start,10";
  }

  private function output($result){
    header('Content-Type: application/json; charset=utf-8');
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPod') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
        foreach ($result as $key => $value) {
          echo json_encode($value, 256); break;
        }
      }
    else {
      echo json_encode($result, 256);
    }
    exit();
  }
}

?>