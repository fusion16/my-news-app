<?php
header("Content-Type: application/json");

// List of RSS feed URLs
$rssFeeds = [
    "Astro Awani" => "https://www.astroawani.com/rss/latest/news.xml",
    "The Star" => "https://www.thestar.com.my/rss/latest",
    "The Edge" => "https://www.theedgemarkets.com/rss.xml",
    "Sinar Harian" => "https://www.sinarharian.com.my/rss/latest",
    "Utusan Malaysia" => "https://www.utusan.com.my/feed/"
];

// Retrieve the keyword and date range from the request
$keyword = strtolower($_GET['keyword'] ?? '');
$startDate = isset($_GET['startDate']) ? strtotime($_GET['startDate']) : null;
$endDate = isset($_GET['endDate']) ? strtotime($_GET['endDate']) : null;

$results = [];

foreach ($rssFeeds as $source => $url) {
    $rssContent = @file_get_contents($url);
    if ($rssContent === FALSE) {
        continue; // Skip feeds that fail to load
    }

    $xml = simplexml_load_string($rssContent);
    if (!$xml) {
        continue; // Skip invalid XML
    }

    foreach ($xml->channel->item as $item) {
        $title = (string)$item->title;
        $link = (string)$item->link;
        $pubDate = strtotime((string)$item->pubDate);
        $description = strtolower((string)$item->description);

        // Filter by keyword
        if ($keyword && strpos(strtolower($title), $keyword) === false && strpos($description, $keyword) === false) {
            continue;
        }

        // Filter by date range
        if (($startDate && $pubDate < $startDate) || ($endDate && $pubDate > $endDate)) {
            continue;
        }

        $results[] = [
            "date" => date("Y-m-d", $pubDate),
            "website" => $source,
            "title" => $title,
            "link" => $link
        ];
    }
}

// Return the results as JSON
echo json_encode($results);
?>