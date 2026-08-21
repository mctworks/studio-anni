<?php
declare(strict_types=1);

/**
 * bsky-proxy.php
 *
 * Server-side proxy for Studio Anni's homepage Bluesky widget.
 *
 * This script logs in server-side, fetches the last N posts, trims them
 * down to what the widget needs, and caches the result to a file so we're
 * not hitting Bluesky's API on every page load.
 *
 * Front-end (bsky.js) just does: fetch('bsky-proxy.php').then(r => r.json())
 */

error_reporting(E_ALL);
ini_set('display_errors', '0'); // never leak errors into the JSON response
header('Content-Type: application/json; charset=utf-8');

// ---- Config -----------------------------------------------------------
const BSKY_HANDLE       = 'studioanni.com';
const BSKY_APP_PASSWORD = 'zhpq-cele-fzey-xaw6';
const BSKY_SERVICE      = 'https://bsky.social';
const BSKY_POST_LIMIT   = 20;
const BSKY_CACHE_TTL    = 300; // seconds — how long before we re-fetch
const BSKY_CACHE_DIR    = __DIR__ . '/cache';
const BSKY_FEED_CACHE_FILE = BSKY_CACHE_DIR . '/bsky_feed_cache.json';

// ---- Helpers ------------------------------------------------------------

function bsky_ensure_cache_dir(): void {
    if (!is_dir(BSKY_CACHE_DIR)) {
        mkdir(BSKY_CACHE_DIR, 0755, true);
    }
    // Belt-and-suspenders: block direct web access to the cache dir.
    $htaccess = BSKY_CACHE_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Require all denied\n");
    }
}

function bsky_send_error(string $message, int $httpCode = 502): void {
    http_response_code($httpCode);
    echo json_encode(['error' => $message]);
    exit;
}

function bsky_http_json(string $url, string $method = 'GET', array $headers = [], ?array $body = null): array {
    $ch = curl_init($url);
    $defaultHeaders = ['Content-Type: application/json', 'User-Agent: StudioAnni-BskyWidget/1.0'];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => array_merge($defaultHeaders, $headers),
        CURLOPT_TIMEOUT        => 10,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("cURL error: $err");
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($raw, true);
    if ($status < 200 || $status >= 300) {
        $msg = $data['message'] ?? "HTTP $status";
        throw new RuntimeException("Bluesky API error: $msg");
    }
    return $data ?? [];
}

function bsky_login(): array {
    // createSession is cheap enough to call fresh each time the cache
    // expires (at most once every BSKY_CACHE_TTL seconds), so we skip
    // the extra complexity of persisting/refreshing tokens.
    return bsky_http_json(BSKY_SERVICE . '/xrpc/com.atproto.server.createSession', 'POST', [], [
        'identifier' => BSKY_HANDLE,
        'password'   => BSKY_APP_PASSWORD,
    ]);
}

function bsky_get_author_feed(string $accessJwt, string $did): array {
    $url = BSKY_SERVICE . '/xrpc/app.bsky.feed.getAuthorFeed'
        . '?actor=' . urlencode($did)
        . '&filter=posts_and_author_threads'
        . '&limit=' . BSKY_POST_LIMIT;
    return bsky_http_json($url, 'GET', ['Authorization: Bearer ' . $accessJwt]);
}

function bsky_calc_rcb_score(int $likes, int $reposts, int $replies): int {
    /* This scoring system drops off the two zeroes from the original namesake
    ReverendCrush.com Banger Score formula. Since "ratio damage" tested
    poorly on ReverendCrush.com (and would be counterproductive for Studio Anni's
    or most others business model), it makes more sense to use whole numbers.*/  
    $score = ($likes * 2) + ($replies * 3) + ($reposts * 5);
    $score += intdiv($likes, 10) * 7;
    $score += intdiv($replies, 25) * 25;
    $score += intdiv($reposts, 5) * 20;
    return $score;
}

function bsky_youtube_id(string $url): ?string {
    if (str_contains($url, 'youtube.com/watch')) {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $params);
        return $params['v'] ?? null;
    }
    if (str_contains($url, 'youtu.be/')) {
        $parts = explode('youtu.be/', $url);
        return explode('?', $parts[1] ?? '')[0] ?: null;
    }
    if (str_contains($url, 'youtube.com/shorts/')) {
        $parts = explode('youtube.com/shorts/', $url);
        return explode('?', $parts[1] ?? '')[0] ?: null;
    }
    return null;
}

function bsky_is_youtube(string $url): bool {
    return str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be');
}

/** Wrap @mentions in already-HTML-escaped text with links to their profile. */
function bsky_linkify_mentions(string $safeText): string {
    return preg_replace_callback(
        '/@(\w+(?:\.\w+)*)(?=[^\w.@]|$)/',
        static function (array $m): string {
            $handle = $m[1];
            return '<a href="https://bsky.app/profile/' . $handle . '" target="_blank" rel="noreferrer">@' . $handle . '</a>';
        },
        $safeText
    ) ?? $safeText;
}

/** Turns raw post text into safe, mention-linked, line-break-preserving HTML. */
function bsky_text_html(?string $text): string {
    if (!$text) return '';
    $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $safe = nl2br($safe);
    return bsky_linkify_mentions($safe);
}

function bsky_map_images(array $images): array {
    return array_map(static fn(array $img): array => [
        'fullsize' => $img['fullsize'] ?? '',
        'alt'      => $img['alt'] ?? '',
    ], $images);
}

function bsky_map_video(array $embed): array {
    return [
        'playlist'  => $embed['playlist'] ?? '',
        'thumbnail' => $embed['thumbnail'] ?? '',
    ];
}

function bsky_map_external(array $external): array {
    $uri = $external['uri'] ?? '';
    $isYt = bsky_is_youtube($uri);
    return [
        'uri'         => $uri,
        'title'       => $external['title'] ?? '',
        'description' => $external['description'] ?? '',
        'thumb'       => $external['thumb'] ?? '',
        'isYoutube'   => $isYt,
        'youtubeId'   => $isYt ? bsky_youtube_id($uri) : null,
    ];
}

/** Pull images/video/external link out of a post's top-level embed. */
function bsky_extract_embed(?array $embed): array {
    $result = ['images' => [], 'video' => null, 'external' => null];
    if (!$embed || !isset($embed['$type'])) return $result;

    $type = $embed['$type'];

    if ($type === 'app.bsky.embed.images#view') {
        $result['images'] = bsky_map_images($embed['images'] ?? []);
    } elseif ($type === 'app.bsky.embed.video#view') {
        $result['video'] = bsky_map_video($embed);
    } elseif ($type === 'app.bsky.embed.external#view') {
        $result['external'] = bsky_map_external($embed['external'] ?? []);
    } elseif ($type === 'app.bsky.embed.recordWithMedia#view') {
        $media = $embed['media'] ?? [];
        $mediaType = $media['$type'] ?? '';
        if ($mediaType === 'app.bsky.embed.images#view') {
            $result['images'] = bsky_map_images($media['images'] ?? []);
        } elseif ($mediaType === 'app.bsky.embed.video#view') {
            $result['video'] = bsky_map_video($media);
        } elseif ($mediaType === 'app.bsky.embed.external#view') {
            $result['external'] = bsky_map_external($media['external'] ?? []);
        }
    }
    return $result;
}

/** Pull a quoted post (if any) out of a post's embed. */
function bsky_extract_quote(?array $embed): ?array {
    if (!$embed || !isset($embed['$type'])) return null;

    $recordEmbed = null;
    if ($embed['$type'] === 'app.bsky.embed.record#view') {
        $recordEmbed = $embed['record'] ?? null;
    } elseif ($embed['$type'] === 'app.bsky.embed.recordWithMedia#view') {
        $recordEmbed = $embed['record']['record'] ?? $embed['record'] ?? null;
    }
    // No author means it's a deleted/blocked/not-found record view — skip it.
    if (!$recordEmbed || !isset($recordEmbed['author'])) return null;

    $author      = $recordEmbed['author'];
    $value       = $recordEmbed['value'] ?? [];
    $labels      = $recordEmbed['labels'] ?? [];
    $quoteEmbeds = $recordEmbed['embeds'] ?? [];

    $images = [];
    $video = null;
    $external = null;
    foreach ($quoteEmbeds as $qe) {
        $qeType = $qe['$type'] ?? '';
        if ($qeType === 'app.bsky.embed.images#view') {
            $images = array_merge($images, bsky_map_images($qe['images'] ?? []));
        } elseif ($qeType === 'app.bsky.embed.video#view') {
            $video = bsky_map_video($qe);
        } elseif ($qeType === 'app.bsky.embed.external#view') {
            $external = bsky_map_external($qe['external'] ?? []);
        } elseif ($qeType === 'app.bsky.embed.recordWithMedia#view') {
            $media = $qe['media'] ?? [];
            $mediaType = $media['$type'] ?? '';
            if ($mediaType === 'app.bsky.embed.images#view') {
                $images = array_merge($images, bsky_map_images($media['images'] ?? []));
            } elseif ($mediaType === 'app.bsky.embed.video#view') {
                $video = bsky_map_video($media);
            }
        }
    }

    return [
        'handle'               => $author['handle'] ?? '',
        'displayName'          => $author['displayName'] ?? '',
        'avatar'               => $author['avatar'] ?? '',
        'textHtml'             => bsky_text_html($value['text'] ?? ''),
        'hasContentWarning'    => !empty($labels),
        'contentWarningLabels' => array_map(static fn(array $l): string => $l['val'] ?? '', $labels),
        'images'               => $images,
        'video'                => $video,
        'external'             => $external,
    ];
}

function bsky_transform_post(array $item): array {
    $post   = $item['post'];
    $author = $post['author'] ?? [];
    $record = $post['record'] ?? [];

    $embedData = bsky_extract_embed($post['embed'] ?? null);
    $quote     = bsky_extract_quote($post['embed'] ?? null);
    $labels    = $post['labels'] ?? ($record['labels'] ?? []);

    $likeCount   = (int) ($post['likeCount'] ?? 0);
    $repostCount = (int) ($post['repostCount'] ?? 0);
    $replyCount  = (int) ($post['replyCount'] ?? 0);

    return [
        'uri'                  => $post['uri'] ?? '',
        'handle'               => $author['handle'] ?? '',
        'displayName'          => $author['displayName'] ?? '',
        'avatar'               => $author['avatar'] ?? '',
        'createdAt'            => $record['createdAt'] ?? ($post['indexedAt'] ?? ''),
        'replyToHandle'        => $item['reply']['parent']['author']['handle'] ?? null,
        'textHtml'             => bsky_text_html($record['text'] ?? ''),
        'hasContentWarning'    => !empty($labels),
        'contentWarningLabels' => array_map(static fn(array $l): string => $l['val'] ?? '', $labels),
        'images'               => $embedData['images'],
        'video'                => $embedData['video'],
        'external'             => $embedData['external'],
        'quote'                => $quote,
        'counts'               => [
            'likes'   => $likeCount,
            'reposts' => $repostCount,
            'replies' => $replyCount,
        ],
        'rcbScore' => bsky_calc_rcb_score($likeCount, $repostCount, $replyCount),
    ];
}

// ---- Main -----------------------------------------------------------

bsky_ensure_cache_dir();

if (is_file(BSKY_FEED_CACHE_FILE) && (time() - filemtime(BSKY_FEED_CACHE_FILE)) < BSKY_CACHE_TTL) {
    readfile(BSKY_FEED_CACHE_FILE);
    exit;
}

try {
    $session = bsky_login();
    $feed    = bsky_get_author_feed($session['accessJwt'], $session['did']);
    $items   = $feed['feed'] ?? [];
    $posts   = array_map('bsky_transform_post', $items);

    $output = json_encode(['posts' => $posts, 'fetchedAt' => time()]);
    file_put_contents(BSKY_FEED_CACHE_FILE, $output, LOCK_EX);
    echo $output;
} catch (Throwable $e) {
    error_log('[bsky-proxy] ' . $e->getMessage());
    // If Bluesky's API is down/rate-limiting us, serve the stale cache
    // rather than showing visitors a broken widget.
    if (is_file(BSKY_FEED_CACHE_FILE)) {
        readfile(BSKY_FEED_CACHE_FILE);
        exit;
    }
    bsky_send_error('Unable to load Bluesky feed right now. :(');
}