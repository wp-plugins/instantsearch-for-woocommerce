<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   WCISPlugin
 * @author    InstantSearchPlus <support@instantsearchplus.com>
 * @license   GPL-2.0+
 * @link      http://www.instantsearchplus.com
 * @copyright 2014 InstantSearchPlus
 */
?>



<div class="wrap">
	<script>
try {
    document.body.style.overflow = "hidden";
    document.body.style.height = "100%";
    document.getElementById('wpwrap').style.height = "100%";


    document.getElementById('wpcontent').style.height = "100%";
    document.getElementById('wpbody').style.height = "100%";
    document.getElementById('wpbody-content').style.height = "100%";


    document.getElementsByClassName('wrap')[0].style.height="100%";
} catch (e) {}
</script>

<iframe width="100%" height="100%" style="height:90%;width:100%" src="<?php echo($wc_admin_url); ?>"Browser not compatible.</iframe>


</div>
