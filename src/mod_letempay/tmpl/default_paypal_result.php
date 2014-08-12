<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// no direct access
defined('_JEXEC') or die; ?>

<script>

    alert("<?php echo JText::_($result_message); ?>"); // 

    // add relevant message above or remove the line if not required
    window.onload = function(){

        if(window.opener){

            var opener = window.opener;

            // Reload parent window
            opener.location.href = opener.location.href;

            if (opener.progressWindow) {
                opener.progressWindow.close();
            }

            window.close();

        } else {

            if(top.dg.isOpen() == true){
                top.dg.closeFlow();
                return true;
            }
        }
    };
</script>