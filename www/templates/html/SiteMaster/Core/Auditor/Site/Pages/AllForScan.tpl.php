<?php
$scan           = $context->getScan();
$site           = $scan->getSite();
$site_pass_fail = \SiteMaster\Core\Config::get('SITE_PASS_FAIL');
?>

<div class="pages info-section">
    <header>
        <h3>Pages</h3>
        <div class="subhead">
            This is a list of all pages that we found on your site.
        </div>
    </header>
    <table data-sortlist="[[0,0],[2,0]]">
        <thead>
        <tr>
            <th>Path</th>
            <th>Grade</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($context as $page) {
            ?>
            <tr>
                <td>
                    <a href="<?php echo $page->getURL()?>"><?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?></a>
                </td>
                <td>
                    <?php 
                    if ($site_pass_fail) {
                        echo $page->percent_grade . "% (" . $page->letter_grade . ")";
                    } else {
                        echo $page->letter_grade;
                    }
                    ?>
                </td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
