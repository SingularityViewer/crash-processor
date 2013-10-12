<?php

define("SITE_ROOT", realpath(dirname(__file__)));
require_once SITE_ROOT . "/lib/init.php";
$S->requireUser();

$filter = new SearchFilter();
$stats = new CrashStats($filter);

Layout::header();
$filter->render();
?>
<script>
    $(function() {
        $( "#tabs" ).tabs();
        //$("div.ui-tabs-panel").css('padding','0px');
    });
</script>
<br/>
<div id="tabs">
    <ul>
        <li><a href="#tab-1">Top Crashers</a></li>
        <li><a href="#tab-2">Graphics Cards</a></li>
        <li><a href="#tab-3">Operating Systems</a></li>
        <li><a href="#tab-4">Regions</a></li>
    </ul>


    <!-- top crashers tab -->
    <div id="tab-1">
    </div>
    <!-- /top crashers tab -->


    <!-- gpu tab -->
    <div id="tab-2">
    </div>
    <!-- /gpu tab -->


    <!-- os tab -->
    <div id="tab-3">
    </div>
    <!-- /os tab -->


    <!-- regions tab -->
    <div id="tab-4">
<?php
    function rl($r)
    {
        global $filter;
        return URL_ROOT . "/crashes.php?" . $filter->getURLArgs() . "&region=" . urlencode($r->region) . "&grid=" . urlencode($r->grid);
    }
    $regions = $stats->getRegionStats();
    $c = count($regions);
    if ($c) :
?>
        <table class="jtable">
            <tr>
                <th>Nr. reports</th>
                <th>Region</th>
                <th>Grid</th>
            </tr>
<?php foreach($regions as $r): ?>
            <tr class="rowhighlight">
                <td style="text-align: right"><a href="<?php echo rl($r) ?>"><?php echo htmlentities($r->nr) ?></a></td>
                <td><a href="<?php echo rl($r) ?>"><?php echo htmlentities($r->region) ?></a></td>
                <td><a href="<?php echo rl($r) ?>"><?php echo htmlentities($r->grid) ?></a></td>
            </tr>
<?php endforeach ?>
        </table>

<?php endif ?>
    </div>
    <!-- /regions tab -->
</div>

<?php
Layout::footer();


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
