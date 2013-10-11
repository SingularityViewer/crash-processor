<?php

class SearchFilter
{
    public $os;
    public $chan;
    public $version;
    public $grid;
    public $stacktrace;
    public $sort_by;
    public $sort_order;
    public static $sort_keys = array("date", "os", "version", "grid");
    public static $sort_orders = array("asc", "desc");
    public $limit = 100;
    public $offset = 0;
    public $page = 0;
    
    function __construct()
    {
        if (in_array($_GET["sort_by"], self::$sort_keys))
        {
            $this->sort_by = $_GET["sort_by"];
        }

        if (in_array($_GET["sort_order"], self::$sort_orders))
        {
            $this->sort_by = $_GET["sort_order"];
        }
        
        if (strlen($_GET["os"]))
        {
            $this->os = $_GET["os"];
        }

        if (strlen($_GET["chan"]))
        {
            $this->chan = $_GET["chan"];
        }

        if (strlen($_GET["version"]))
        {
            $this->version = $_GET["version"];
        }

        if (strlen($_GET["page"]))
        {
            $this->page = $_GET["page"];
        }

        if (strlen($_GET["grid"]))
        {
            $this->grid = $_GET["grid"];
        }

        if (strlen($_GET["stacktrace"]))
        {
            $this->stacktrace = $_GET["stacktrace"];
        }
    }
    
    function getWhere()
    {
        $cond = array();
        if ($this->os) $cond[] = kl_str_sql("os_type=!s", $this->os);
        if ($this->version) $cond[] = kl_str_sql("client_version=!s", $this->version);
        if ($this->chan) $cond[] = kl_str_sql("client_channel=!s", $this->chan);
        if ($this->grid) $cond[] = kl_str_sql("grid=!s", $this->grid);
        
        if ($this->stacktrace)
        {
            $parts = preg_split("/\\s+/", trim($this->stacktrace));
            foreach($parts as $part)
            {
                $cond[] = kl_str_sql("raw_stacktrace like !s", "%{$part}%");
            }
        }
        
        if (!count($cond)) return "";
        return "where " . implode(" and ", $cond);
    }
    
    function getVersions()
    {
        $ret = array();
        $where = $this->chan ? kl_str_sql("where client_channel=!s", $this->chan) : '';
        $q = "select distinct client_version from reports $where order by client_version desc";
        if (false !== $cached = Memc::getq($q)) return $cached;
        
        if (!$res = DBH::$db->query($q))
        {
            return $ret;
        }
        
        while ($row = DBH::$db->fetchRow($res))
        {
            $ret[] = $row["client_version"];
        }
        
        Memc::setq($q, $ret);
        return $ret;
    }
    
    function getGrids()
    {
        $ret = array();
        $q = "select distinct grid from reports order by grid asc";
        if (false !== $cached = Memc::getq($q)) return $cached;
        
        if (!$res = DBH::$db->query($q))
        {
            return $ret;
        }
        
        while ($row = DBH::$db->fetchRow($res))
        {
            $ret[] = $row["grid"];
        }
        
        Memc::setq($q, $ret);
        return $ret;
    }

    function render()
    {
        $ver = $this->getVersions();
        $grids = $this->getGrids();
?>
<script>
  $(function() {
    $( ".radio" )
    .buttonset()
    .click(function() {
       $(this).closest("form").submit();
    });
  });

</script>

<form method="get">
<div class="ui-widget ui-corner-all ui-widget-content">
    <div class="ui-widget-header" style="padding: 5px">Filter</div>

    <div class="filterelem">
        <div class="radio">
            Channel<br />
            <input type="radio" id="chan1" name="chan" value="" <?php echo !$this->chan ? 'checked="checked"' : '' ?>/><label for="chan1">All</label>
            <input type="radio" id="chan2" name="chan" value="Singularity" <?php echo $this->chan == "Singularity" ? 'checked="checked"' : '' ?>/><label for="chan2">Singularity</label>
            <input type="radio" id="chan3" name="chan" value="SingularityAlpha" <?php echo $this->chan == "SingularityAlpha" ? 'checked="checked"' : '' ?>/><label for="chan3">SingularityAlpha</label>
        </div>
    </div>
    
    <div class="filterelem">
        Version<br/>
        <select class="ui-widget-content" name="version" onchange="this.form.submit();" style="width: 100px; margin-top: 4px;"> 
            <option value="" <?php echo !$this->version ? 'selected="selected"' : '' ?>>All</option>
<?php
for($i = 0; $i < count($ver); $i++)
{
    $sel = $this->version == $ver[$i] ? ' selected="selected"' : '';
    print '<option value="' . htmlentities($ver[$i]) . '"' . $sel . '>' . htmlentities($ver[$i]). '</option>';
}
?>
        </select>
    </div>

    <div class="filterelem">
        <div class="radio">
            Operating system<br />
            <input type="radio" id="os_all" name="os" value="" <?php echo !$this->os ? 'checked="checked"' : '' ?>/><label for="os_all">All</label>
            <input type="radio" id="os_windows" name="os" value="Windows NT" <?php echo $this->os == "Windows NT" ? 'checked="checked"' : '' ?>/><label for="os_windows">Windows</label>
            <input type="radio" id="os_linux" name="os" value="Linux" <?php echo $this->os == "Linux" ? 'checked="checked"' : '' ?>/><label for="os_linux">Linux</label>
            <input type="radio" id="os_mac" name="os" value="Mac OS X" <?php echo $this->os == "Mac OS X" ? 'checked="checked"' : '' ?> /><label for="os_mac">Mac</label>
        </div>
    </div>

    <div class="filterelem">
        Grid<br/>
        <select class="ui-widget-content" name="grid" onchange="this.form.submit();" style="width: 200px; margin-top: 4px;"> 
            <option value="" <?php echo !$this->grid ? 'selected="selected"' : '' ?>>All</option>
<?php
for($i = 0; $i < count($grids); $i++)
{
    $sel = $this->grid == $grids[$i] ? ' selected="selected"' : '';
    print '<option value="' . htmlentities($grids[$i]) . '"' . $sel . '>' . htmlentities($grids[$i]). '</option>';
}
?>
        </select>
    </div>

    <div class="filterelem">
        Stacktrace contains<br/>
        <input class="ui-widget-content ui-button" type="text" name="stacktrace" value="<?php echo htmlentities($this->stacktrace) ?>" style="text-align: left; padding-left: 4px;" />
        <input class="ui-widget-content toolbarbutton" type="submit" name="do_search" value="Search" />
    </div>
</div>
</form>
        
<?php
    }
    
}