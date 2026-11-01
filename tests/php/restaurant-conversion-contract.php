<?php
/** Regression contract for dining hero CTA hierarchy and enquiry truthfulness. */
$root = dirname(__DIR__, 2);
$errors = [];
$read = static function ($relative) use ($root, &$errors) {
    $path = $root . '/' . $relative;
    $source = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($source)) {
        $errors[] = $relative . ': file is missing or unreadable';
        return '';
    }
    return $source;
};
$restaurant = $read('restaurant.php');
$roof = $read('roof.php');
$restaurantCss = $read('css/restaurant.css');
$roofCss = $read('css/roof.css');

$restaurantHeroStart = strpos($restaurant, '<section class="restaurant-hero"');
$restaurantIntroStart = strpos($restaurant, '<section class="section restaurant-intro"');
$restaurantHero = $restaurantHeroStart !== false && $restaurantIntroStart > $restaurantHeroStart
    ? substr($restaurant, $restaurantHeroStart, $restaurantIntroStart - $restaurantHeroStart)
    : '';
$roofHeroStart = strpos($roof, '<section class="page-hero page-hero--photo">');
$roofNextSection = $roofHeroStart === false ? false : strpos($roof, '<section class="section"', $roofHeroStart + 1);
$roofHero = $roofHeroStart !== false && $roofNextSection !== false
    ? substr($roof, $roofHeroStart, $roofNextSection - $roofHeroStart)
    : '';

$checks = [
    ['restaurant hero has stable table-enquiry attribution', strpos($restaurantHero, 'data-cta-id="enquiry-restaurant-table"') !== false && strpos($restaurantHero, 'data-cta-location="restaurant-hero"') !== false],
    ['restaurant hero does not contain the Trip action', strpos($restaurantHero, 'data-trip-add') === false],
    ['restaurant Trip action sits in a separate planning panel', strpos($restaurant, '<section class="section restaurant-trip-planner"') !== false && strpos($restaurant, 'restaurant-trip-planner-actions') !== false],
    ['restaurant and rooftop share the stable Trip item ID', strpos($restaurant, "'id' => 'rooftop-dining'") !== false && strpos($roof, "'id' => 'rooftop-dining'") !== false],
    ['reservation form destination uses truthful request wording', strpos($restaurant, '<p class="eyebrow eyebrow-light">Table enquiry</p>') !== false && strpos($restaurant, '<h2>Request a table.</h2>') !== false],
    ['roof hero retains enquiry and menu discovery', strpos($roofHero, 'data-cta-id="enquiry-rooftop-table"') !== false && strpos($roofHero, 'href="restaurant.php#menu"') !== false],
    ['roof hero does not contain the Trip action', strpos($roofHero, 'data-trip-add') === false],
    ['roof Trip action is placed in the lower planning story', strpos($roof, '<div class="roof-trip-action"') !== false && strpos($roof, 'data-trip-add') > strpos($roof, '<div class="roof-trip-action"')],
    ['restaurant Trip panel has responsive styles and focus treatment', strpos($restaurantCss, '.restaurant-trip-planner-inner') !== false && strpos($restaurantCss, '@media (max-width: 760px)') !== false && strpos($restaurantCss, ':focus-visible') !== false],
    ['roof Trip panel has responsive styles and focus treatment', strpos($roofCss, '.roof-trip-action') !== false && strpos($roofCss, '@media (max-width: 760px)') !== false && strpos($roofCss, ':focus-visible') !== false],
];
foreach ($checks as [$label, $passed]) {
    if (!$passed) $errors[] = $label;
}
if ($errors) {
    fwrite(STDERR, "FAIL dining conversion hierarchy contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS dining conversion hierarchy and truthful enquiry contract.\n";
