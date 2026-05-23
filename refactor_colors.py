import os
import re

# Mapping of legacy hex codes (case insensitive) to new Tailwind classes
color_mapping = {
    '#082e8f': 'primary-600', # Primary
    '#052066': 'primary-800', # Primary deep
    '#1a43a8': 'primary-700', # Focus blue
    '#20449e': 'primary-700', # Link blue
    
    '#f1f4f7': 'neutral-100', # Surface soft
    '#dee3e9': 'neutral-200', # Hairline soft
    '#ced0d4': 'neutral-300', # Hairline
    '#8595a4': 'neutral-400', # Stone
    '#5d6c7b': 'neutral-500', # Steel
    '#4b4c4f': 'neutral-600', # Slate
    '#444950': 'neutral-600', # Charcoal
    '#1c1e21': 'neutral-900', # Ink
    '#0a1317': 'neutral-900', # Ink deep
    
    '#31a24c': 'success', # Success
    '#24e400': 'success', # Success bg (should ideally be mapped to a valid token if not exactly success, but success works)
    '#2e7d32': 'success-700', # Another green found in grep
    
    '#f2a918': 'warning', # Attention
    '#f7b928': 'warning', # Warning
    '#ffe200': 'warning-light', # Warning bg
    
    '#e41e3f': 'error', # Critical
    '#f0284a': 'error-500', # Critical strong
    
    '#ffffff': 'white', # Canvas/on-primary
    '#000000': 'black', # Ink-button
}

# Pre-compile regexes for efficiency
# We look for color, background, and border properties.
# Matches: property: value; (with optional spaces)
props_re = re.compile(r'(color|background|background-color|border|border-color|border-top|border-bottom|border-left|border-right)\s*:\s*([^;]+);?', re.IGNORECASE)

hex_re = re.compile(r'(#[0-9a-fA-F]{3,6})')

# Match a whole HTML tag so we can safely inject classes and modify styles
# This looks for <tagName ... style="..." ... >
tag_re = re.compile(r'<([a-zA-Z0-9\-]+)([^>]*?)>', re.DOTALL)

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    modified = False

    def tag_replacer(match):
        tag_name = match.group(1)
        attrs = match.group(2)
        
        # Check if this tag has a style attribute
        style_match = re.search(r'style\s*=\s*([\'"])(.*?)\1', attrs, re.DOTALL)
        if not style_match:
            return match.group(0) # No style attribute, leave unchanged
        
        style_quote = style_match.group(1)
        style_content = style_match.group(2)
        
        new_classes = []
        new_style_content = style_content
        
        # Parse CSS properties
        # We need a custom replacement function for properties to strip them if they are fully matched
        def prop_replacer(prop_match):
            prop_name = prop_match.group(1).lower()
            prop_val = prop_match.group(2)
            
            # Check for hex code in value
            hex_match = hex_re.search(prop_val)
            if not hex_match:
                return prop_match.group(0)
                
            hex_code = hex_match.group(1).lower()
            
            # Map shorthand hex like #fff to #ffffff
            if len(hex_code) == 4:
                hex_code = '#' + hex_code[1]*2 + hex_code[2]*2 + hex_code[3]*2
                
            if hex_code in color_mapping:
                tw_class = color_mapping[hex_code]
                
                # Determine prefix based on property
                prefix = ''
                if 'color' in prop_name and 'border' not in prop_name and 'background' not in prop_name:
                    prefix = 'text-'
                elif 'background' in prop_name:
                    prefix = 'bg-'
                elif 'border' in prop_name:
                    prefix = 'border-'
                    
                    # If it's a specific border side, we might need a specific class like border-t-xxx
                    if 'border-top' in prop_name: prefix = 'border-t-'
                    elif 'border-bottom' in prop_name: prefix = 'border-b-'
                    elif 'border-left' in prop_name: prefix = 'border-l-'
                    elif 'border-right' in prop_name: prefix = 'border-r-'
                    
                if prefix:
                    new_classes.append(f"{prefix}{tw_class}")
                    
                    # If the property value was just the color (like color:#fff), we can remove the whole property
                    # If it was a compound value like "1px solid #fff", we just remove the property and assume Tailwind borders are set elsewhere, 
                    # OR we can just remove it anyway since tailwind handles it. Let's just remove the property entirely.
                    return "" 
            
            return prop_match.group(0)
            
        new_style_content = props_re.sub(prop_replacer, style_content)
        
        if len(new_classes) == 0:
            return match.group(0) # No mapping found
            
        # Reconstruct the attrs
        new_attrs = attrs
        
        # Update or remove style attribute
        new_style_content = new_style_content.strip()
        if new_style_content:
            new_attrs = new_attrs.replace(f'style={style_quote}{style_content}{style_quote}', f'style={style_quote}{new_style_content}{style_quote}')
        else:
            # Remove empty style
            new_attrs = re.sub(r'\s*style\s*=\s*[\'"].*?[\'"]', '', new_attrs)
            
        # Add new classes
        class_match = re.search(r'class\s*=\s*([\'"])(.*?)\1', new_attrs, re.DOTALL)
        if class_match:
            class_quote = class_match.group(1)
            class_content = class_match.group(2)
            # Avoid duplicates
            existing_classes = set(class_content.split())
            classes_to_add = [c for c in new_classes if c not in existing_classes]
            
            if classes_to_add:
                new_class_content = class_content + (" " if class_content else "") + " ".join(classes_to_add)
                new_attrs = new_attrs.replace(f'class={class_quote}{class_content}{class_quote}', f'class={class_quote}{new_class_content}{class_quote}')
        else:
            # Add class attribute
            new_attrs += f' class="{" ".join(new_classes)}"'
            
        return f'<{tag_name}{new_attrs}>'

    # Process all tags
    content = tag_re.sub(tag_replacer, content)

    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated: {filepath}")
        return True
    return False

def main():
    views_dir = os.path.join(os.path.dirname(__file__), 'resources', 'views')
    updated_count = 0
    
    for root, _, files in os.walk(views_dir):
        for file in files:
            if file.endswith('.blade.php'):
                filepath = os.path.join(root, file)
                if process_file(filepath):
                    updated_count += 1
                    
    print(f"\nDone! Updated {updated_count} files.")

if __name__ == '__main__':
    main()
