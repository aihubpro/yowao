<?php
/**
 * Yowao Navigation Pro v1.0.0 - 核心功能库
 * * @package Yowao
 * @author 熊哥
 * @version 1.0.0
 */

add_action('after_setup_theme', function(){ add_theme_support('post-thumbnails'); });

add_filter('show_admin_bar', function($show){ return is_admin() ? $show : false; });

// 0. 前台加载媒体库
add_action('wp_enqueue_scripts', function(){ 
    wp_enqueue_media(); wp_enqueue_script('jquery');
});

// 后台加载媒体库
add_action('admin_enqueue_scripts', function($hook){
    wp_enqueue_media();
});

// 1. 后台菜单优化
add_action('admin_menu', function(){
    remove_menu_page('edit.php'); 
    remove_menu_page('edit-comments.php');
    remove_submenu_page('edit.php?post_type=yowao_bookmark', 'post-new.php?post_type=yowao_bookmark');
    remove_submenu_page('edit.php?post_type=yowao_app', 'post-new.php?post_type=yowao_app');
    add_menu_page('主题设置', '主题设置', 'manage_options', 'yowao_settings', 'y_render_settings_page', 'dashicons-admin-generic', 5);
});

// 2. 注册文章类型
add_action('init', function(){
    register_post_type('yowao_bookmark', [
        'labels' => ['name' => '我的收藏', 'add_new' => '添加收藏', 'add_new_item' => '添加收藏', 'edit_item' => '编辑收藏', 'new_item' => '添加收藏', 'view_item' => '查看收藏'],
        'public' => false, 'show_ui' => true, 'menu_position' => 3, 'menu_icon' => 'dashicons-star-filled',
        'supports' => ['title', 'author', 'page-attributes'],
        'show_in_menu' => true
    ]);
    register_post_type('yowao_app', [
        'labels' => ['name' => '网址导航', 'add_new' => '添加网址', 'add_new_item' => '添加网址', 'edit_item' => '编辑网址', 'new_item' => '添加网址', 'view_item' => '查看网址'],
        'public' => true, 'show_ui' => true, 'menu_position' => 4, 'menu_icon' => 'dashicons-admin-site-alt3',
        'supports' => ['title', 'author', 'page-attributes', 'thumbnail'],
        'show_in_menu' => true
    ]);
});

// 2.1 自动转换别名与自动排序
add_filter('wp_insert_post_data', function($data, $postarr){
    if(in_array($data['post_type'], ['yowao_bookmark', 'yowao_app']) && $data['post_status'] != 'auto-draft'){
        if( $postarr['ID'] == 0 || empty($postarr['post_name']) ) {
            $title = isset($data['post_title']) ? $data['post_title'] : '';
            $has_cn = preg_match('/[\x{4e00}-\x{9fa5}]/u', $title);
            $slug_from_title = $has_cn ? y_pinyin($title) : sanitize_title($title);
            $slug_from_title .= date('md');
            $data['post_name'] = $slug_from_title;
        }
        if($postarr['ID'] == 0 || !isset($postarr['menu_order']) || $postarr['menu_order'] == 0){
            global $wpdb;
            $max = $wpdb->get_var($wpdb->prepare("SELECT MAX(menu_order) FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish'", $data['post_type']));
            $data['menu_order'] = $max ? ($max + 1) : 1;
        }
    }
    return $data;
}, 10, 2);

// ★★★ 核心优化：替换 Gravatar 为 Cravatar (国内镜像) ★★★
add_filter('get_avatar_url', function($url){
    return str_replace(
        ['www.gravatar.com/avatar/', '0.gravatar.com/avatar/', '1.gravatar.com/avatar/', '2.gravatar.com/avatar/', 'secure.gravatar.com/avatar/'], 
        'cravatar.cn/avatar/', 
        $url
    );
});

// 3. 主题设置页面
function y_render_settings_page() {
    if(isset($_POST['y_save_settings'])) {
        // 安全检查：设置页面自带 nonce 检查
        check_admin_referer('yowao_settings_save');
        
        if(isset($_POST['engines'])) update_option('yowao_search_config_v66', $_POST['engines']);
        if(isset($_POST['footer_text'])) update_option('yowao_footer_text', stripslashes($_POST['footer_text']));
        if(isset($_POST['icp_num'])) update_option('yowao_icp_num', sanitize_text_field($_POST['icp_num']));
        update_option('yowao_icp_link', esc_url_raw($_POST['icp_link'] ?? ''));
        update_option('yowao_logo_url', esc_url_raw($_POST['logo_url'] ?? ''));
        update_option('yowao_seo_desc', sanitize_text_field($_POST['seo_desc'] ?? ''));
        update_option('yowao_seo_keywords', sanitize_text_field($_POST['seo_keywords'] ?? ''));
        update_option('yowao_footer_links', stripslashes($_POST['footer_links'] ?? ''));
        update_option('yowao_analytics_code', stripslashes($_POST['analytics_code'] ?? ''));
        update_option('yowao_enable_email_reg', isset($_POST['enable_email_reg']) ? 1 : 0);
        
        echo '<div class="updated"><p>配置已保存！</p></div>';
    }
    
    $defaults = [
        'bing' => ['name'=>'必应', 'icon'=>'', 'order'=>1, 'url'=>'https://www.bing.com/search?q='],
        'baidu' => ['name'=>'百度', 'icon'=>'', 'order'=>2, 'url'=>'https://www.baidu.com/s?wd='],
        'google' => ['name'=>'谷歌', 'icon'=>'', 'order'=>3, 'url'=>'https://www.google.com/search?q=']
    ];
    $engines = get_option('yowao_search_config_v66', $defaults);
    uasort($engines, function($a, $b){ return $a['order'] - $b['order']; });
    $enable_email = get_option('yowao_enable_email_reg', 1);
    ?>
    <div class="wrap">
        <h1>主题核心设置</h1>
        <form method="post">
            <?php wp_nonce_field('yowao_settings_save'); ?>
            <h2 class="title">一、注册与安全</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">启用邮箱验证 (极速注册)</th>
                    <td>
                        <label><input type="checkbox" name="enable_email_reg" value="1" <?php checked($enable_email, 1); ?>> 注册时强制验证邮箱</label>
                        <p class="description">如果不勾选，用户仅需输入用户名和密码即可注册。</p>
                    </td>
                </tr>
            </table>
            <h2 class="title">二、搜索引擎排序</h2>
            <table class="widefat fixed" style="max-width:900px; margin-bottom:30px;">
                <thead><tr><th width="80">引擎</th><th>显示名称</th><th width="80">排序</th></tr></thead>
                <tbody>
                <?php foreach(['bing','baidu','google'] as $k): $v = $engines[$k] ?? $defaults[$k]; ?>
                <tr>
                    <td><strong><?php echo strtoupper($k); ?></strong></td>
                    <td><input type="text" name="engines[<?php echo $k; ?>][name]" value="<?php echo esc_attr($v['name']); ?>" style="width:100%"></td>
                    <td><input type="number" name="engines[<?php echo $k; ?>][order]" value="<?php echo esc_attr($v['order']); ?>" style="width:60px"><input type="hidden" name="engines[<?php echo $k; ?>][url]" value="<?php echo $defaults[$k]['url']; ?>"></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <h2 class="title">三、站点 Logo & 底部</h2>
            <table class="form-table">
                <tr><th>Logo 图片</th><td><input type="text" id="logo_url" name="logo_url" value="<?php echo esc_attr(get_option('yowao_logo_url','')); ?>" class="regular-text"><input type="button" class="button" value="上传" onclick="yUpload('logo_url')"></td></tr>
                <tr><th>备案号 (ICP)</th><td>
                    <input type="text" name="icp_num" value="<?php echo esc_attr(get_option('yowao_icp_num','')); ?>" class="regular-text" placeholder="示例：粤ICP备xxxx号">
                </td></tr>
                <tr><th>备案链接</th><td>
                    <input type="url" name="icp_link" value="<?php echo esc_attr(get_option('yowao_icp_link','https://beian.miit.gov.cn/')); ?>" class="regular-text">
                </td></tr>
                <tr><th>版权/自定义HTML</th><td><textarea name="footer_links" rows="2" class="large-text code"><?php echo esc_textarea(get_option('yowao_footer_links','')); ?></textarea></td></tr>
                <tr><th>统计代码</th><td>
                    <textarea name="analytics_code" rows="4" class="large-text code" placeholder="<script>...</script>"><?php echo esc_textarea(get_option('yowao_analytics_code','')); ?></textarea>
                </td></tr>
            </table>
            <h2 class="title">SEO 信息</h2>
            <table class="form-table">
                <tr><th>网站描述</th><td><input type="text" name="seo_desc" value="<?php echo esc_attr(get_option('yowao_seo_desc','')); ?>" class="regular-text"></td></tr>
                <tr><th>关键词</th><td><input type="text" name="seo_keywords" value="<?php echo esc_attr(get_option('yowao_seo_keywords','')); ?>" class="regular-text"></td></tr>
            </table>
            <p class="submit"><input type="submit" name="y_save_settings" class="button button-primary" value="保存所有设置"></p>
        </form>
    </div>
    <script>function yUpload(id){var m=wp.media({title:'选择图片',button:{text:'确定'},multiple:false});m.on('select',function(){document.getElementById(id).value=m.state().get('selection').first().toJSON().url;});m.open();}</script>
    <?php
}

// 4. 列表与字段
add_filter('manage_yowao_bookmark_posts_columns', function($c){ return ['cb'=>$c['cb'], 'title'=>'名称', 'url'=>'跳转网址', 'author'=>'作者', 'order'=>'排序', 'date'=>'日期']; });
add_action('manage_yowao_bookmark_posts_custom_column', function($c, $id){
    if($c=='url') echo get_post_meta($id, '_book_url', true);
    if($c=='order') echo get_post($id)->menu_order;
}, 10, 2);

add_filter('manage_yowao_app_posts_columns', function($c){ return ['cb'=>$c['cb'], 'title'=>'名称', 'url'=>'网址', 'type'=>'类型', 'likes'=>'点赞', 'order'=>'排序', 'date'=>'日期']; });
add_action('manage_yowao_app_posts_custom_column', function($c, $id){
    if($c=='url') echo get_post_meta($id, '_app_url', true);
    if($c=='type') echo (get_post_meta($id, '_app_style', true)=='card'?'宽版卡片':'标准图标');
    if($c=='likes') echo (int)get_post_meta($id, '_app_likes', true);
    if($c=='order') echo get_post($id)->menu_order;
}, 10, 2);

add_filter('manage_edit-yowao_bookmark_sortable_columns', function($cols){ $cols['order']='menu_order'; return $cols; });
// [修改] 增加 'type' => 'type' 让类型列可排序
add_filter('manage_edit-yowao_app_sortable_columns', function($cols){ $cols['type']='type'; $cols['likes']='likes'; $cols['order']='menu_order'; return $cols; });

add_action('pre_get_posts', function($q){ 
    if(is_admin() && $q->is_main_query()){
        if($q->get('post_type')==='yowao_app'){ 
            $ob=$q->get('orderby'); 
            if($ob==='likes'){ $q->set('meta_key','_app_likes'); $q->set('orderby','meta_value_num'); }
            // [修改] 增加类型的排序逻辑 (根据 _app_style 字段)
            elseif($ob==='type'){ $q->set('meta_key','_app_style'); $q->set('orderby','meta_value'); }
            elseif($ob==='menu_order'){ $q->set('orderby','menu_order'); } 
        }
        if($q->get('post_type')==='yowao_bookmark'){
            $ob=$q->get('orderby');
            if($ob==='menu_order'){ $q->set('orderby','menu_order'); }
        }
    } 
});

add_action('add_meta_boxes', function(){
    add_meta_box('book_box', '收藏设置', 'y_html_bk', 'yowao_bookmark', 'normal', 'high');
    add_meta_box('app_box', '网址设置', 'y_html_ap', 'yowao_app', 'normal', 'high');
});

// [核心修复] 后台 JS 上传逻辑也加上 Security Nonce
add_action('admin_footer', function(){
    $nonce = wp_create_nonce('yowao_frontend_action');
    ?>
    <script>
    function yU(id){
        var m=wp.media({title:"选择资源",button:{text:"确认"},multiple:false})
        .on("select",function(){document.getElementById(id).value=m.state().get("selection").first().toJSON().url})
        .open();
    }
    jQuery(document).ready(function($){
        var f=$('input[name="app_img_file"]');
        var t=$('#ai');
        var pid=$('#post_ID').val();
        var ti=$('#title');
        var security = '<?php echo $nonce; ?>'; // 这里的钥匙
        
        if(ti.length){ ti.on('input', function(){ this.value = this.value.replace(/[^\u4e00-\u9fa5A-Za-z0-9\s]/g,''); }); }
        if(f.length){
            f.on('change',function(){
                if(!this.files.length)return;
                var fd=new FormData(); 
                fd.append('action','y_upload_app_img'); 
                fd.append('pid',pid); 
                fd.append('file',this.files[0]);
                fd.append('security', security); // 带上钥匙
                fetch(ajaxurl,{method:'POST',body:fd}).then(function(r){return r.json()}).then(function(d){
                    if(d&&d.success&&d.data&&d.data.url){t.val(d.data.url);}else{alert('上传失败: '+(d.data.msg||'未知错误'));}
                }).catch(function(){alert('上传失败');});
            });
        }
    });
    </script>
    <?php
});

function y_html_bk($p){
    $u = get_post_meta($p->ID, "_book_url", true); $i = get_post_meta($p->ID, "_book_img", true);
    echo '<p>跳转网址: <input type="text" name="book_url" value="'.$u.'" style="width:100%"></p>';
    echo '<p>图标地址: <input type="text" id="bi" name="book_img" value="'.$i.'" style="width:80%"><input type="button" class="button" onclick="yU(\'bi\')" value="上传"></p>';
}
function y_html_ap($p){
    $v = []; foreach(['url','img','style','label','likes','desc'] as $k) $v[$k]=get_post_meta($p->ID,"_app_$k",true);
    ?>
    <p><strong>显示样式:</strong> <label><input type="radio" name="app_style" value="icon" <?php checked($v['style']?:'icon','icon');?>> 标准图标</label> &nbsp; <label><input type="radio" name="app_style" value="card" <?php checked($v['style'],'card');?>> 宽版卡片</label></p>
    <p class="description" style="color:#d63638">注意：前端只会在列表的前2位（排序1和2）显示为“宽版卡片”，排在后面的即使选择卡片样式也会自动降级为“标准图标”。</p>
    <p>跳转链接: <input type="text" name="app_url" value="<?php echo esc_attr($v['url']); ?>" style="width:100%"></p>
    <p>描述: <textarea name="app_desc" style="width:100%;height:50px;"><?php echo esc_textarea($v['desc']); ?></textarea></p>
    <p>角标文字: <input type="text" name="app_label" value="<?php echo esc_attr($v['label']); ?>"> &nbsp; 虚拟点赞: <input type="number" name="app_likes" value="<?php echo intval($v['likes']); ?>"></p>
    <p>封面/Logo: <input type="text" id="ai" name="app_img" value="<?php echo esc_attr($v['img']); ?>" style="width:60%"> <input type="file" name="app_img_file" accept="image/*"> <input type="button" class="button" onclick="yU('ai')" value="选择媒体库"></p>
    <?php
}

// 5. 保存与辅助
function y_pinyin($s) {
    $o = $s;
    if(class_exists('Transliterator')){
        $tr = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()');
        if($tr){ $s = $tr->transliterate($s); }
    } else {
        $s = @iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);
        if($s === false) $s = $o;
    }
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9\s-]/','', $s);
    $s = preg_replace('/\s+|-+/','', $s);
    $s = trim($s);
    return $s;
}
add_filter('wp_unique_post_slug', function($slug, $p_ID, $status, $type) { return $slug; }, 10, 4);
add_action('save_post', function($pid){
    if(isset($_POST['book_url'])){
        $u=trim($_POST['book_url']); if($u && !preg_match("~^(?:f|ht)tps?://~i",$u)) $u="https://".$u;
        update_post_meta($pid,"_book_url",$u);
        if(isset($_POST['book_img'])){ $bi = trim($_POST['book_img']); if($bi !== '') update_post_meta($pid,"_book_img",esc_url_raw($bi)); }
    }
    if(isset($_POST['app_url'])){
        $u=trim($_POST['app_url']); if($u && !preg_match("~^(?:f|ht)tps?://~i",$u)) $u="https://".$u;
        update_post_meta($pid,"_app_url",$u);
        foreach(['img','style','label','likes','desc'] as $f) if(isset($_POST["app_$f"])) update_post_meta($pid,"_app_$f",$_POST["app_$f"]);
    }
});

function y_check_file_type($path) {
    $info = @getimagesize($path);
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp','ico'];
    if(!$path || !$info || strpos(($info['mime'] ?? ''), 'image/') !== 0 || !in_array($ext, $allowed)){ return false; }
    return true;
}

// 6. 核心接口 (★★★ 安全增强：增加 Nonce 校验 ★★★)

add_action('wp_ajax_y_upload_app_img', function(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    if(!is_user_logged_in()) wp_send_json_error(['msg'=>'auth']);
    if(!current_user_can('upload_files')) wp_send_json_error(['msg'=>'perm']);
    $pid = intval($_POST['pid'] ?? 0);
    if(empty($_FILES['file'])) wp_send_json_error(['msg'=>'nofile']);
    
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    
    $att_id = media_handle_upload('file', $pid);
    if(is_wp_error($att_id)) wp_send_json_error(['msg'=>$att_id->get_error_message()]);
    
    $path = get_attached_file($att_id);
    if(!y_check_file_type($path)){ wp_delete_attachment($att_id, true); wp_send_json_error(['msg'=>'仅允许上传 jpg,png,gif,webp,ico']); }
    
    $url = wp_get_attachment_url($att_id);
    update_post_meta($pid, "_app_img", $url);
    wp_send_json_success(['url'=>$url]);
});

add_action('wp_ajax_y_profile_v67', function(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    if(!is_user_logged_in()) wp_send_json_error();
    $uid = get_current_user_id();
    if(isset($_POST['avatar_url'])) update_user_meta($uid, 'yowao_avatar', esc_url_raw($_POST['avatar_url']));
    if(isset($_POST['bg_url'])) update_user_meta($uid, 'yowao_bg', esc_url_raw($_POST['bg_url']));
    if($_POST['mode'] === 'reset') delete_user_meta($uid, 'yowao_bg');
    wp_send_json_success();
});

add_action('wp_ajax_y_del_bookmark', function(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    if(!is_user_logged_in()) wp_send_json_error();
    $id = intval($_POST['id']);
    $post = get_post($id);
    if($post->post_author == get_current_user_id() && $post->post_type == 'yowao_bookmark') { wp_delete_post($id, true); wp_send_json_success(); }
    wp_send_json_error();
});

add_action('wp_ajax_y_upload_profile_file', function(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    if(!is_user_logged_in()) wp_send_json_error(['code'=>'auth']);
    if(empty($_FILES['file'])) wp_send_json_error(['code'=>'nofile']);
    
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    
    $att_id = media_handle_upload('file', 0);
    if(is_wp_error($att_id)) wp_send_json_error(['code'=>'err']);
    
    $path = get_attached_file($att_id);
    if(!y_check_file_type($path)){ wp_delete_attachment($att_id, true); wp_send_json_error(['code'=>'bad-file']); }
    $url = wp_get_attachment_url($att_id);
    wp_send_json_success(['url'=>$url]);
});

add_action('wp_ajax_y_add_bookmark', function(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    if(!is_user_logged_in()) wp_send_json_error(['msg'=>'请先登录']);
    $title = sanitize_text_field($_POST['title']);
    $url = esc_url_raw($_POST['url']);
    if(!$title || !$url) wp_send_json_error(['msg'=>'标题和网址不能为空']);
    
    $pid = wp_insert_post(['post_title'=>$title, 'post_status'=>'publish', 'post_type'=>'yowao_bookmark', 'post_author'=>get_current_user_id()]);
    
    if($pid){
        if(!preg_match("~^(?:f|ht)tps?://~i", $url)) $url = "https://" . $url;
        update_post_meta($pid, "_book_url", $url);
        
        $img_saved = false; $img_url = ''; $img_error = '';
        $file_received = !empty($_FILES['img_file']) && !empty($_FILES['img_file']['name']);
        
        if($file_received){
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $att_id = media_handle_upload('img_file', $pid);
            if(is_wp_error($att_id)) { $img_error = 'wp_error'; } else {
                $path = get_attached_file($att_id);
                if(y_check_file_type($path)){ $img_url = wp_get_attachment_url($att_id); if($img_url){ update_post_meta($pid, "_book_img", $img_url); $img_saved = true; } } else { wp_delete_attachment($att_id, true); $img_error = 'format_error'; }
            }
        }
        if(!$img_saved && !empty($_POST['img_url'])){ $iu = esc_url_raw($_POST['img_url']); if($iu) { update_post_meta($pid, "_book_img", $iu); $img_url = $iu; } }
        wp_send_json_success(['msg'=>'添加成功']);
    } else { wp_send_json_error(['msg'=>'系统错误']); }
});

add_action('wp_ajax_nopriv_y_like_app', 'y_like_app'); add_action('wp_ajax_y_like_app', 'y_like_app');
function y_like_app(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    $id = intval($_POST['id'] ?? 0);
    $post = get_post($id);
    if(!$post || $post->post_type !== 'yowao_app') wp_send_json_error(['code'=>'invalid']);
    if(!is_user_logged_in()) wp_send_json_error(['code'=>'auth']);
    $uid = get_current_user_id();
    if(get_user_meta($uid, 'yowao_like_app_'.$id, true)) {
        $cur = (int)get_post_meta($id, '_app_likes', true);
        wp_send_json_error(['code'=>'already', 'likes'=>$cur]);
    }
    $cur = (int)get_post_meta($id, '_app_likes', true);
    update_post_meta($id, '_app_likes', $cur + 1);
    update_user_meta($uid, 'yowao_like_app_'.$id, 1);
    wp_send_json_success(['likes' => $cur + 1]);
}

add_action('wp_ajax_nopriv_y_get_app_likes', 'y_get_app_likes'); add_action('wp_ajax_y_get_app_likes', 'y_get_app_likes');
function y_get_app_likes(){
    // 获取数据不需要严格的nonce，但加上也无妨
    $id = intval($_POST['id'] ?? 0);
    $cur = (int)get_post_meta($id, '_app_likes', true);
    wp_send_json_success(['likes'=>$cur]);
}

add_action('wp_ajax_y_click_bookmark', 'y_click_bookmark');
function y_click_bookmark(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    if(!is_user_logged_in()) wp_send_json_error(['code'=>'auth']);
    $id = intval($_POST['id'] ?? 0);
    $post = get_post($id);
    if(!$post || $post->post_type !== 'yowao_bookmark') wp_send_json_error(['code'=>'invalid']);
    if((int)$post->post_author !== get_current_user_id()) wp_send_json_error(['code'=>'denied']);
    $cur = (int)get_post_meta($id, '_book_clicks', true);
    update_post_meta($id, '_book_clicks', $cur + 1);
    wp_send_json_success(['clicks' => $cur + 1]);
}

function get_yowao_list($type){
    $args = ['post_type'=>$type,'posts_per_page'=>99, 'orderby'=>'menu_order date', 'order'=>'ASC'];
    if($type === 'yowao_bookmark') { if(!is_user_logged_in()) return []; $args['author'] = get_current_user_id(); }
    $q = new WP_Query($args);
    $d = []; $p = ($type==='yowao_app')?'_app_':'_book_';
    $ord = 0;
    while($q->have_posts()){$q->the_post(); $id=get_the_ID();
        $item=['id'=>$id,'name'=>get_the_title(),'url'=>get_post_meta($id,$p.'url',true),'img'=>get_post_meta($id,$p.'img',true),'desc'=>get_post_meta($id,$p.'desc',true),'style'=>get_post_meta($id,$p.'style',true)?:'icon','label'=>get_post_meta($id,$p.'label',true),'likes'=>(int)get_post_meta($id,$p.'likes',true)];
        if($type==='yowao_bookmark'){ $item['clicks'] = (int)get_post_meta($id, '_book_clicks', true); $item['ord']=$ord; }
        $d[] = $item; $ord++;
    } wp_reset_postdata();
    if($type==='yowao_bookmark'){
        usort($d, function($a,$b){
            $ac = isset($a['clicks']) ? (int)$a['clicks'] : 0;
            $bc = isset($b['clicks']) ? (int)$b['clicks'] : 0;
            if($ac>0 && $bc>0) return $bc - $ac; 
            if($ac>0 && $bc==0) return -1; 
            if($ac==0 && $bc>0) return 1;
            return 0; 
        });
    }
    return $d;
}

function y_normalize_path($path){
    $parts = explode('/', $path); $stack = [];
    foreach($parts as $p){
        if($p === '' || $p === '.') continue;
        if($p === '..') { if(!empty($stack)) array_pop($stack); continue; }
        $stack[] = $p;
    }
    return '/' . implode('/', $stack);
}

function y_resolve_url($base, $href){
    if(!$href) return '';
    if(preg_match('~^https?://~i', $href)) return $href;
    $bp = wp_parse_url($base);
    if(!$bp || empty($bp['scheme']) || empty($bp['host'])) return $href;
    $origin = $bp['scheme'] . '://' . $bp['host'] . (isset($bp['port'])? (':' . $bp['port']) : '');
    if(strpos($href, '//') === 0) return $bp['scheme'] . ':' . $href;
    if(strpos($href, '/') === 0) return $origin . $href;
    $dir = isset($bp['path']) ? preg_replace('~/[^/]*$~', '/', $bp['path']) : '/';
    $merged = $dir . $href; $norm = y_normalize_path($merged);
    return $origin . $norm;
}

function y_sanitize_href($href){
    $href = html_entity_decode(trim($href), ENT_QUOTES);
    $href = trim($href, " \t\n\r\0\x0B`");
    if(preg_match('/^(javascript:|data:)/i', $href)) return '';
    return $href;
}

function y_pick_best_icon($html, $base){
    $html = preg_replace('/\s+/',' ', $html);
    $candidates = [];
    if(preg_match_all('/<link[^>]*rel=[^>]*icon[^>]*>/i', $html, $tags)){
        foreach($tags[0] as $tag){
            $href = ''; $rel = ''; $sizes = '';
            if(preg_match('/href=["\']([^"\']+)["\']/i', $tag, $m)) $href = $m[1]; elseif(preg_match('/href=([^\s>]+)/i', $tag, $m)) $href = $m[1];
            if(preg_match('/rel=["\']([^"\']+)["\']/i', $tag, $m)) $rel = strtolower($m[1]); elseif(preg_match('/rel=([^\s>]+)/i', $tag, $m)) $rel = strtolower($m[1]);
            if(preg_match('/sizes=["\']([^"\']+)["\']/i', $tag, $m)) $sizes = strtolower($m[1]);
            $href = y_sanitize_href($href); if(!$href) continue;
            $abs = y_resolve_url($base, $href);
            $score = 0;
            if($rel){
                if(strpos($rel, 'shortcut icon') !== false) $score += 30;
                if($rel === 'icon' || strpos($rel, ' icon') !== false) $score += 20;
                if(strpos($rel, 'apple') !== false || strpos($rel, 'touch') !== false) $score -= 10;
                if(strpos($rel, 'mask') !== false) $score -= 5;
            }
            $ext = strtolower(pathinfo(parse_url($abs, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
            if($ext === 'ico') $score += 15; elseif($ext === 'png') $score += 10; elseif($ext === 'jpg' || $ext === 'jpeg') $score += 5; elseif($ext === 'svg') $score += 2;
            if($sizes && preg_match('/(\d+)x(\d+)/', $sizes, $sm)){ $w = intval($sm[1]); $h = intval($sm[2]); $s = max($w, $h); if($s <= 64) $score += 20; elseif($s <= 128) $score += 10; }
            $candidates[] = ['url'=>$abs, 'score'=>$score];
        }
    }
    usort($candidates, function($a,$b){ return $b['score'] <=> $a['score']; });
    return $candidates ? $candidates[0]['url'] : '';
}

function y_get_favicon($url){
    $host = parse_url($url, PHP_URL_HOST);
    if(!$host) return '';
    $key = 'y_favicon_v2_' . md5(strtolower($host));
    $cached = get_transient($key);
    if($cached !== false){
        if(is_string($cached) && strpos($cached, '/?y_fav=') === false && preg_match('~^https?://~i', $cached)){
            $prox = y_favicon_proxy_url($cached);
            set_transient($key, $prox, 12 * HOUR_IN_SECONDS);
            return $prox;
        }
        return $cached;
    }
    $resp = wp_remote_get($url, ['timeout' => 2, 'redirection' => 4, 'headers' => ['User-Agent' => 'Mozilla/5.0 YowaoTheme']]);
    if(!is_wp_error($resp)){
        $html = wp_remote_retrieve_body($resp);
        $icon = '';
        if($html){ $icon = y_pick_best_icon($html, $url); }
        if($icon){
            $icon = y_sanitize_href($icon); $icon = y_resolve_url($url, $icon);
            $hr = wp_remote_head($icon, ['timeout'=>2, 'redirection'=>3]);
            if(!is_wp_error($hr) && (int)wp_remote_retrieve_response_code($hr) >= 200 && (int)wp_remote_retrieve_response_code($hr) < 400){
                set_transient($key, y_favicon_proxy_url($icon), 12 * HOUR_IN_SECONDS);
                return y_favicon_proxy_url($icon);
            }
        }
    }
    $fallback = y_resolve_url($url, '/favicon.ico');
    $hr = wp_remote_head($fallback, ['timeout'=>2, 'redirection'=>3]);
    if(!is_wp_error($hr) && (int)wp_remote_retrieve_response_code($hr) >= 200 && (int)wp_remote_retrieve_response_code($hr) < 400){
        set_transient($key, y_favicon_proxy_url($fallback), 12 * HOUR_IN_SECONDS);
        return y_favicon_proxy_url($fallback);
    }
    // ★★★ 核心优化：移除 Google Favicon 回退，防止国内访问卡死 ★★★
    set_transient($key, '', 6 * HOUR_IN_SECONDS);
    return '';
}

function y_favicon_proxy_url($remote){
    return home_url('/?y_fav=' . rawurlencode($remote));
}

add_action('template_redirect', function(){
    if(isset($_GET['y_fav'])){
        $u = y_sanitize_href($_GET['y_fav']);
        if(!$u || !preg_match('~^https?://~i', $u)) { status_header(400); exit; }
        $hash = md5(strtolower($u));
        $up = wp_upload_dir();
        $dir = trailingslashit($up['basedir']) . 'yowao/favicons';
        if(!file_exists($dir)) wp_mkdir_p($dir);
        $exts = ['.ico'=>['image/x-icon','image/vnd.microsoft.icon'], '.png'=>['image/png'], '.jpg'=>['image/jpeg','image/jpg'], '.svg'=>['image/svg+xml']];
        foreach(array_keys($exts) as $e){
            $p = $dir . '/' . $hash . $e;
            if(file_exists($p)){
                $ctype = $exts[$e][0]; header('Content-Type: '.$ctype); header('Cache-Control: max-age=86400'); readfile($p); exit;
            }
        }
        $bp = wp_parse_url($u);
        $origin = ($bp && !empty($bp['scheme']) && !empty($bp['host'])) ? ($bp['scheme'].'://'.$bp['host']) : '';
        $resp = wp_remote_get($u, ['timeout'=>10, 'redirection'=>3, 'headers'=>['User-Agent'=>'Mozilla/5.0 YowaoTheme', 'Accept'=>'image/avif,image/webp,image/apng,image/*,*/*;q=0.8'] + ($origin? ['Referer'=>$origin]:[]) ]);
        if(is_wp_error($resp)) { status_header(404); exit; }
        $code = (int)wp_remote_retrieve_response_code($resp);
        if($code < 200 || $code >= 400){ status_header(404); exit; }
        $body = wp_remote_retrieve_body($resp);
        $ctype = wp_remote_retrieve_header($resp, 'content-type');
        $len = strlen($body);
        if($len <= 0 || $len > 1048576) { status_header(404); exit; }
        $ext = '.ico';
        $found = false;
        foreach($exts as $e => $types){ if($ctype && in_array(strtolower($ctype), $types, true)){ $ext = $e; $found = true; break; } }
        if(!$found && preg_match('/\.svg(\?|$)/i', $u)) { $ext = '.svg'; $ctype = 'image/svg+xml'; $found = true; }
        $path = $dir . '/' . $hash . $ext;
        file_put_contents($path, $body);
        header('Content-Type: '.($ctype ?: 'image/x-icon')); header('Cache-Control: max-age=86400'); echo $body; exit;
    }
});

function y_weather_icon_class($text){
    $t = strtolower($text);
    if(preg_match('/(clear|晴)/', $t)) return 'fas fa-sun text-yellow-300';
    if(preg_match('/(cloud|阴|多云)/', $t)) return 'fas fa-cloud text-slate-200';
    if(preg_match('/(rain|drizzle|雨)/', $t)) return 'fas fa-cloud-showers-heavy text-cyan-300';
    if(preg_match('/(thunder|storm|雷)/', $t)) return 'fas fa-cloud-bolt text-yellow-400';
    if(preg_match('/(snow|雪)/', $t)) return 'fas fa-snowflake text-blue-300';
    if(preg_match('/(mist|fog|haze|霾|雾)/', $t)) return 'fas fa-smog text-slate-300';
    if(preg_match('/(wind|风)/', $t)) return 'fas fa-wind text-slate-200';
    return 'fas fa-cloud text-slate-200';
}

function y_weather_from_local($city){
    $dir = get_template_directory() . '/weather/cache';
    if(!is_dir($dir)) return false;
    $key = 'y_weather_city_map_' . md5($city);
    $cid = get_transient($key);
    $try = function($file){
        if(!file_exists($file)) return false;
        $s = @file_get_contents($file); if(!$s) return false;
        $d = json_decode($s, true); if(!$d || !isset($d['weatherinfo'])) return false;
        $w = $d['weatherinfo'];
        $desc = $w['weather1'] ?? ($w['weather'] ?? ''); $temp = $w['temp1'] ?? ''; $name = $w['city'] ?? $city;
        if(!$temp && isset($w['temp']) && $w['temp'] !== '') $temp = $w['temp'] . '°C';
        return ['desc'=>$desc ?: $temp, 'text'=> trim($name . ' ' . $temp)];
    };
    if($cid){ $res = $try($dir . '/' . $cid . '.htm'); if($res) return $res; }
    $files = glob($dir . '/*.htm');
    $lc = strtolower($city);
    foreach($files as $f){
        $s = @file_get_contents($f); if(!$s) continue;
        $d = json_decode($s, true); if(!$d || !isset($d['weatherinfo'])) continue;
        $w = $d['weatherinfo'];
        $cn = isset($w['city']) ? $w['city'] : ''; $en = isset($w['city_en']) ? strtolower($w['city_en']) : '';
        if(($cn && (mb_stripos($city, $cn) !== false || mb_stripos($cn, $city) !== false)) || ($en && $en === $lc)){
            if(isset($w['cityid'])) set_transient($key, $w['cityid'], 12 * HOUR_IN_SECONDS);
            $desc = $w['weather1'] ?? ($w['weather'] ?? ''); $temp = $w['temp1'] ?? '';
            if(!$temp && isset($w['temp']) && $w['temp'] !== '') $temp = $w['temp'] . '°C';
            return ['desc'=>$desc ?: $temp, 'text'=> trim(($cn ?: $city) . ' ' . $temp)];
        }
    }
    return false;
}

function y_detect_city(){
    $c = get_transient('y_detect_city'); if($c) return $c;
    $c = '';
    if(function_exists('shortcode_exists') && shortcode_exists('useriploc')){ $r = trim(do_shortcode('[useriploc type="city"]')); if($r) $c = $r; }
    if(!$c){
        $resp = wp_remote_get('http://ip-api.com/json/?lang=zh-CN', ['timeout'=>2, 'redirection'=>2]);
        if(!is_wp_error($resp) && (int)wp_remote_retrieve_response_code($resp) === 200){
            $d = json_decode(wp_remote_retrieve_body($resp), true); if($d && !empty($d['city'])) $c = sanitize_text_field($d['city']);
        }
    }
    if(!$c) $c = '深圳';
    set_transient('y_detect_city', $c, 10 * MINUTE_IN_SECONDS);
    return $c;
}

// 7. 认证与安全核心 (★★★ 安全增强：增加 Nonce 校验 ★★★)
add_action('wp_ajax_nopriv_y_auth_v67', 'y_auth_fn'); add_action('wp_ajax_y_auth_v67', 'y_auth_fn');
function y_auth_fn(){ 
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    $u=sanitize_user($_POST['user']); $p=$_POST['pass']; 
    $user=wp_signon(['user_login'=>$u,'user_password'=>$p,'remember'=>true],false); 
    if(is_wp_error($user))wp_send_json_error(['msg'=>'账号或密码错误']); wp_send_json_success(); 
}

add_action('wp_ajax_nopriv_y_send_reg_code', 'y_send_reg_code_fn');
add_action('wp_ajax_y_send_reg_code', 'y_send_reg_code_fn');
function y_send_reg_code_fn(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    $ip = $_SERVER['REMOTE_ADDR'];
    $lock_key = 'y_ip_lock_' . md5($ip);
    if(get_transient($lock_key)) wp_send_json_error(['msg'=>'操作太频繁，请稍后再试']);
    if(!get_option('yowao_enable_email_reg', 1)) wp_send_json_error(['msg'=>'本站未开启邮箱验证，请直接注册']);
    $e = sanitize_email($_POST['email']);
    if(!is_email($e)) wp_send_json_error(['msg'=>'邮箱格式不正确']);
    if(email_exists($e)) wp_send_json_error(['msg'=>'该邮箱已被注册']);

    $code = rand(100000, 999999);
    set_transient('y_reg_code_' . md5($e), $code, 10 * 60);

    $subject = "注册验证码 - " . get_bloginfo('name');
    $body = "您好！\r\n\r\n您的注册验证码是：{$code}\r\n\r\n该验证码10分钟内有效，请勿泄露给他人。";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    if(wp_mail($e, $subject, $body, $headers)){
        set_transient($lock_key, 1, 60);
        wp_send_json_success(['msg'=>'验证码已发送']);
    } else {
        $err = get_transient('y_mail_last_error');
        wp_send_json_error(['msg'=> $err ? ('邮件发送失败：' . $err) : '邮件发送失败，请联系管理员检查SMTP配置']);
    }
}

add_action('wp_ajax_nopriv_y_register_v67', 'y_register_fn');
function y_register_fn(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    $enable_email = get_option('yowao_enable_email_reg', 1);
    $u = sanitize_user($_POST['user']); $p = $_POST['pass'];
    if(!preg_match('/^[a-zA-Z0-9]+$/', $u)) wp_send_json_error(['msg'=>'用户名仅允许英文或数字']);
    if(empty($u) || empty($p)) wp_send_json_error(['msg'=>'请填写完整信息']);
    if(username_exists($u)) wp_send_json_error(['msg'=>'用户名已被占用']);

    $e = '';
    if($enable_email){
        $e = sanitize_email($_POST['email']); $code = sanitize_text_field($_POST['code'] ?? '');
        if(email_exists($e)) wp_send_json_error(['msg'=>'邮箱已被注册']);
        if(empty($code)) wp_send_json_error(['msg'=>'请输入验证码']);
        $cached_code = get_transient('y_reg_code_' . md5($e));
        if(!$cached_code || $cached_code != $code) wp_send_json_error(['msg'=>'验证码错误或已过期']);
        delete_transient('y_reg_code_' . md5($e));
    } else {
        $e = $u . '@no-email.' . $_SERVER['HTTP_HOST'];
    }

    $user_id = wp_create_user($u, $p, $e);
    if(is_wp_error($user_id)) { wp_send_json_error(['msg' => $user_id->get_error_message()]); } 
    else { wp_set_current_user($user_id); wp_set_auth_cookie($user_id); wp_send_json_success(['msg'=>'注册成功', 'action'=>'reload']); }
}

add_filter('wp_authenticate_user', function($user, $password){
    if($user instanceof WP_User){
        if(get_user_meta($user->ID, 'yowao_user_status', true) === 'inactive'){ return new WP_Error('not_verified', '账号未激活，请检查邮箱完成验证'); }
    }
    return $user;
}, 10, 2);

add_action('template_redirect', function(){
    if(isset($_GET['y_verify']) && isset($_GET['code'])){
        $uid = intval($_GET['y_verify']); $code = sanitize_text_field($_GET['code']);
        $db_code = get_user_meta($uid, 'yowao_activation_key', true);
        if($db_code && $db_code === $code){
            delete_user_meta($uid, 'yowao_user_status'); delete_user_meta($uid, 'yowao_activation_key');
            wp_set_current_user($uid); wp_set_auth_cookie($uid); wp_redirect(home_url()); exit;
        } else { wp_die('激活链接无效或已过期。', '激活失败'); }
    }
});

add_action('wp_ajax_nopriv_y_reset_pwd', 'y_reset_pwd_fn');
add_action('wp_ajax_y_reset_pwd', 'y_reset_pwd_fn');
function y_reset_pwd_fn(){
    check_ajax_referer('yowao_frontend_action', 'security'); // 安全校验
    $input = trim($_POST['user_input']);
    if(empty($input)) wp_send_json_error(['msg'=>'请输入账号或邮箱']);
    $user = get_user_by('email', $input);
    if(!$user) $user = get_user_by('login', $input);
    if(!$user) wp_send_json_error(['msg'=>'用户不存在']);
    
    $key = get_password_reset_key($user);
    if(is_wp_error($key)) wp_send_json_error(['msg'=>'系统错误，无法重置']);
    
    $link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
    $subject = "重置密码 - " . get_bloginfo('name');
    $msg = "您正在请求重置密码。\r\n\r\n请点击以下链接重置密码：\r\n" . $link;
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    if(wp_mail($user->user_email, $subject, $msg, $headers)){ wp_send_json_success(['msg'=>'重置链接已发送到您的邮箱']); } 
    else { 
        $err = get_transient('y_mail_last_error');
        wp_send_json_error(['msg'=> $err ? ('邮件发送失败：' . $err) : '邮件发送失败，请检查后台SMTP配置']); 
    }
}

add_action('wp_mail_failed', function($wp_error){
    if(is_wp_error($wp_error)){ set_transient('y_mail_last_error', sanitize_text_field($wp_error->get_error_message()), 120); }
});

add_action('wp_footer', function(){
    $code = get_option('yowao_analytics_code');
    if($code) echo $code;
});