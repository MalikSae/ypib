// dummy partners
$partners = [
    'Mitra 1', 'Mitra 2', 'Mitra 3', 'Mitra 4', 'Mitra 5', 'Mitra 6'
];

foreach ($partners as $index => $name) {
    $url = 'https://placehold.co/200x80/e2e8f0/475569.png?text=' . urlencode($name);
    
    // Set user agent so placehold.co doesn't block it
    $options = [
        'http' => [
            'method' => "GET",
            'header' => "Accept-language: en\r\n" .
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
        ]
    ];
    $context = stream_context_create($options);
    
    $contents = file_get_contents($url, false, $context);
    
    if ($contents) {
        $filename = 'partners/dummy_' . ($index + 1) . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $contents);
        
        \App\Models\Partner::create([
            'name' => $name,
            'logo_path' => $filename,
            'is_active' => true,
        ]);
        echo "Created partner: $name\n";
    } else {
        echo "Failed to download image for: $name\n";
    }
}
echo "Done.\n";
