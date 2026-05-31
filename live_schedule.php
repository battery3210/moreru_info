<?php

require_once __DIR__ . '/includes/functions.php';

$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$currentPage = $currentPage > 0 ? $currentPage : 1;

$perPage = get_live_schedule_per_page();
$totalItems = count_live_entries(false);
$totalPages = $totalItems > 0 ? (int) ceil($totalItems / $perPage) : 1;

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;
$liveItems = fetch_live_entries(false, $perPage, $offset);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>moreru Official Website</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">
    <link rel="stylesheet" href="https://use.typekit.net/jpl8skz.css">
    <link rel="stylesheet" href="./destyle.css">
    <link rel="stylesheet" href="css/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/slider_style.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="icon" href="./image/moreru_favicon_16.png" sizes="16x16">
    <link rel="icon" href="./image/moreru_favicon_32.png" sizes="32x32">
    <style>
        .live-detail__label {
            margin-bottom: 7px;
            font-size: 0.95em;
            letter-spacing: 0.04em;
            color: crimson;
        }

        .live-detail__acts {
            margin-bottom: 10px;
            line-height: 1.7;
        }

        .live-schedule video {
            width: 80%;
            height: auto;
            display: block;
            margin: 0 auto;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            margin: 40px 0 20px;
            font-size: 1.4rem;
        }

        .pagination a {
            color: blue;
            text-decoration: none;
        }

        .pagination a:hover {
            color: #ff0000;
        }
    </style>
    <script>
        (function(d) {
          var config = {
            kitId: 'kes5zcq',
            scriptTimeout: 3000,
            async: true
          },
          h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='https://use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
        })(document);
    </script>
</head>
<body>

    <div class="container">
        <header class="header">
            <div class="header__inner">
                <div class="burger">
                    <div class="line1"></div>
                    <div class="line2"></div>
                    <div class="line3"></div>
                </div>
                <nav class="header__nav">
                    <ul class="header__nav-items">
                        <li class="header__nav-item">
                            <a class="header__nav-link" href="./top.html">
                                <div class="header__nav-li--eng">top</div>
                            </a>
                        </li>
                        <li class="header__nav-item">
                            <a class="header__nav-link" href="live_schedule.php">
                                <div class="header__nav-li--eng">live schedule</div>
                            </a>
                        </li>
                        <li class="header__nav-item">
                            <a class="header__nav-link" href="connect.html">
                                <div class="header__nav-li--eng">connect</div>
                            </a>
                        </li>
                        <li class="header__nav-item header__nav-item--img">
                            <a class="header__nav-link" href="./">
                                <img src="image/main_bg.png" alt="メニュー画像">
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>
        <section class="top">
            <div class="content-wrapper">
                <div id="main-content">
                    <div class="background"></div>
                    <div class="main_wrapper live-schedule">
                        <?php if (empty($liveItems)): ?>
                            <div class="live-overview">
                                live schedule will be updated soon.
                            </div>
                        <?php else: ?>
                            <?php foreach ($liveItems as $item): ?>
                                <?php if (!empty($item['live_pict'])): ?>
                                    <?php echo render_live_media($item['live_pict'], '', 'live media'); ?>
                                <?php endif; ?>
                                <?php echo $item['body_html']; ?>
                            <?php endforeach; ?>
                            <?php if ($totalPages > 1): ?>
                                <div class="pagination">
                                    <?php if ($currentPage > 1): ?>
                                        <a href="<?php echo h(build_page_url($currentPage - 1)); ?>">prev</a>
                                    <?php endif; ?>
                                    <span><?php echo (int) $currentPage; ?> / <?php echo (int) $totalPages; ?></span>
                                    <?php if ($currentPage < $totalPages): ?>
                                        <a href="<?php echo h(build_page_url($currentPage + 1)); ?>">next</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div id="backToTop" class="material-symbols-outlined md-18 back-to-top">
        arrow_upward
    </div>

    <script>
        setTimeout(function () {
            var mainContent = document.getElementById('main-content');
            var contentWrapper = document.querySelector('#main-content .main_wrapper');
            var background = document.querySelector('#main-content .background');

            requestAnimationFrame(function () {
                mainContent.classList.add('show');
                contentWrapper.classList.add('show');
                background.classList.add('show');
            });
        }, 500);
    </script>
    <script src="./script.js"></script>

</body>
</html>