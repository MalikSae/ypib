<?php

$views_dir = __DIR__ . '/resources/views';

$color_mapping = [
    '#082e8f' => 'primary-600',
    '#052066' => 'primary-800',
    '#1a43a8' => 'primary-700',
    '#20449e' => 'primary-700',
    
    '#f1f4f7' => 'neutral-100',
    '#dee3e9' => 'neutral-200',
    '#ced0d4' => 'neutral-300',
    '#8595a4' => 'neutral-400',
    '#5d6c7b' => 'neutral-500',
    '#4b4c4f' => 'neutral-600',
    '#444950' => 'neutral-600',
    '#1c1e21' => 'neutral-900',
    '#0a1317' => 'neutral-900',
    
    '#31a24c' => 'success',
    '#24e400' => 'success',
    '#2e7d32' => 'success-700',
    
    '#f2a918' => 'warning',
    '#f7b928' => 'warning',
    '#ffe200' => 'warning-light',
    
    '#e41e3f' => 'error',
    '#f0284a' => 'error-500',
    
    '#ffffff' => 'white',
    '#fff' => 'white',
    '#000000' => 'black',
    '#000' => 'black'
];

$updated_count = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($views_dir));
foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) !== 'php') continue;
    
    $filepath = $file->getPathname();
    $content = file_get_contents($filepath);
    $original = $content;
    
    // Match tags
    $content = preg_replace_callback('/<([a-zA-Z0-9\-]+)([^>]*?)>/s', function($match) use ($color_mapping) {
        $tag_name = $match[1];
        $attrs = $match[2];
        
        // Find style attribute
        if (!preg_match('/style\s*=\s*([\'"])(.*?)\1/s', $attrs, $style_match)) {
            return $match[0];
        }
        
        $style_quote = $style_match[1];
        $style_content = $style_match[2];
        
        $new_classes = [];
        
        // Parse properties
        $new_style_content = preg_replace_callback('/(color|background|background-color|border|border-color|border-top|border-bottom|border-left|border-right)\s*:\s*([^;]+);?/i', function($prop_match) use ($color_mapping, &$new_classes) {
            $prop_name = strtolower($prop_match[1]);
            $prop_val = strtolower($prop_match[2]);
            
            if (preg_match('/(#[0-9a-f]{3,6})/i', $prop_val, $hex_match)) {
                $hex = strtolower($hex_match[1]);
                if (strlen($hex) === 4) {
                    $hex = '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
                }
                
                if (isset($color_mapping[$hex])) {
                    $tw_class = $color_mapping[$hex];
                    
                    $prefix = '';
                    if (strpos($prop_name, 'color') !== false && strpos($prop_name, 'border') === false && strpos($prop_name, 'background') === false) {
                        $prefix = 'text-';
                    } elseif (strpos($prop_name, 'background') !== false) {
                        $prefix = 'bg-';
                    } elseif (strpos($prop_name, 'border') !== false) {
                        $prefix = 'border-';
                        if (strpos($prop_name, 'border-top') !== false) $prefix = 'border-t-';
                        elseif (strpos($prop_name, 'border-bottom') !== false) $prefix = 'border-b-';
                        elseif (strpos($prop_name, 'border-left') !== false) $prefix = 'border-l-';
                        elseif (strpos($prop_name, 'border-right') !== false) $prefix = 'border-r-';
                    }
                    
                    if ($prefix) {
                        $new_classes[] = $prefix . $tw_class;
                        return ""; // Remove the property
                    }
                }
            }
            return $prop_match[0];
        }, $style_content);
        
        if (empty($new_classes)) {
            return $match[0];
        }
        
        // Update attributes
        $new_attrs = $attrs;
        $new_style_content = trim($new_style_content);
        
        if ($new_style_content) {
            $new_attrs = str_replace("style={$style_quote}{$style_content}{$style_quote}", "style={$style_quote}{$new_style_content}{$style_quote}", $new_attrs);
        } else {
            // Remove style
            $new_attrs = preg_replace('/\s*style\s*=\s*[\'"].*?[\'"]/s', '', $new_attrs);
        }
        
        // Inject class
        if (preg_match('/class\s*=\s*([\'"])(.*?)\1/s', $new_attrs, $class_match)) {
            $class_quote = $class_match[1];
            $class_content = $class_match[2];
            $existing_classes = array_filter(explode(' ', $class_content));
            
            foreach ($new_classes as $c) {
                if (!in_array($c, $existing_classes)) {
                    $existing_classes[] = $c;
                }
            }
            $new_class_content = implode(' ', $existing_classes);
            $new_attrs = str_replace("class={$class_quote}{$class_content}{$class_quote}", "class={$class_quote}{$new_class_content}{$class_quote}", $new_attrs);
        } else {
            $new_attrs .= ' class="' . implode(' ', $new_classes) . '"';
        }
        
        return '<' . $tag_name . $new_attrs . '>';
    }, $content);
    
    if ($content !== $original) {
        file_put_contents($filepath, $content);
        echo "Updated: $filepath\n";
        $updated_count++;
    }
}

echo "\nDone! Updated $updated_count files.\n";
