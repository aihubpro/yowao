<?php
/**
 * The template for displaying 404 pages (not found)
 * 最终完美版：保留了原版的高级视觉效果，修复了 PHP 兼容性问题
 *
 * @package Yowao
 */

// 1. 安全防线：防止直接访问文件报错
if (!defined('ABSPATH')) {
    exit;
}

$uri = get_template_directory_uri(); 
$root = get_template_directory();

// 2. 修复后的背景逻辑：移除不稳定的 GLOB_BRACE，改用兼容模式
$bg_path = $root . '/assets/img/bg/';
$bg_files = array();

// 检查目录是否存在，循环匹配图片
if (is_dir($bg_path)) {
    $extensions = array('jpg', 'png', 'jpeg', 'webp');
    foreach ($extensions as $ext) {
        $files = glob($bg_path . '*.' . $ext);
        if ($files !== false && !empty($files)) {
            $bg_files = array_merge($bg_files, $files);
        }
    }
}

if (!empty($bg_files)) {
    // 随机取一张本地背景图
    $random_file = $bg_files[array_rand($bg_files)];
    $final_bg = $uri . '/assets/img/bg/' . basename($random_file);
} else {
    // 如果本地没有图片，使用这张高质量的默认兜底图
    $final_bg = 'https://images.unsplash.com/photo-1494500764479-0c8f2919a3d8?q=80&w=2070';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - <?php bloginfo('name'); ?></title>
    <script src="<?php echo $uri; ?>/assets/js/tailwind.js"></script>
    <link href="<?php echo $uri; ?>/assets/css/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; overflow: hidden; margin: 0; }
        /* 背景层：增加模糊和滤镜，突出前景 */
        .bg-layer { 
            position: fixed; inset: 0; z-index: -2; 
            background: url('<?php echo $final_bg; ?>') center/cover no-repeat fixed; 
            filter: blur(10px) brightness(0.7); 
            transform: scale(1.1); 
            transition: background 1s ease;
        }
        /* 磨砂玻璃卡片特效 */
        .glass-card {
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border-radius: 2rem;
        }
        .text-glow { text-shadow: 0 0 30px rgba(6, 182, 212, 0.6); }
        .animate-bounce-in { animation: bounceIn 1s cubic-bezier(0.215, 0.61, 0.355, 1) both; }
        
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale3d(0.3, 0.3, 0.3); }
            20% { transform: scale3d(1.1, 1.1, 1.1); }
            40% { transform: scale3d(0.9, 0.9, 0.9); }
            60% { opacity: 1; transform: scale3d(1.03, 1.03, 1.03); }
            80% { transform: scale3d(0.97, 0.97, 0.97); }
            100% { opacity: 1; transform: scale3d(1, 1, 1); }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="bg-layer"></div>

    <div class="glass-card p-12 text-center max-w-lg mx-4 animate-bounce-in relative overflow-hidden group">
        <div class="absolute -top-10 -left-10 w-32 h-32 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>

        <div class="relative z-10">
            <div class="text-9xl font-black mb-4 bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-blue-600 text-glow select-none">
                404
            </div>
            
            <h2 class="text-3xl font-bold mb-6 text-white tracking-wide">信号丢失...</h2>
            
            <p class="text-lg text-gray-300 mb-10 leading-relaxed">
                您正在寻找的页面似乎漂流到了数字宇宙的边缘。
                <br>它可能已被删除，或者从未存在过。
            </p>

            <a href="<?php echo home_url(); ?>" class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl font-bold text-white shadow-lg hover:shadow-cyan-500/50 transition-all duration-300 transform hover:scale-105 hover:-translate-y-1 group-hover:ring-2 group-hover:ring-white/20">
                <i class="fas fa-rocket"></i>
                返回控制台 (首页)
            </a>
        </div>
    </div>

</body>
</html>