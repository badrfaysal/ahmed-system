<?php
$content = file_get_contents('resources/views/treasury.blade.php');
$lines = explode("\n", $content);
for ($i = 0; $i < count($lines) - 1; $i++) {
    if (strpos($lines[$i], '<p class="mb-') !== false && strpos($lines[$i+1], '<h3') !== false) {
        $temp = $lines[$i];
        $lines[$i] = $lines[$i+1];
        $lines[$i+1] = $temp;
        // Optionally swap margin classes, e.g. change mt-2 to mb-2 on h3, but it's okay to just leave it or fix it.
        $lines[$i] = str_replace('mt-2', 'mb-2', $lines[$i]);
        $lines[$i] = str_replace('mt-1 mb-2', 'mb-2', $lines[$i]);
    }
}
$content = implode("\n", $lines);

// Fix grid layout (first row)
$content = str_replace(
    '<div class="row row-cols-2 row-cols-md-5 g-3 mb-4 animate__animated animate__fadeInUp">',
    '<div class="row row-cols-2 row-cols-md-4 row-cols-xl-6 g-3 mb-4 animate__animated animate__fadeInUp">',
    $content
);

// Fix grid layout (second row)
$content = str_replace(
    '    <div class="col">
        <a href="{{ url(\'/debts\') }}" class="card-link">
            <div class="stat-card bg-debts-for h-100">',
    '</div> <!-- End first row -->

<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">

    <div class="col">
        <a href="{{ url(\'/debts\') }}" class="card-link">
            <div class="stat-card bg-debts-for h-100">',
    $content
);

// Fix expenses button filter wrap
$content = str_replace(
    '<div class="d-flex flex-wrap gap-1">',
    '<div class="d-flex flex-nowrap justify-content-between gap-1 w-100">',
    $content
);

// Fix padding and text for buttons
$content = preg_replace(
    '/<a href="\{\{ \$expBase \}\}&exp_filter=\{\{ \$val \}\}"\s*style="font-size:\.68rem; border-radius:6px; padding:2px 8px; text-decoration:none;/s',
    '<a href="{{ $expBase }}&exp_filter={{ $val }}"
                       class="text-center"
                       style="flex: 1; font-size:.65rem; border-radius:6px; padding:2px 0; text-decoration:none;',
    $content
);

// Fix default filter value
$content = str_replace(
    '$ef      = request(\'exp_filter\', \'3months\');',
    '$ef      = request(\'exp_filter\', \'month\');',
    $content
);
$content = str_replace(
    '<i class="fa fa-filter me-1"></i> {{ $expFilterLabel ?? \'آخر 3 أشهر\' }}',
    '<i class="fa fa-filter me-1"></i> {{ $expFilterLabel ?? \'هذا الشهر\' }}',
    $content
);

file_put_contents('resources/views/treasury.blade.php', $content);
echo "Done\n";
