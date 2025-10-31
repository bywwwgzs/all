<?php
<?php
// 安全设置
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

header('Content-Type: text/html; charset=UTF-8');

echo '<!DOCTYPE html>';
echo '<html lang="zh-CN">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>PHP图像扩展检查</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }';
echo '.section { margin: 20px 0; padding: 15px; border-radius: 6px; }';
echo '.success { background-color: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d; }';
echo '.error { background-color: #f2dede; border: 1px solid #ebccd1; color: #a94442; }';
echo '.info { background-color: #d9edf7; border: 1px solid #bce8f1; color: #31708f; }';
echo 'h1 { color: #333; }';
echo 'h2 { margin-top: 0; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<h1>PHP图像扩展检查</h1>';

// 检查GD扩展
echo '<div class="section ' . (extension_loaded('gd') ? 'success' : 'error') . '">';
echo '<h2>GD扩展</h2>';

if (extension_loaded('gd')) {
    echo '<p>✓ GD扩展已加载</p>';
    $gd_info = gd_info();
    echo '<p>GD版本: ' . htmlspecialchars($gd_info['GD Version']) . '</p>';
    echo '<p>支持的格式:</p>';
    echo '<ul>';
    
    $formats = [
        'PNG Support', 
        'JPEG Support', 
        'GIF Read Support', 
        'GIF Create Support',
        'WebP Support'
    ];
    
    foreach ($formats as $format) {
        if (isset($gd_info[$format]) && $gd_info[$format]) {
            echo '<li>✓ ' . htmlspecialchars($format) . '</li>';
        } else {
            echo '<li>✗ ' . htmlspecialchars($format) . '</li>';
        }
    }
    echo '</ul>';
    
    // 特别检查ICO支持
    if (function_exists('imageico')) {
        echo '<p>✓ 支持ICO格式输出</p>';
    } else {
        echo '<p class="error">✗ 不支持ICO格式输出 - 这会导致转换失败</p>';
        echo '<p>解决方案: 升级PHP版本或重新编译GD库以支持ICO格式</p>';
    }
} else {
    echo '<p>✗ GD扩展未加载</p>';
    echo '<p>解决方案: 请安装并启用PHP GD扩展</p>';
    echo '<p>Windows系统: 编辑php.ini，取消注释 extension=gd2</p>';
    echo '<p>Linux系统: 安装php-gd包并重启Web服务器</p>';
}
echo '</div>';

// 检查Imagick扩展
echo '<div class="section ' . (extension_loaded('imagick') ? 'success' : 'info') . '">';
echo '<h2>Imagick扩展</h2>';

if (extension_loaded('imagick')) {
    echo '<p>✓ Imagick扩展已加载</p>';
    try {
        $imagick = new Imagick();
        $formats = $imagick->queryFormats();
        echo '<p>支持的格式数量: ' . count($formats) . '</p>';
        echo '<p>支持ICO: ' . (in_array('ICO', $formats) ? '✓' : '✗') . '</p>';
        echo '<p>支持PNG: ' . (in_array('PNG', $formats) ? '✓' : '✗') . '</p>';
    } catch (Exception $e) {
        echo '<p>✗ Imagick初始化失败: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p>✗ Imagick扩展未加载</p>';
    echo '<p>注意: Imagick不是必需的，但可以作为GD库的替代方案</p>';
}
echo '</div>';

// 系统信息
echo '<div class="section info">';
echo '<h2>系统信息</h2>';
echo '<p>PHP版本: ' . htmlspecialchars(phpversion()) . '</p>';
echo '<p>操作系统: ' . htmlspecialchars(PHP_OS) . '</p>';
echo '<p>服务器软件: ' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? '未知') . '</p>';
echo '</div>';

echo '</body>';
echo '</html>';