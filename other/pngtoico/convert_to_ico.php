<?php
<?php
// 安全设置
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

// 源文件路径
$source_file = 'favicon.png';
// 目标文件路径
$target_file = 'favicon.ico';

// 安全检查：验证源文件
if (!file_exists($source_file)) {
    error_log('源文件 favicon.png 不存在');
    die("错误: 源文件 favicon.png 不存在\n");
}

if (!is_readable($source_file)) {
    error_log('无法读取源文件 favicon.png');
    die("错误: 无法读取源文件 favicon.png\n");
}

// 验证文件类型
$mime_type = mime_content_type($source_file);
if ($mime_type !== 'image/png') {
    error_log('源文件不是有效的PNG图像: ' . $mime_type);
    die("错误: 源文件不是有效的PNG图像\n");
}

// 验证文件大小（限制5MB）
$file_size = filesize($source_file);
if ($file_size > 5 * 1024 * 1024) {
    error_log('源文件过大: ' . $file_size);
    die("错误: 源文件大小不能超过5MB\n");
}

// 检查GD库
if (!extension_loaded('gd')) {
    error_log('GD库未加载');
    die("错误: PHP GD库未安装，请先安装GD库扩展\n");
}

$gd_info = gd_info();
if (empty($gd_info['PNG Support'])) {
    error_log('GD库不支持PNG格式');
    die("错误: GD库不支持PNG格式，请重新配置GD库\n");
}

// 尝试使用GD库打开PNG文件
$image = @imagecreatefrompng($source_file);
if (!$image) {
    error_log('无法打开PNG文件');
    die("错误: 无法打开PNG文件，可能是文件已损坏或格式不正确\n");
}

// 获取图像宽度和高度
$width = imagesx($image);
$height = imagesy($image);

// 验证图像尺寸
if ($width > 1024 || $height > 1024) {
    error_log("图像尺寸过大: {$width}x{$height}");
    die("错误: 图像尺寸过大，建议使用16x16、32x32或48x48像素的图像\n");
}

$success = false;
$icon_sizes = [16, 32, 48];

// 尝试创建多尺寸ICO
try {
    // 对于支持多尺寸ICO的PHP版本
    $target_images = [];
    
    foreach ($icon_sizes as $size) {
        $resized_image = imagecreatetruecolor($size, $size);
        
        // 保留透明度
        imagealphablending($resized_image, false);
        $transparent = imagecolorallocatealpha($resized_image, 0, 0, 0, 127);
        imagefill($resized_image, 0, 0, $transparent);
        imagesavealpha($resized_image, true);
        
        // 调整图像大小
        imagecopyresampled(
            $resized_image, 
            $image, 
            0, 0, 0, 0, 
            $size, $size, 
            $width, $height
        );
        
        $target_images[] = $resized_image;
    }
    
    // 尝试保存为ICO
    $success = imageico($image, $target_file, $icon_sizes);
    
    // 释放资源
    foreach ($target_images as $img) {
        imagedestroy($img);
    }
} catch (Exception $e) {
    error_log('转换过程出错: ' . $e->getMessage());
}

// 如果多尺寸转换失败，尝试单尺寸转换
if (!$success) {
    try {
        $resized_image = imagecreatetruecolor(16, 16);
        
        // 保留透明度
        imagealphablending($resized_image, false);
        $transparent = imagecolorallocatealpha($resized_image, 0, 0, 0, 127);
        imagefill($resized_image, 0, 0, $transparent);
        imagesavealpha($resized_image, true);
        
        // 调整图像大小
        imagecopyresampled(
            $resized_image, 
            $image, 
            0, 0, 0, 0, 
            16, 16, 
            $width, $height
        );
        
        // 再次尝试保存
        $success = imageico($resized_image, $target_file);
        
        // 释放资源
        imagedestroy($resized_image);
    } catch (Exception $e) {
        error_log('单尺寸转换失败: ' . $e->getMessage());
    }
}

// 释放原始图像资源
imagedestroy($image);

// 验证输出文件
if ($success && file_exists($target_file)) {
    // 设置适当的文件权限
    chmod($target_file, 0644);
    echo "转换成功！已生成 " . $target_file . "\n";
} else {
    error_log('所有转换方法均失败');
    echo "转换失败。请尝试使用在线工具或图像编辑软件将PNG转换为ICO格式。\n";
    echo "推荐尺寸：16x16, 32x32, 48x48像素\n";
}