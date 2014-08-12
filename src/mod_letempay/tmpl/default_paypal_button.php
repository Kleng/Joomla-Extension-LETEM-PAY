<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation'); ?>

<?php if($params->get('product_name', 0)): ?>

    <script src='https://www.paypalobjects.com/js/external/dg.js' type='text/javascript'></script>
    <script type="text/javascript">
        Joomla.submitbutton = function(task) {
            if (task == 'pay' || document.formvalidator.isValid(document.id('payment-form'))) {
                    var dg = new PAYPAL.apps.DGFlow({trigger: 'paypal_submit'});
                    /* https://developer.paypal.com/webapps/developer/docs/classic/express-checkout/digital-goods/IntroducingExpressCheckoutDG/ */
            } else {
                return false;
            }
        }
    </script>

    <?php
        $u = JFactory::getURI();
        $u->setVar('paypal_task','checkout');
    ?>

    <div class="paypal">
        <form action="<?php echo $u->toString(); ?>" method="post" name="paymentForm" id="payment-form" class="form-validate"> <!-- phpcheckout.php -->

            <?php if($params->get('show_product_name', 0)): ?>
                <h3>
                    <?php echo $params->get('product_name'); ?>
                </h3>
            <?php endif; ?>

            <?php
                $tcfile = $params->get('tcfile', 0);

                if(!$tcfile){
                    $tcfile = $component_params->get('default_tcfile', 0);
                }
            ?>

            <?php if($tcfile && $tcfile != -1): ?>
                <div class="terms_conditions">
                    <input type="checkbox" name="termsconditions" value="1" class="inputbox required">
                    <?php 
                        $link_to_tc = JHtml::_('link', JRoute::_('./images/'.$tcfile), JText::_('MOD_LETEMPAY_TC_LABEL'), array('target' => '_blank', 'rel' => "nofollow", 'class' => "tcfile"));
                        echo JText::sprintf('MOD_LETEMPAY_AGREE_TC_TEXT_BEFORE_LABEL', $link_to_tc);
                    ?>
                </div>
                <hr/>
            <?php endif; ?>

            <?php if(isset($product_price_view) && $product_price_view) :?>
                <span class="price">
                    <?php echo str_replace(".00", "", $product_price_view); ?>
                </span>
            <?php endif; ?>

            <input type="image" name="paypal_submit" id="paypal_submit" src="https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif" border="0" align="top" alt="Pay with PayPal" onclick="Joomla.submitbutton('pay');"/>
            <?php // https://www.paypal-community.com/t5/Merchant-services-Archive/Auto-Return-giving-different-POST-and-GET-data/td-p/50537 ?>

            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>

<?php else: ?>

    <?php echo JText::_('MOD_LETEMPAY_MISSING_PRODUCT_NAME_LABEL'); ?>

<?php endif; ?>