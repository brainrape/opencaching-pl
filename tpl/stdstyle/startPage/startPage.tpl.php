
<div class="content2-pagetitle">
  <?php if($view->isUserLogged) { ?>
    <?=tr('startPage_welcome')?>&nbsp;<?=$view->username?>
  <?php } else { //if-isUserLogged ?>
    <?=tr('startPage_title')?>
  <?php } //if-isUserLogged ?>
</div>


<div class="content2-container">

    <?php if(!$view->isUserLogged) { ?>
      <!-- intro -->
      <div id="intro">
        <?=$view->introText?>
      </div>
      <!-- /intro -->
    <?php } //if-isUserLogged ?>


    <?php if($view->isUserLogged && !empty($view->newsList)){ ?>
    <!-- NEWS -->

    <div id="newsDiv">
        <p class="content-title-noshade-size3">
          <?=tr('news')?>
        </p>

        <?php foreach($view->newsList as $news) { ?>
          <div class="newsItem">
            <div class="news-statusline">
              <img src="/tpl/stdstyle/images/free_icons/newspaper.png" alt="">
              <?=$news->getDatePublication(true)?>
                 <span class="newsTitle">
                    <?=$news->getTitle()?>
                    <?php if($news->isAuthorHidden()) { ?>
                      <?=tr('news_OCTeam')?>
                    <?php } else { // if-$news->isAuthorHidden() ?>
                      <a href="<?=$news->getAuthor()->getProfileUrl()?>" class="links">
                        <?=$news->getAuthor()->getUserName()?>
                      </a>
                    <?php } // if-$news->isAuthorHidden() ?>
                  </span>
            </div>
            <?=$news->getContent()?>
          </div>
        <?php } //foreach-newsList ?>
    </div>
    <!-- /news -->
    <?php } //if-!empty($view->newsList) ?>


    <!-- total Stats -->
    <div id="totalStatsDiv">
        <p class="content-title-noshade-size3">
          <?=tr('startPage_totalStatsTitle')?>
        </p>

      <div id="totalStatsCounters">
        <div class="counterWidget" title="<?=tr('startPage_totalCachesDesc')?>">
          <div class="counterTitle"><?=tr('startPage_totalCaches')?></div>
          <div class="counterNumber"><?=$view->totalStats->totalCaches?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_readyToSearchDesc')?>">
          <div class="counterTitle"><?=tr('startPage_readyToSearch')?></div>
          <div class="counterNumber"><?=$view->totalStats->activeCaches?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_topRatedCachesDesc')?>">
          <div class="counterTitle"><?=tr('startPage_topRatedCaches')?></div>
          <div class="counterNumber"><?=$view->totalStats->topRatedCaches?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_newCachesDesc')?>">
          <div class="counterTitle"><?=tr('startPage_newCaches')?></div>
          <div class="counterNumber"><?=$view->totalStats->latestCaches?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_activeCacheSetsDesc')?>">
          <div class="counterTitle"><?=tr('startPage_activeCacheSets')?></div>
          <div class="counterNumber"><?=$view->totalStats->activeCacheSets?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_totalUsersDesc')?>">
          <div class="counterTitle"><?=tr('startPage_totalUsers')?></div>
          <div class="counterNumber"><?=$view->totalStats->totalUsers?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_newUsersDesc')?>">
          <div class="counterTitle"><?=tr('startPage_newUsers')?></div>
          <div class="counterNumber"><?=$view->totalStats->newUsers?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_totalSearchesDesc')?>">
          <div class="counterTitle"><?=tr('startPage_totalSearches')?></div>
          <div class="counterNumber"><?=$view->totalStats->totalSearches?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_newSearchesDesc')?>">
          <div class="counterTitle"><?=tr('startPage_newSearches')?></div>
          <div class="counterNumber"><?=$view->totalStats->latestSearches?></div>
        </div>

        <div class="counterWidget" title="<?=tr('startPage_newoRecomDesc')?>">
          <div class="counterTitle"><?=tr('startPage_newoRecom')?></div>
          <div class="counterNumber"><?=$view->totalStats->latestRecomendations?></div>
        </div>
      </div>
    </div>
    <!-- /total Stats -->


    <div id="map">
      <?php $view->callChunk('staticMap', $view->staticMapModel); ?>
    </div>

    <script type="text/javascript">
        function showMarker(id) {
          $('#'+id).toggleClass('hovered');
        }

        function hideMarker(id) {
          $('#'+id).toggleClass('hovered');
        }
    </script>

    <!-- Newest caches -->
    <div id="newCachesList">
      <p class="content-title-noshade-size3">
        <?=tr('startPage_latestCachesList')?>
      </p>

      <ul class="startPageList">
        <?php foreach($view->latestCaches as $c){ ?>
          <li>
            <div>
              (<?=$c['date']?>)
              <span class="content-title-noshade"><?=$c['location']?></span>
            </div>
            <div>
              <a class="links highlite" href="<?=$c['link']?>"
                 onmouseover="showMarker('<?=$c['markerId']?>')"
                 onmouseout="hideMarker('<?=$c['markerId']?>')">

                <img src="<?=$c['icon']?>" class="icon16" alt="CacheIcon" title="">
                <?=$c['cacheName']?>

              </a>
              <?=tr('hidden_by')?>
              <a class="links" href="<?=$c['userUrl']?>"><?=$c['userName']?></a>
            </div>
          </li>
        <?php } //foreach ?>

          <li class="showMoreLink">
            <a href="/newcaches.php" class="btn btn-sm">
              <?=tr('startPage_showMore')?>
            </a>
          </li>
       </ul>
    </div>
    <!-- /newest caches -->


    <!-- incomming events -->
    <div id="newCachesList">
      <p class="content-title-noshade-size3">
        <?=tr('incomming_events')?>
      </p>

      <ul class="startPageList">
        <?php foreach($view->incomingEvents as $c){ ?>
          <li>
            <div>
              (<?=$c['date']?>)
              <span class="content-title-noshade"><?=$c['location']?></span>
            </div>
            <div>
                <a class="links highlite" href="<?=$c['link']?>"
                   onmouseover="showMarker('<?=$c['markerId']?>')"
                   onmouseout="hideMarker('<?=$c['markerId']?>')">

                  <img src="<?=$c['icon']?>" class="icon16" alt="CacheIcon" title="">
                  <?=$c['cacheName']?>

                </a>
                <?=tr('hidden_by')?>
                <a class="links" href="<?=$c['userUrl']?>"><?=$c['userName']?></a>
            </div>
          </li>
        <?php } //foreach ?>

          <li class="showMoreLink">
            <a href="/newevents.php" class="btn btn-sm">
              <?=tr('startPage_showMore')?>
            </a>
          </li>
       </ul>
    </div>
    <!-- /incomming events -->


    <!-- titled caches -->
    <?php if($view->titledCacheData){ ?>
    <div id="cacheTitled">
      <p class="content-title-noshade-size3">
        <?=tr('startPage_latesttitledCaches')?>
      </p>
      <ul class="startPageList">
        <li>
          <div>
            (<?=$view->titledCacheData['date']?>)
            <span class="content-title-noshade">
              <?=$view->titledCacheData['cacheLocation']?>
            </span>
          </div>
          <div>
            <img src="<?=$view->titledCacheData['cacheIcon']?>" class="icon16" alt="Cache" title="Cache">
            <a href="<?=$view->titledCacheData['cacheUrl']?>" class="links highlite"
                 onmouseover="showMarker('<?=$view->titledCacheData['markerId']?>')"
                 onmouseout="hideMarker('<?=$view->titledCacheData['markerId']?>')">
              <?=$view->titledCacheData['cacheName']?>
            </a>
            <?=tr('hidden_by')?>
            <a href="<?=$view->titledCacheData['cacheOwnerUrl']?>" class="links">
              <?=$view->titledCacheData['cacheOwnerName']?>
            </a>
          </div>

          <div class="cacheTitledLog">
            <img src="images/rating-star.png" alt="Star">
              <a href="<?=$view->titledCacheData['logOwnerUrl']?>" class="links">
                <?=$view->titledCacheData['logOwnerName']?>
              </a>:<br><br>
                    <?=$view->titledCacheData['logText']?>
          </div>
        </li>
        <li class="showMoreLink">
          <a href="/cache_titled.php" class="btn btn-sm">
            <?=tr('startPage_showMore')?>
          </a>
        </li>
      </ul>
    </div>
    <?php } //if-titledCacheData ?>
    <!-- /titled caches -->



    <!-- last-cacheSets -->
    <?php if($view->displayLastCacheSets){ ?>
        <div id="newestCacheSets">
          <p class="content-title-noshade-size3">
            <?=tr('startPage_latestCacheSets')?>
          </p>
          <ul class="startPageList">
          <?php foreach($view->lastCacheSets AS $cs){ ?>
            <li>
              <div>
                (<?=$cs->getCreationDate(true)?>)
                <span class='content-title-noshade'>
                  <?=$cs->getLocation()->getDescription(' > ')?>
                </span>
              </div>
              <div>
                <a href="<?=$cs->getUrl()?>" class="links highlite"
                    onmouseover="showMarker('<?='cs_'.$cs->getId()?>')"
                    onmouseout="hideMarker('<?='cs_'.$cs->getId()?>')">
                  <img src="<?=$cs->getImage()?>" />
                  <?=$cs->getName()?>
                </a>
                <?=tr('hidden_by')?>
                lista autorów...
              </div>
            </li>
          <?php } //foreach-lastCacheSets ?>
            <li class="showMoreLink">
              <a href="/powerTrail.php" class="btn btn-sm">
                <?=tr('startPage_showMore')?>
              </a>
            </li>
          </ul>
        </div>
    <?php } // if-displayGeoPathOfTheDay) ?>
    <!-- /last-cacheSets -->


    <!-- feeds -->
    <?php foreach($view->feeds as $feedName => $feedPosts) { ?>
      <div id="feedArea">
        <p class="content-title-noshade-size3"><?=tr('feed_'.$feedName)?></p>
        <ul id="feedList">
          <?php foreach($feedPosts as $post){ ?>
              <li>
                <?=$post->date?>
                <a class="links" href="<?=$post->link?>">
                  <?=$post->title?>
                </a>
                (<?=$post->author?>)
              </li>
          <?php } //foreach-feedPosts ?>
        </ul>
      </div>
    <?php }//foreach-feeds ?>
    <!-- /feeds -->
</div>


<!-- /CONTENT -->
