<?php
/**
 * Yowao Navigation Pro v1.0.0 - 要哇棱镜旗舰版
 * * @package Yowao
 * @author 熊哥
 * @link https://www.yowao.com
 * @version 1.0.0
 */
$u = wp_get_current_user(); 
$login = is_user_logged_in();
$uri = get_template_directory_uri(); 
$root = get_template_directory();

$res_vue = $uri . '/assets/js/vue.js'; 
$res_tw = $uri . '/assets/js/tailwind.js'; 
$res_fa = $uri . '/assets/css/fontawesome/css/all.min.css';

// [关键] 生成安全凭证，配合 functions.php v71 使用
$frontend_nonce = wp_create_nonce('yowao_frontend_action');

// 背景逻辑
$custom_bg = $login ? get_user_meta($u->ID, 'yowao_bg', true) : '';
$final_bg = '';
if ($custom_bg) {
    $final_bg = $custom_bg;
} else {
    $bg_files = glob($root . '/assets/img/bg/*.{jpg,png,jpeg,webp}', GLOB_BRACE);
    if ($bg_files && count($bg_files) > 0) {
        $random_file = $bg_files[array_rand($bg_files)];
        $final_bg = $uri . '/assets/img/bg/' . basename($random_file);
    } else {
        $final_bg = 'https://images.unsplash.com/photo-1494500764479-0c8f2919a3d8?q=80&w=2070';
    }
}

$avatar = $login ? (get_user_meta($u->ID,'yowao_avatar',true) ?: get_avatar_url($u->ID)) : '';
$bookmarks = $login ? get_yowao_list('yowao_bookmark') : [];
$bookmarks = array_map(function($x){ $x['fav'] = $x['img'] ? '' : y_get_favicon($x['url']); return $x; }, $bookmarks);

$apps = get_yowao_list('yowao_app');
foreach ($apps as $k => $v) {
    $apps[$k]['fav'] = $v['img'] ? '' : y_get_favicon($v['url']);
}

$search_engines = get_option('yowao_search_config_v66', [
    'bing' => ['name'=>'必应','icon'=>'','order'=>1,'url'=>'https://www.bing.com/search?q='],
    'baidu' => ['name'=>'百度','icon'=>'','order'=>2,'url'=>'https://www.baidu.com/s?wd='],
    'google' => ['name'=>'谷歌','icon'=>'','order'=>3,'url'=>'https://www.google.com/search?q=']
]);
uasort($search_engines, function($a,$b){ return $a['order']-$b['order']; });

$footer_text = get_option('yowao_footer_text', '© 2024 Yowao Portal. All Rights Reserved.');
$icp_num = get_option('yowao_icp_num', '');
$icp_link = get_option('yowao_icp_link', 'https://beian.miit.gov.cn/');
$logo_url = get_option('yowao_logo_url', '');
$seo_desc = get_option('yowao_seo_desc', '');
$seo_keywords = get_option('yowao_seo_keywords', '');
$footer_links = get_option('yowao_footer_links', '');
$enable_email_reg = get_option('yowao_enable_email_reg', 1);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php bloginfo('name'); ?></title>
    <?php if($seo_desc): ?><meta name="description" content="<?php echo esc_attr($seo_desc); ?>"><?php endif; ?>
    <?php if($seo_keywords): ?><meta name="keywords" content="<?php echo esc_attr($seo_keywords); ?>"><?php endif; ?>
    <script>
        window.YowaoConfig = {
            ajaxUrl: "<?php echo admin_url('admin-ajax.php'); ?>",
            nonce: "<?php echo $frontend_nonce; ?>",
            isLogin: <?php echo $login ? 'true' : 'false'; ?>
        };
    </script>
    <link href="<?php echo $res_fa; ?>" rel="stylesheet">
    <script src="<?php echo $res_tw; ?>"></script>
    <script src="<?php echo $res_vue; ?>"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', 'sans-serif'] },
                    animation: { 'float': 'float 6s ease-in-out infinite', 'pulse-glow': 'pulseGlow 3s infinite', 'shimmer': 'shimmer 2.5s infinite linear' },
                    keyframes: {
                        float: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-6px)' } },
                        pulseGlow: { '0%, 100%': { boxShadow: '0 0 0 0 rgba(6, 182, 212, 0)' }, '50%': { boxShadow: '0 0 25px 5px rgba(6, 182, 212, 0.4)' } },
                        shimmer: { '0%': { opacity: '0.8' }, '50%': { opacity: '1', textShadow: '0 0 20px rgba(6, 182, 212, 0.6)' }, '100%': { opacity: '0.8' } }
                    }
                }
            }
        }
    </script>
    <style>
        body { background: #fdfdfd; color: #1e293b; margin:0; overflow-x: hidden; -webkit-tap-highlight-color: transparent; }
        .bg-layer { position: fixed; inset: 0; z-index: -2; background: url('<?php echo $final_bg; ?>') center/cover no-repeat fixed; transition: 1s; }
        .grid-overlay { position: fixed; inset: 0; z-index: -1; pointer-events: none; background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 50px 50px; }
        .glass-card {
            background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.4); border-top: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); border-radius: 1.5rem; 
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .glass-card:hover { background: rgba(255, 255, 255, 0.35); border-color: rgba(255, 255, 255, 0.9); transform: translateY(-4px); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15); }
        .logo-core::before { content: ''; position: absolute; inset: -5px; background: radial-gradient(circle, rgba(6,182,212,0.8) 0%, transparent 70%); z-index: -1; animation: pulse-glow 3s infinite; }
        .time-flow { font-weight: 900; letter-spacing: -4px; animation: shimmer 3s infinite ease-in-out; text-shadow: 0 0 20px rgba(6, 182, 212, 0.35), 0 4px 10px rgba(0,0,0,0.1); }
        .search-bar-glow { background: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255,255,255,0.3); border-radius: 99px; height: 72px; display: flex; align-items: center; position: relative; z-index: 50; }
        .search-bar-glow:focus-within { background: rgba(255, 255, 255, 0.45); border-color: rgba(255, 255, 255, 0.9); box-shadow: 0 0 40px rgba(6, 182, 212, 0.3); }
        .engine-select { position: absolute; top: 110%; left: 0; background: #fff; color: #333; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 140px; z-index: 100; animation: fadeIn 0.2s; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(5px)} to{opacity:1;transform:translateY(0)} }
        .add-card { border: 2px dashed rgba(255,255,255,0.4); border-radius: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; }
        .add-card:hover { background: rgba(255,255,255,0.15); border-color: #fff; }
        .hero-layout { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 768px) { 
            .hero-layout { grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
            .hero-layout .web-card:first-child { grid-column: span 2; }
        }
        .def-icon { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #06b6d4, #3b82f6); color: white; font-weight: bold; border-radius: 0.75rem; }
        .del-btn { position: absolute; top: 5px; right: 5px; width: 20px; height: 20px; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; opacity: 0; transition: 0.2s; background: rgba(0,0,0,0.3); }
        .glass-card:hover .del-btn { opacity: 1; top: 8px; right: 8px; }
        .site-footer { text-align: center; padding: 20px; color: rgba(255,255,255,0.6); font-size: 12px; position: absolute; bottom: 0; width: 100%; z-index: 10; }
        [v-cloak] { display: none !important; }
        .web-overlay { position:absolute; inset:0; border-radius:1.5rem; mix-blend-mode:screen; 
            --web-x: 80%; --web-y: 20%;
            background-image:
            repeating-radial-gradient(circle at var(--web-x) var(--web-y), rgba(255,255,255,0.22) 0 3px, transparent 3px 22px),
            repeating-linear-gradient(60deg, rgba(255,255,255,0.12) 0 3px, transparent 3px 22px),
            repeating-linear-gradient(120deg, rgba(255,255,255,0.12) 0 3px, transparent 3px 22px);
            filter: contrast(1.05) saturate(1.1);
        }
        .web-depth { position:absolute; inset:0; border-radius:1.5rem; pointer-events:none; mix-blend-mode:screen;
            --web-x: 80%; --web-y: 20%;
            background:
            radial-gradient(circle at var(--web-x) var(--web-y), rgba(255,255,255,0.35) 0 60px, rgba(255,255,255,0) 120px);
            filter: brightness(1.2) drop-shadow(0 8px 16px rgba(255,255,255,0.25));
        }
        .file-label { display: flex; align-items: center; justify-content: center; width: 100%; padding: 10px; border: 1px dashed rgba(255,255,255,0.3); border-radius: 0.75rem; cursor: pointer; color: rgba(255,255,255,0.7); font-size: 13px; transition: 0.2s; }
        .file-label:hover { border-color: #22d3ee; color: white; background: rgba(255,255,255,0.05); }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="antialiased text-white selection:bg-cyan-500 selection:text-white">

<div id="app" v-cloak @click="closeDropdowns" class="relative min-h-screen flex flex-col pb-20">
    <div class="bg-layer"></div>
    <div class="grid-overlay"></div>

    <header class="w-full max-w-[1400px] mx-auto px-6 py-6 flex justify-between items-center z-20">
        <div class="flex items-center gap-4 group cursor-pointer select-none" @click="location.reload()">
            <div class="logo-core w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-400 flex items-center justify-center text-white shadow-2xl animate-float relative">
                <?php if($logo_url): ?>
                    <img src="<?php echo $logo_url; ?>" class="w-12 h-12 rounded-xl object-cover">
                <?php else: ?>
                    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/logo.png'); ?>" alt="Logo" class="w-12 h-12 rounded-xl object-cover">
                <?php endif; ?>
            </div>
            <div class="hidden md:block">
                <h1 class="text-2xl font-black tracking-wide drop-shadow-md font-sans">YOWAO</h1>
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest opacity-90"><span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span> Navigation Pro</div>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <?php 
                $weather_city = function_exists('y_detect_city') ? y_detect_city() : 'Shenzhen';
                $local = function_exists('y_weather_from_local') ? y_weather_from_local($weather_city) : false;
                if($local && !empty($local['text'])){
                    $w_icon = y_weather_icon_class($local['desc']);
                    echo '<div class="inline-flex items-center h-[30px] gap-2 glass-card px-3 rounded-full text-[11px] font-medium backdrop-blur-md text-white/80"><i class="'.$w_icon.' text-xs opacity-80"></i>' . esc_html($local['text']) . '</div>'; 
                } elseif(function_exists('shortcode_exists') && shortcode_exists('weather_widget_wp_location')) {
                    $w_display = do_shortcode('[weather_widget_wp_location city="' . esc_attr($weather_city) . '" title="' . esc_attr($weather_city) . '" style="style-minimal" units="C" desc=0 icon=0 date=0 wind=0 max=0 min=0 css_class="inline-flex items-center gap-2 whitespace-nowrap" css_style="display:inline-flex;align-items:center;gap:.35rem;white-space:nowrap;" ]');
                    echo '<div class="inline-flex items-center h-[30px] gap-2 glass-card px-3 rounded-full text-[11px] font-medium backdrop-blur-md text-white/80"><i class="fas fa-cloud text-xs opacity-80"></i>' . $w_display . '</div>'; 
                } else { ?>
                    <div class="hidden md:flex items-center h-[30px] gap-2 glass-card px-3 rounded-full text-xs font-bold backdrop-blur-md text-slate-100">
                        <i class="fas fa-cloud-sun text-yellow-300"></i> 24° 深圳
                    </div>
            <?php } ?>
            <?php if($login): ?>
                <button @click.stop="modals.user = true" class="glass-card h-[30px] px-3 rounded-full inline-flex items-center gap-2 text-[11px] font-medium hover:bg-white/30 transition">
                    <img src="<?php echo $avatar; ?>" class="w-5 h-5 rounded-full border border-white/50">
                    <span class="max-w-[60px] truncate"><?php echo $u->display_name; ?></span>
                </button>
            <?php else: ?>
                <button @click.stop="modals.auth = true" class="glass-card h-[30px] px-3 rounded-full text-[11px] font-medium hover:bg-white/30 transition">登录 / 注册</button>
            <?php endif; ?>
        </div>
    </header>

    <main class="w-full max-w-[1200px] mx-auto px-4 flex-1 flex flex-col items-center z-10">
        <div class="text-center mb-16 mt-8 animate-float w-full">
            <h2 class="text-7xl md:text-9xl font-medium tracking-tighter drop-shadow-lg mb-2 font-sans time-flow">{{ time }}</h2>
            <p class="text-lg opacity-90 font-medium mb-10 drop-shadow">{{ date }} {{ weekday }} {{ lunarText }} {{ ganzhiText }}</p>

            <div class="w-full max-w-3xl mx-auto relative group z-30">
                <div class="absolute -inset-1 bg-gradient-to-r from-cyan-400 via-blue-500 to-purple-500 rounded-full blur opacity-20 group-hover:opacity-50 transition duration-700"></div>
                <div class="relative search-bar-glow transition-all duration-300" @click.stop>
                    <div class="relative pl-6 pr-4 border-r border-white/20 cursor-pointer h-full flex items-center max-[492px]:pl-3 max-[492px]:pr-2" @click="showEngines = !showEngines">
                        <div class="flex items-center gap-2 font-bold hover:text-cyan-300 transition text-white">
                            <img v-if="engines[engine].icon" :src="engines[engine].icon" class="w-5 h-5 rounded-full">
                            <i v-else class="fas fa-search text-lg"></i>
                            <span class="hidden md:inline text-sm opacity-90 ml-1">{{ engines[engine].name }}</span>
                            <i class="fas fa-angle-down text-xs opacity-50 ml-1"></i>
                        </div>
                    <div v-if="showEngines" class="engine-select">
                            <div v-for="(v, k) in engines" @click.stop="selectEngine(k)" class="px-4 py-2 hover:bg-blue-50 flex items-center gap-3 cursor-pointer text-slate-700 transition">
                                <img v-if="v.icon" :src="v.icon" class="w-4 h-4 rounded-full">
                                <span v-else class="text-blue-500 font-bold text-xs">{{v.name.substring(0,1)}}</span>
                                <span class="text-xs font-bold">{{v.name}}</span>
                            </div>
                        </div>
                    </div>
                    <input v-model="query" @keyup.enter="doSearch" type="text" placeholder="探索无限可能..." class="flex-1 bg-transparent px-6 text-xl text-white placeholder-white/40 outline-none font-medium tracking-wide max-[492px]:px-4 max-[492px]:text-base max-[375px]:px-3 max-[375px]:text-sm">
                    <div class="pr-2 flex-shrink-0 ml-2"><button @click="doSearch" class="w-12 h-12 rounded-full bg-white/20 hover:bg-cyan-500 text-white flex items-center justify-center transition-all duration-300 hover:scale-110 max-[492px]:w-10 max-[492px]:h-10 max-[375px]:w-9 max-[375px]:h-9"><i class="fas fa-arrow-right text-lg max-[492px]:text-base max-[375px]:text-sm"></i></button></div>
                </div>
            </div>
        </div>

        <?php if($login): ?>
        <div class="w-full mb-12">
            <div class="flex items-center gap-3 mb-6 pl-1"><div class="w-1.5 h-6 bg-cyan-400 rounded-full shadow-[0_0_10px_#06b6d4]"></div><h3 class="font-bold text-xl text-shadow tracking-wide">我的收藏</h3><span class="text-[10px] bg-cyan-500/80 px-2 py-0.5 rounded text-white font-bold backdrop-blur">Private</span><span class="text-[13px] text-white/60 ml-2">My Favorites</span></div>
            
            <div class="overflow-hidden relative w-full mb-3 cursor-pointer" 
                 @mousedown="bmOnDown" @mousemove="bmOnMove" @mouseup="bmOnUp" @mouseleave="bmOnUp"
                 @touchstart="bmOnTouchStart" @touchmove="bmOnTouchMove" @touchend="bmOnTouchEnd">
                <div class="flex transition-transform duration-500 ease-[cubic-bezier(0.25,0.8,0.25,1)]" 
                     :style="{ transform: `translateX(${-bmCurrentPage * 100}%)` }">
                    <div v-for="(pageBms, pIdx) in bmChunked" :key="pIdx" class="w-full flex-shrink-0 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6 p-1">
                        <template v-for="b in pageBms">
                            <div v-if="b.__add" @click="modals.addBook = true" class="add-card h-28 text-white/60 hover:text-white group relative">
                                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center group-hover:bg-cyan-500 transition mb-2"><i class="fas fa-plus"></i></div>
                                <span class="text-xs font-bold">添加链接</span>
                            </div>
                            <a v-else :href="b.url" target="_blank" @click="clickBookmark(b.id)" class="glass-card h-28 flex flex-col items-center justify-center p-4 group cursor-pointer relative overflow-hidden">
                                <div class="del-btn" @click.stop.prevent="deleteBookmark(b.id)"><i class="fas fa-times"></i></div>
                                <div class="w-12 h-12 rounded-xl bg-white/90 shadow-lg flex items-center justify-center text-slate-700 text-xl mb-3 group-hover:scale-110 transition duration-300">
                                    <img v-if="b.img || b.fav" :src="b.img ? b.img : b.fav" class="w-full h-full object-contain rounded-lg">
                                    <div v-else class="def-icon text-base">{{ b.name.substring(0,1) }}</div>
                                </div>
                                <span class="text-sm font-bold text-white group-hover:text-cyan-300 transition shadow-black/50 drop-shadow-md truncate w-full text-center" :title="b.name">{{ b.name }}</span>
                            </a>
                        </template>
                        <div v-if="pageBms.length < bmPageSize" v-for="k in (bmPageSize - pageBms.length)" class="hidden md:block"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-center gap-2 mt-2" v-if="bmTotalPages > 1">
                <template v-for="d in bmPageDots">
                    <span v-if="d===-1" class="w-2 h-2 bg-white/20 rounded-full"></span>
                    <span v-else class="rounded-full transition-all duration-300 cursor-pointer"
                          :class="bmCurrentPage === d ? 'w-6 h-2 bg-cyan-400 shadow-[0_0_10px_#22d3ee]' : 'w-2 h-2 bg-white/30 hover:bg-white/60'"
                          @click="bmGoPage(d)"></span>
                </template>
            </div>
        </div>
        <?php endif; ?>
        <div class="w-full mb-12">
            <div class="flex items-center gap-3 mb-4 pl-1">
                <div class="w-1.5 h-6 bg-blue-500 rounded-full shadow-[0_0_10px_#3b82f6]"></div>
                <h3 class="font-bold text-xl text-shadow tracking-wide">常用推荐</h3><span class="text-[13px] text-white/60 ml-2">Recommended</span>
            </div>
            
            <div class="hero-layout mb-6">
                <?php $card_apps = array_values(array_filter($apps, function($it){ return isset($it['style']) && $it['style'] === 'card'; })); 
                $top_cards = array_slice($card_apps, 0, 2);
                foreach($top_cards as $idx => $item): ?>
                <a href="<?php echo $item['url']; ?>" target="_blank" class="glass-card web-card rounded-3xl p-6 relative overflow-hidden group hover:bg-slate-900/40 transition flex flex-col justify-between h-[200px] backdrop-blur-xl border border-white/20 shadow-[0_0_24px_rgba(99,102,241,0.25)] hover:shadow-[0_0_36px_rgba(59,130,246,0.35)] hover:scale-[1.02]">
                    <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-fuchsia-500/25 via-cyan-400/25 to-blue-600/25 blur-xl opacity-40 pointer-events-none"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600/40 via-cyan-500/20 to-purple-500/30 opacity-60 group-hover:opacity-80 transition mix-blend-screen"></div>
                    <?php if($idx!==0): ?>
                    <div class="absolute inset-0 web-overlay opacity-70" style="background: repeating-linear-gradient(0deg, rgba(255,255,255,0.08) 0, rgba(255,255,255,0.08) 1px, transparent 1px, transparent 20px), repeating-linear-gradient(90deg, rgba(255,255,255,0.08) 0, rgba(255,255,255,0.08) 1px, transparent 1px, transparent 20px);"></div>
                    <?php endif; ?>
                    <?php if($idx===0): ?>
                    <div class="absolute right-0 top-0 text-9xl text-white/10 -mr-5 -mt-5 group-hover:rotate-12 group-hover:opacity-30 transition transform"><i class="fas fa-rocket"></i></div>
                    <?php endif; ?>

                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center backdrop-blur text-2xl font-bold">
                                    <?php if($item['img']): ?><img src="<?php echo $item['img']; ?>" class="w-full h-full object-cover rounded-lg shadow-lg"><?php else: ?><div class="def-icon"><?php echo mb_substr($item['name'],0,1); ?></div><?php endif; ?>
                                </div>
                                <h3 class="text-xl font-bold group-hover:text-cyan-300 transition truncate flex-1 min-w-0"><?php echo $item['name']; ?></h3>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="inline-flex items-center h-[20px] text-[10px] bg-white/10 px-2 rounded-full backdrop-blur cursor-pointer select-none" @click.stop.prevent="likeApp(<?php echo $item['id']; ?>)"><i class="fas fa-heart mr-1 transition transform" :class="heartPulseMap[<?php echo $item['id']; ?>] ? 'text-red-400 scale-125' : (likedMap[<?php echo $item['id']; ?>] ? 'text-red-400' : 'text-red-400')"></i>{{ topLikesMap[<?php echo $item['id']; ?>] }}</span>
                                <?php if($item['label']): ?>
                                    <span class="inline-flex items-center h-[20px] <?php echo $idx===0 ? 'bg-blue-600' : 'bg-orange-500 text-black'; ?> text-white text-[10px] font-bold px-2 rounded-full shadow-lg"><?php echo $item['label']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-lg text-white/80 w-full line-clamp-3 break-words whitespace-normal"><?php echo $item['desc'] ?: '暂无描述'; ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php $more_card_apps = array_slice($card_apps, 2); if(!empty($more_card_apps)): ?>
            <div class="overflow-hidden relative w-full mb-3 cursor-pointer"
                 @mousedown="onDownCard" @mousemove="onMoveCard" @mouseup="onUpCard" @mouseleave="onUpCard"
                 @touchstart="onTouchStartCard" @touchmove="onTouchMoveCard" @touchend="onTouchEndCard">
                <div class="flex transition-transform duration-500 ease-[cubic-bezier(0.25,0.8,0.25,1)]" :style="cardSliderInlineStyle">
                    <div v-for="(pageCards, cpIdx) in cardChunkedApps" :key="cpIdx" class="w-full flex-shrink-0 grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 p-1">
                        <a v-for="c in pageCards" :href="c.url" target="_blank" class="glass-card web-card rounded-3xl p-6 relative overflow-hidden group hover:bg-slate-900/40 transition flex flex-col justify-between h-[200px] backdrop-blur-xl border border-white/20">
                            <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-fuchsia-500/25 via-cyan-400/25 to-blue-600/25 blur-xl opacity-40 pointer-events-none"></div>
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/40 via-cyan-500/20 to-purple-500/30 opacity-60 group-hover:opacity-80 transition mix-blend-screen"></div>
                            <div class="relative z-10">
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center backdrop-blur text-2xl font-bold">
                                            <img v-if="c.img" :src="c.img" class="w-full h-full object-cover rounded-lg shadow-lg">
                                            <div v-else class="def-icon">{{ c.name.substring(0,1) }}</div>
                                        </div>
                                        <h3 class="text-xl font-bold group-hover:text-cyan-300 transition truncate hidden md:block flex-1 min-w-0">{{ c.name }}</h3>
                                    </div>
                                    <div class="flex flex-col md:flex-row items-start md:items-center gap-1 md:gap-1">
                                        <span class="inline-flex items-center h-[20px] text-[10px] bg-white/10 px-2 rounded-full backdrop-blur cursor-pointer select-none" @click.stop.prevent="likeApp(c.id)"><i class="fas fa-heart mr-1 transition transform" :class="heartPulseMap[c.id] ? 'text-red-400 scale-125' : (likedMap[c.id] ? 'text-red-400' : 'text-red-400')"></i>{{ topLikesMap[c.id] }}</span>
                                    </div>
                                </div>
                                <h3 class="text-base font-bold text-white/90 mb-1 truncate md:hidden">{{ c.name }}</h3>
                                <p class="text-sm md:text-lg text-white/80 w-full line-clamp-3 break-words whitespace-normal">{{ c.desc || '暂无描述' }}</p>
                            </div>
                        </a>
                        <div v-if="pageCards.length < cardPageSize" v-for="k in (cardPageSize - pageCards.length)" class="hidden md:block"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-center gap-2 mt-2 mb-2" v-if="cardTotalPages > 1">
                <template v-for="d in cardPageDots">
                    <span v-if="d===-1" class="w-2 h-2 bg-white/20 rounded-full"></span>
                    <span v-else class="rounded-full transition-all duration-300 cursor-pointer" :class="cardCurrentPage === d ? 'w-6 h-2 bg-cyan-400 shadow-[0_0_10px_#22d3ee]' : 'w-2 h-2 bg-white/30 hover:bg-white/60'" @click="cardGoPage(d)"></span>
                </template>
            </div>
            <?php endif; ?>

            <div class="overflow-hidden relative w-full mb-3 cursor-pointer" 
                 @mousedown="onDown" @mousemove="onMove" @mouseup="onUp" @mouseleave="onUp"
                 @touchstart="onTouchStart" @touchmove="onTouchMove" @touchend="onTouchEnd">
                <div class="flex transition-transform duration-500 ease-[cubic-bezier(0.25,0.8,0.25,1)]" :style="sliderInlineStyle">
                    <div v-for="(pageApps, pIdx) in chunkedApps" :key="pIdx" class="w-full flex-shrink-0 grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 p-1">
                        <a v-for="a in pageApps" :href="a.url" target="_blank" class="glass-card p-2 md:p-3 rounded-xl md:rounded-2xl flex items-center gap-2 md:gap-3 group cursor-pointer hover:bg-white/20 h-[64px] md:h-[72px]">
                            <div class="w-10 h-10 rounded-lg overflow-hidden flex items-center justify-center text-xl group-hover:scale-105 transition flex-shrink-0">
                                <img v-if="a.img || a.fav" :src="a.img ? a.img : a.fav" class="w-full h-full object-contain scale-105">
                                <div v-else class="def-icon text-xs">{{ a.name.substring(0,1) }}</div>
                            </div>
                            <span class="text-xs md:text-sm font-bold text-white group-hover:text-cyan-300 w-full text-center md:text-left truncate" :title="a.name">{{ a.name }}</span>
                        </a>
                        <div v-if="pageApps.length < pageSize" v-for="k in (pageSize - pageApps.length)" class="hidden md:block"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-center gap-2 mt-2" v-if="totalPages > 1">
                <template v-for="d in pageDots">
                    <span v-if="d===-1" class="w-2 h-2 bg-white/20 rounded-full"></span>
                    <span v-else class="rounded-full transition-all duration-300 cursor-pointer" :class="currentPage === d ? 'w-6 h-2 bg-cyan-400 shadow-[0_0_10px_#22d3ee]' : 'w-2 h-2 bg-white/30 hover:bg-white/60'" @click="goPage(d)"></span>
                </template>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <?php if($icp_num): ?><span><a href="<?php echo esc_url($icp_link ?: 'https://beian.miit.gov.cn/'); ?>" target="_blank" class="hover:text-white"><?php echo $icp_num; ?></a></span><?php endif; ?>
        <?php if($footer_links) echo ' · ' . $footer_links; ?>
        <div class="mt-1 opacity-50 scale-90">Powered by Yowao Navigation Pro v1.0.0</div>
    </footer>

    <div v-if="modals.user" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 p-4" @click.self="modals.user=false">
        <div class="bg-white/10 border border-white/20 rounded-3xl p-8 w-full max-w-[380px] shadow-2xl relative text-center backdrop-blur-xl">
            <div class="relative w-24 h-24 mx-auto mb-4 group cursor-pointer" @click="triggerMedia('avatar')">
                <img src="<?php echo $avatar; ?>" class="w-full h-full rounded-full border-4 border-white/20 object-cover group-hover:border-cyan-400 transition">
                <div class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition"><i class="fas fa-camera"></i></div>
            </div>
            <h3 class="text-xl font-bold mb-6 text-white"><?php echo $u->display_name; ?></h3>
            <div class="space-y-3">
                <button @click="triggerMedia('bg')" class="w-full py-3 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl font-bold text-white shadow-lg hover:shadow-cyan-500/30 transition transform hover:scale-[1.02] flex items-center justify-center gap-2"><i class="fas fa-image"></i> 自定义背景 (上传)</button>
                <button @click="profileAction('reset')" class="w-full py-2 text-sm text-gray-300 hover:text-white transition flex items-center justify-center gap-1"><i class="fas fa-undo"></i> 恢复默认背景</button>
                <hr class="border-white/10 my-4">
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="block w-full py-3 bg-red-500/20 text-red-400 rounded-xl font-bold hover:bg-red-500 hover:text-white transition">退出登录</a>
            </div>
        </div>
    </div>

    <div v-if="modals.auth" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 p-4" @click.self="modals.auth=false">
        <div class="bg-slate-900 border border-white/10 rounded-3xl p-8 w-full max-w-[400px] shadow-2xl relative text-center text-white transition-all duration-300">
            <h3 class="text-2xl font-bold mb-8 cursor-default" v-if="authMode!=='reset'">
                <span :class="{ 'opacity-40': authMode!=='login' }" class="cursor-pointer hover:opacity-100 transition" @click="authMode='login'">登录</span> 
                <span class="opacity-20 mx-2">/</span> 
                <span :class="{ 'opacity-40': authMode!=='register' }" class="cursor-pointer hover:opacity-100 transition" @click="authMode='register'">注册</span>
            </h3>
            <h3 class="text-2xl font-bold mb-8" v-else>重置密码</h3>
            <div class="space-y-5">
                <div v-if="authMode!=='reset'">
                    <input v-model="form.user" @input="cleanUser" placeholder="用户名 (仅限英文/数字)" class="w-full p-4 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-cyan-500 text-white transition mb-5">
                    <div v-if="authMode==='register' && regConfig.email" class="mb-5 space-y-5">
                        <div class="flex gap-3">
                            <input v-model="form.email" type="email" placeholder="电子邮箱" class="flex-1 p-4 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-cyan-500 text-white transition w-full">
                            <button type="button" @click="sendCode" :disabled="countdown > 0" class="w-[100px] bg-white/10 hover:bg-white/20 rounded-xl text-xs font-bold transition disabled:opacity-50 disabled:cursor-not-allowed">{{ countdown > 0 ? countdown + 's' : '获取验证码' }}</button>
                        </div>
                        <input v-model="form.code" type="text" placeholder="输入验证码" class="w-full p-4 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-cyan-500 text-white transition">
                    </div>
                    <input v-model="form.pass" type="password" placeholder="密码" class="w-full p-4 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-cyan-500 text-white transition mb-5" @keyup.enter="authAction">
                    <button @click="authAction" class="w-full py-4 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl font-bold shadow-lg hover:shadow-cyan-500/30 transition transform hover:scale-[1.02]">{{ authMode === 'login' ? '立即登录' : '注册账号' }}</button>
                </div>
                <div v-else>
                    <p class="text-sm text-gray-400 mb-4 text-left">请输入您的用户名或注册邮箱，我们将发送重置链接给您。</p>
                    <input v-model="form.resetInput" placeholder="用户名 / 邮箱" class="w-full p-4 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-cyan-500 text-white transition mb-5">
                    <button @click="resetAction" class="w-full py-4 bg-cyan-600 rounded-xl font-bold shadow-lg hover:bg-cyan-500 transition">发送重置链接</button>
                    <div class="mt-4 text-sm text-gray-400 cursor-pointer hover:text-white" @click="authMode='login'"><i class="fas fa-arrow-left"></i> 返回登录</div>
                </div>
                <div v-if="authMode!=='reset'" class="flex items-center justify-between text-xs text-gray-400 mt-4 px-2">
                    <span v-if="authMode==='login'" class="cursor-pointer hover:text-white" @click="authMode='register'">没有账号？去注册</span>
                    <span v-else class="cursor-pointer hover:text-white" @click="authMode='login'">已有账号？去登录</span>
                    <span class="cursor-pointer hover:text-white" @click="authMode='reset'">忘记密码?</span>
                </div>
            </div>
        </div>
    </div>

    <div v-if="modals.addBook" class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 p-4" @click.self="modals.addBook=false">
        <div class="bg-slate-800 border border-white/10 rounded-3xl p-8 w-full max-w-[400px] shadow-2xl relative text-center text-white">
            <h3 class="text-xl font-bold mb-6">新增收藏</h3>
            <div class="space-y-4">
                <input v-model="bookForm.title" @input="cleanBookTitle" placeholder="网站名称" class="w-full p-3 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-cyan-500 text-white">
                <input v-model="bookForm.url" placeholder="跳转链接 (https://...)" class="w-full p-3 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-cyan-500 text-white">
                <label class="file-label relative overflow-hidden group">
                    <span v-if="!bookImgFile" class="flex items-center gap-2"><i class="fas fa-cloud-upload-alt"></i> 上传图标 (非必选)</span>
                    <span v-else class="text-cyan-400 font-bold truncate">{{ bookImgFile.name }}</span>
                    <input type="file" accept=".jpg,.png,.gif,.webp,.ico" @change="onBookImgChange" class="absolute inset-0 opacity-0 cursor-pointer">
                </label>
                <button @click="addBookmarkAction" class="w-full py-3 bg-cyan-500 hover:bg-cyan-400 rounded-xl font-bold transition mt-2">确认添加</button>
            </div>
        </div>
    </div>
</div>

<script>
    const { createApp, ref, computed, onMounted } = Vue;
    createApp({
        setup() {
            // [安全] 获取全局配置 (nonce)
            const ajaxUrl = window.YowaoConfig.ajaxUrl;
            const nonce = window.YowaoConfig.nonce;
            
            const time=ref(''), date=ref(''), weekday=ref(''), lunarText=ref(''), ganzhiText=ref(''), query=ref(''), showEngines=ref(false);
            const modals=ref({auth:false, user:false, addBook:false});
            const authMode = ref('login'); 
            const form=ref({user:'', pass:'', email:'', code:'', resetInput:''});
            const countdown = ref(0);
            const bookForm = ref({title:'', url:''});
            const bookImgFile = ref(null);
            const regConfig = { email: <?php echo $enable_email_reg ? 'true' : 'false'; ?> };

            const onBookImgChange = (e) => { const f = e.target.files && e.target.files[0]; bookImgFile.value = f || null; };
            const cleanBookTitle = () => { bookForm.value.title = (bookForm.value.title || '').replace(/[^\u4e00-\u9fa5A-Za-z0-9\s]/g,''); };
            const enginesConfig = <?php echo json_encode($search_engines); ?>;
            const engine = ref(Object.keys(enginesConfig)[0]); 
            const engines = computed(() => enginesConfig);
            
            // [API] 删除书签
            const deleteBookmark = async (id) => {
                if(!confirm('确定移除此收藏？')) return;
                const fd = new FormData(); 
                fd.append('action', 'y_del_bookmark'); fd.append('id', id);
                fd.append('security', nonce); // 安全字段
                await fetch(ajaxUrl, { method: 'POST', body: fd }); location.reload();
            };

            // [API] 添加书签
            const addBookmarkAction = async () => {
                if(!bookForm.value.title || !bookForm.value.url) { alert('请填写完整'); return; }
                const fd = new FormData(); 
                fd.append('action', 'y_add_bookmark');
                fd.append('title', bookForm.value.title); fd.append('url', bookForm.value.url);
                fd.append('security', nonce); // 安全字段
                if(bookImgFile.value) fd.append('img_file', bookImgFile.value);
                const r = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                const d = await r.json();
                if(d.success) { alert('添加成功'); modals.value.addBook = false; location.reload(); } 
                else { alert(d.data.msg || '添加失败'); }
            };

            // [API] 点击统计
            const clickBookmark = async (id) => {
                try {
                    const fd = new FormData(); fd.append('action','y_click_bookmark'); fd.append('id', id);
                    fd.append('security', nonce);
                    fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin', keepalive: true });
                } catch(e) {}
            };

            // [API] 点赞
            const topLikesMap = ref({<?php if(isset($card_apps) && is_array($card_apps)){ $pairs=[]; foreach($card_apps as $it){ $pairs[] = json_encode((string)intval($it['id'])) . ':' . intval($it['likes']); } echo implode(',', $pairs); } ?>});
            const heartPulseMap = ref({});
            const likedMap = ref({<?php if(isset($card_apps) && is_array($card_apps)){ $pairs=[]; foreach($card_apps as $it){ $liked = ($login && get_user_meta($u->ID, 'yowao_like_app_'.$it['id'], true)) ? 'true' : 'false'; $pairs[] = json_encode((string)intval($it['id'])) . ':' . $liked; } echo implode(',', $pairs); } ?>});
            const likeApp = async (id) => {
                try {
                    const fd = new FormData();
                    fd.append('action', 'y_like_app'); fd.append('id', id);
                    fd.append('security', nonce); // 安全字段
                    const r = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                    const d = await r.json();
                    if(d && d.success) {
                        topLikesMap.value[id] = d.data.likes;
                        heartPulseMap.value[id] = true;
                        setTimeout(()=>{ heartPulseMap.value[id] = false; }, 500);
                        likedMap.value[id] = true;
                    } else if(d && d.data && d.data.code === 'auth') { modals.value.auth = true; }
                } catch(e) {}
            };
            
            const appsData = <?php echo json_encode($apps); ?>;
            const bookmarksData = <?php echo json_encode($bookmarks); ?>;
            
            // 分页逻辑
            const restApps = computed(() => appsData.filter((item) => item.style !== 'card'));
            const updatePageSize = () => { const w = window.innerWidth; return w >= 1024 ? 15 : (w >= 768 ? 10 : 8); };
            const pageSize = ref(15);
            const currentPage = ref(0);
            const chunkedApps = computed(() => {
                const chunks = []; const arr = restApps.value;
                for (let i = 0; i < arr.length; i += pageSize.value) chunks.push(arr.slice(i, i + pageSize.value));
                if(chunks.length === 0) chunks.push([]); return chunks;
            });
            const totalPages = computed(() => chunkedApps.value.length);
            const pageDots = computed(() => { const t = totalPages.value; const r = []; for(let i=0;i<t;i++) r.push(i); return r.slice(0,9); });
            const sliderInlineStyle = computed(() => ({ transform: 'translateX(' + (-currentPage.value * 100) + '%)' }));
            const nextPage = () => { if(currentPage.value < totalPages.value - 1) currentPage.value++; else currentPage.value = 0; };
            const prevPage = () => { if(currentPage.value > 0) currentPage.value--; };
            const goPage = (n) => { currentPage.value = n; };

            const swiping = ref(false); const startX = ref(0); const deltaX = ref(0); const SWIPE_THRESHOLD = 50;
            const onDown = (e) => { swiping.value = true; startX.value = e.clientX; deltaX.value = 0; };
            const onMove = (e) => { if(!swiping.value) return; deltaX.value = e.clientX - startX.value; };
            const onUp = () => { if(!swiping.value) return; if(deltaX.value <= -SWIPE_THRESHOLD) nextPage(); else if(deltaX.value >= SWIPE_THRESHOLD) prevPage(); swiping.value=false; };
            const onTouchStart = (e) => { swiping.value = true; startX.value = e.touches[0].clientX; };
            const onTouchMove = (e) => { if(!swiping.value) return; deltaX.value = e.touches[0].clientX - startX.value; };
            const onTouchEnd = () => { onUp(); };

            const bmCols = ref(6); const bmPageSize = ref(12); const bmCurrentPage = ref(0);
            const bmChunked = computed(() => { const src = [{__add:true}, ...bookmarksData]; const chunks = []; for(let i=0;i<src.length;i+=bmPageSize.value) chunks.push(src.slice(i, i+bmPageSize.value)); return chunks; });
            const bmTotalPages = computed(() => bmChunked.value.length);
            const bmPageDots = computed(() => { const t=bmTotalPages.value; const r=[]; for(let i=0;i<t;i++) r.push(i); return r; });
            const bmGoPage = (n) => { bmCurrentPage.value = n; };
            const bmNextPage = () => { if(bmCurrentPage.value < bmTotalPages.value-1) bmCurrentPage.value++; else bmCurrentPage.value=0; };
            const bmPrevPage = () => { if(bmCurrentPage.value > 0) bmCurrentPage.value--; };
            const bmOnDown = (e) => { swiping.value = true; startX.value = e.clientX; deltaX.value=0; };
            const bmOnMove = (e) => { if(!swiping.value) return; deltaX.value = e.clientX - startX.value; };
            const bmOnUp = () => { if(!swiping.value) return; if(deltaX.value <= -SWIPE_THRESHOLD) bmNextPage(); else if(deltaX.value >= SWIPE_THRESHOLD) bmPrevPage(); swiping.value=false; };
            const bmOnTouchStart = (e) => { swiping.value = true; startX.value = e.touches[0].clientX; };
            const bmOnTouchMove = (e) => { if(!swiping.value) return; deltaX.value = e.touches[0].clientX - startX.value; };
            const bmOnTouchEnd = () => { bmOnUp(); };

            const updateBmCols = () => { const w = window.innerWidth; bmCols.value = w >= 1024 ? 6 : (w >= 768 ? 4 : 2); pageSize.value = updatePageSize(); bmPageSize.value = bmCols.value * 2; bmCurrentPage.value = 0; cardPageSize.value = (w >= 768 ? 6 : 4); };

            const cardPageSize = ref(6); const cardCurrentPage = ref(0);
            const cardApps = computed(() => appsData.filter((item) => item.style === 'card').slice(2));
            const cardChunkedApps = computed(() => { const chunks=[]; for(let i=0;i<cardApps.value.length;i+=cardPageSize.value) chunks.push(cardApps.value.slice(i, i+cardPageSize.value)); if(chunks.length===0) chunks.push([]); return chunks; });
            const cardTotalPages = computed(() => cardChunkedApps.value.length);
            const cardPageDots = computed(() => { const t=cardTotalPages.value; const r=[]; for(let i=0;i<t;i++) r.push(i); return r; });
            const cardSliderInlineStyle = computed(() => ({ transform: 'translateX(' + (-cardCurrentPage.value * 100) + '%)' }));
            const cardNextPage = () => { if(cardCurrentPage.value < cardTotalPages.value - 1) cardCurrentPage.value++; else cardCurrentPage.value = 0; };
            const cardPrevPage = () => { if(cardCurrentPage.value > 0) cardCurrentPage.value--; };
            const cardGoPage = (n) => { cardCurrentPage.value = n; };
            const onDownCard = (e) => { swiping.value = true; startX.value = e.clientX; deltaX.value=0; };
            const onMoveCard = (e) => { if(!swiping.value) return; deltaX.value = e.clientX - startX.value; };
            const onUpCard = () => { if(!swiping.value) return; if(deltaX.value <= -SWIPE_THRESHOLD) cardNextPage(); else if(deltaX.value >= SWIPE_THRESHOLD) cardPrevPage(); swiping.value=false; };
            const onTouchStartCard = (e) => { swiping.value = true; startX.value = e.touches[0].clientX; };
            const onTouchMoveCard = (e) => { if(!swiping.value) return; deltaX.value = e.touches[0].clientX - startX.value; };
            const onTouchEndCard = () => { onUpCard(); };

            const lunarInfo=[0x04bd8,0x04ae0,0x0a570,0x054d5,0x0d260,0x0d950,0x16554,0x056a0,0x09ad0,0x055d2,0x04ae0,0x0a5b6,0x0a4d0,0x0d250,0x1d255,0x0b540,0x0d6a0,0x0ada2,0x095b0,0x14977,0x04970,0x0a4b0,0x0b4b5,0x06a50,0x06d40,0x1ab54,0x02b60,0x09570,0x052f2,0x04970,0x06566,0x0d4a0,0x0ea50,0x06e95,0x05ad0,0x02b60,0x186e3,0x092e0,0x1c8d7,0x0c950,0x0d4a0,0x1d8a6,0x0b550,0x056a0,0x1a5b4,0x025d0,0x092d0,0x0d2b2,0x0a950,0x0b557,0x06ca0,0x0b550,0x15355,0x04da0,0x0a5d0,0x14573,0x052d0,0x0a9a8,0x0e950,0x06aa0,0x0aea6,0x0ab50,0x04b60,0x0aae4,0x0a570,0x05260,0x0f263,0x0d950,0x05b57,0x056a0,0x096d0,0x04dd5,0x04ad0,0x0a4d0,0x0d4d4,0x0d250,0x0d558,0x0b540,0x0b5a0,0x195a6,0x095b0,0x049b0,0x0a974,0x0a4b0,0x0b27a,0x06a50,0x06d40,0x0af46,0x0ab60,0x09570,0x04af5,0x04970,0x064b0,0x074a3,0x0ea50,0x06b58,0x05ac0,0x0ab60,0x096d5,0x092e0,0x0c960,0x0d954,0x0d4a0,0x0da50,0x07552,0x056a0,0x0abb7,0x025d0,0x092d0,0x0cab5,0x0a950,0x0b4a0,0x0baa4,0x0ad50,0x055d9,0x04ba0,0x0a5b0,0x15176,0x052b0,0x0a930,0x07954,0x06aa0,0x0ad50,0x05b52,0x04b60,0x0a6e6,0x0a4e0,0x0d260,0x0ea65,0x0d530,0x05aa0,0x076a3,0x096d0,0x04bd7,0x04ad0,0x0a4d0,0x1d0b6,0x0d250,0x0d520,0x0dd45,0x0b5a0,0x056d0,0x055b2,0x049b0,0x0a577,0x0a4b0,0x0aa50,0x1b255,0x06d20,0x0ada0,0x14b63];
            const lYearDays=(y)=>{let sum=348; for(let i=0x8000;i>0x8;i>>=1) sum+=((lunarInfo[y-1900]&i)?1:0); return sum+leapDays(y);} 
            const leapMonth=(y)=> (lunarInfo[y-1900]&0xf);
            const leapDays=(y)=>{if(leapMonth(y)) return ((lunarInfo[y-1900]&0x10000)?30:29); else return 0;}
            const monthDays=(y,m)=> ((lunarInfo[y-1900]&(0x10000>>m))?30:29);
            const solarToLunar=(date)=>{let offset=(Date.UTC(date.getFullYear(),date.getMonth(),date.getDate())-Date.UTC(1900,0,31))/86400000; let year, temp; for(year=1900; year<2100 && offset>0; year++){ temp=lYearDays(year); offset-=temp; } if(offset<0){ offset+=temp; year--; } let leap=leapMonth(year), isLeap=false; let month; for(month=1; month<=12 && offset>0; month++){ temp=monthDays(year,month); if(leap && month==leap+1 && !isLeap){ month--; temp=leapDays(year); isLeap=true; } else { if(leap && month==leap+1 && isLeap){ isLeap=false; } } offset-=temp; } if(offset==0 && leap && month==leap+1 && isLeap){ if(isLeap){ isLeap=false; } else { isLeap=true; month--; } } if(offset<0){ offset+=temp; month--; } let day=offset+1; return {year,month,day,leap:isLeap}; };
            const cnNum=['零','一','二','三','四','五','六','七','八','九','十','十一','十二','十三','十四','十五','十六','十七','十八','十九','二十','二十一','二十二','二十三','二十四','二十五','二十六','二十七','二十八','二十九','三十'];
            const cnMonth=(m)=> (m<=10? ['一','二','三','四','五','六','七','八','九','十'][m-1] : (m==11?'十一':'十二'))+'月';
            const cnDay=(d)=> cnNum[d];
            const stems=['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'];
            const branches=['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'];
            const ganzhiDay=(date)=>{ const idx = Math.floor(Date.UTC(date.getFullYear(),date.getMonth(),date.getDate())/86400000) + 25567 + 10; return stems[idx%10] + branches[idx%12]; };
            const updateClock = () => {
                const now = new Date();
                time.value = String(now.getHours()).padStart(2,'0')+':'+String(now.getMinutes()).padStart(2,'0')+':'+String(now.getSeconds()).padStart(2,'0');
                date.value = (now.getMonth()+1)+'月'+now.getDate()+'日';
                weekday.value = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'][now.getDay()];
                const l = solarToLunar(now);
                lunarText.value = (l.leap?'闰':'') + cnMonth(l.month) + cnDay(l.day);
                ganzhiText.value = ganzhiDay(now);
            };
            setInterval(updateClock, 1000); updateClock();

            const selectEngine = (k) => { engine.value = k; showEngines.value = false; };
            const closeDropdowns = () => { showEngines.value = false; };
            const doSearch = () => { if(query.value) window.open(engines.value[engine.value].url + encodeURIComponent(query.value), '_blank'); };
            const cleanUser = () => { form.value.user = form.value.user.replace(/[^a-zA-Z0-9]/g, ''); };
            
            // [API] 发送验证码
            const sendCode = async () => {
                if(!form.value.email) { alert('请先填写邮箱'); return; }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) { alert('邮箱格式不正确'); return; }
                const fd = new FormData();
                fd.append('action', 'y_send_reg_code'); fd.append('email', form.value.email);
                fd.append('security', nonce); // 安全字段
                countdown.value = 60; const timer = setInterval(() => { countdown.value--; if(countdown.value <= 0) clearInterval(timer); }, 1000);
                try {
                    const r = await fetch(ajaxUrl, { method: 'POST', body: fd });
                    const d = await r.json();
                    if(d.success) { alert('验证码已发送至您的邮箱，请查收'); }
                    else { alert(d.data.msg || '发送失败'); countdown.value = 0; clearInterval(timer); }
                } catch(e) { alert('网络错误'); countdown.value = 0; clearInterval(timer); }
            };
            
            // [API] 登录与注册 (核心修复版)
            const authAction = async () => {
                if(!form.value.user || !form.value.pass) { alert('请输入账号密码'); return; }
                if(authMode.value === 'register' && regConfig.email && !form.value.email) { alert('注册需要填写邮箱'); return; }
                const fd = new FormData();
                const actionName = authMode.value === 'login' ? 'y_auth_v67' : 'y_register_v67';
                fd.append('action', actionName);
                fd.append('user', form.value.user);
                fd.append('pass', form.value.pass);
                fd.append('security', nonce); // 安全字段
                if(authMode.value === 'register' && regConfig.email) { fd.append('email', form.value.email); fd.append('code', form.value.code || ''); }
                try {
                    const r = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                    if (!r.ok) throw new Error('服务器错误: ' + r.status);
                    const d = await r.json();
                    if(d.success) {
                        if(authMode.value === 'register' && d.data.action === 'reload') { alert('注册成功，已自动登录！'); location.reload(); }
                        else if(authMode.value === 'login') { alert('登录成功'); location.reload(); }
                        else { alert(d.data.msg); }
                    } else { alert(d.data.msg || '操作失败'); }
                } catch (e) { console.error(e); alert('操作失败，请按F12查看控制台报错: ' + e.message); }
            };

            // [API] 重置密码
            const resetAction = async () => {
                if(!form.value.resetInput) { alert('请输入用户名或邮箱'); return; }
                const fd = new FormData(); fd.append('action', 'y_reset_pwd');
                fd.append('user_input', form.value.resetInput);
                fd.append('security', nonce); // 安全字段
                const r = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                const d = await r.json();
                alert(d.data.msg);
            };

            // [API] 个人资料与文件上传
            const profileAction = async (mode, val = '') => {
                const fd = new FormData(); fd.append('action', 'y_profile_v67'); fd.append('mode', mode);
                fd.append('security', nonce); // 安全字段
                if(mode === 'avatar') fd.append('avatar_url', val);
                if(mode === 'bg') fd.append('bg_url', val);
                await fetch(ajaxUrl, { method: 'POST', body: fd }); location.reload();
            };

            const uploadProfileFile = async (type, file) => {
                if(!file) return;
                const fd = new FormData(); fd.append('action', 'y_upload_profile_file'); 
                fd.append('type', type); fd.append('file', file);
                fd.append('security', nonce); // 安全字段
                const r = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                const d = await r.json();
                if(d && d.success && d.data && d.data.url) { await profileAction(type, d.data.url); return; }
                if(d && !d.success && d.data && d.data.code === 'auth') { modals.value.auth = true; }
            };

            const triggerMedia = (type) => {
                if(window.wp && wp.media) {
                    const m = wp.media({title: '选择图片', button: {text: '确认'}, multiple: false}).on('select', () => {
                        profileAction(type, m.state().get('selection').first().toJSON().url);
                    }).open();
                    return;
                }
                const input = document.createElement('input'); input.type = 'file'; input.accept = 'image/*';
                input.onchange = () => { const f = input.files && input.files[0]; uploadProfileFile(type, f); };
                input.click();
            };

            onMounted(() => {
                updateBmCols(); window.addEventListener('resize', updateBmCols);
                document.body.addEventListener('click', (e) => {
                    if(showEngines.value && !e.target.closest('.engine-select') && !e.target.closest('.search-bar-glow')) showEngines.value = false;
                });
                window.addEventListener('keydown', (e) => { if(e.key==='Escape') showEngines.value=false; });
                document.querySelectorAll('.web-card').forEach((c)=>{
                    const ov = c.querySelector('.web-overlay'); const dv = c.querySelector('.web-depth'); if(!ov) return;
                    c.addEventListener('mousemove', (e)=>{
                        const r = c.getBoundingClientRect();
                        const x = Math.max(0, Math.min(1, (e.clientX - r.left) / r.width));
                        const y = Math.max(0, Math.min(1, (e.clientY - r.top) / r.height));
                        ov.style.setProperty('--web-x', (x*100)+'%'); ov.style.setProperty('--web-y', (y*100)+'%');
                        if(dv){ dv.style.setProperty('--web-x', (x*100)+'%'); dv.style.setProperty('--web-y', (y*100)+'%'); }
                        ov.style.transform = `perspective(600px) rotateX(${(0.5 - y) * 10}deg) rotateY(${(x - 0.5) * 10}deg)`;
                    });
                    c.addEventListener('mouseleave', ()=>{
                        ov.style.transform = 'none'; ov.style.setProperty('--web-x', '80%'); ov.style.setProperty('--web-y', '20%');
                        if(dv){ dv.style.setProperty('--web-x','80%'); dv.style.setProperty('--web-y','20%'); }
                    });
                });
            });

            return { 
                time, date, weekday, lunarText, ganzhiText, query, engine, engines, showEngines, selectEngine, closeDropdowns, doSearch, 
                modals, form, authMode, authAction, resetAction, profileAction, uploadProfileFile, triggerMedia, 
                deleteBookmark, addBookmarkAction, bookForm, bookImgFile, onBookImgChange, cleanBookTitle,
                likeApp, topLikesMap, heartPulseMap, likedMap, sendCode, countdown, cleanUser, regConfig,
                currentPage, totalPages, chunkedApps, sliderInlineStyle, nextPage, prevPage, goPage, pageDots, pageSize,
                onDown, onMove, onUp, onTouchStart, onTouchMove, onTouchEnd,
                bmChunked, bmCurrentPage, bmTotalPages, bmGoPage, bmPageDots, bmOnDown, bmOnMove, bmOnUp, bmOnTouchStart, bmOnTouchMove, bmOnTouchEnd,
                cardChunkedApps, cardTotalPages, cardSliderInlineStyle, cardGoPage, cardCurrentPage, cardPageSize, cardPageDots, 
                onDownCard, onMoveCard, onUpCard, onTouchStartCard, onTouchMoveCard, onTouchEndCard
            }
        }
    }).mount('#app');
</script>
<?php wp_footer(); ?>
</body>
</html>