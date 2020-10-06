<?php

namespace Latitude\Payment\Setup;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Tryzens\CmsInstall\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * InstallData constructor.
     * @param BlockFactory $blockFactory
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }


    /**
     * Install Latitude/Genoapay popup content
     *
     * {@inheritDoc}
     *
     * MEQP2 Warning: $context necessary for interface
     *
     * @see ModuleDataSetupInterface::install()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $context->getVersion();
        $this->upgradeVersionOneZeroOne();
        $setup->endSetup();
    }

    /**
     * Install data
     *
     */
    private function upgradeVersionOneZeroOne()
    {
        $model = $this->blockFactory->create();
        $cmsBlockData[] = [
            'title' => 'Genoapay Popup content',
            'identifier' => 'genoapay-popup-content',
            'content' => "<div class=\"g-infomodal-content\">   
           <button class=\"action-close\" data-role=\"closeBtn\" type=\"button\">
                <span>Close</span>
            </button>
            <div class=\"g-modal-header\">
                <img class=\"g-infomodal-logo\" src=\"{{view url='Latitude_Payment::images/genoapay_logo_white.svg'}}\">
                <span>Pay over 10 weeks.<br>No interest, no fees.</span>
            </div>
            <div class=\"g-infomodal-body\">
                <div class=\"g-infomodal-card-group\">
                    <div class=\"g-infomodal-card\">
                        <div class=\"g-infomodal-card-content\">
                            <img src=\"{{view url='Latitude_Payment::images/shopping_trolly_icon.svg'}}\">
                        </div>
                        <div class=\"g-infomodal-card-footer\">
                            <div class=\"g-infomodal-card-title\"><span>Checkout with </span><span>Genoapay</span></div>
                        </div>
                    </div>
                    <div class=\"g-infomodal-card\">
                        <div class=\"g-infomodal-card-content\">
                            <img src=\"{{view url='Latitude_Payment::images/thin_tick_icon.svg'}} \">
                        </div>
                        <div class=\"g-infomodal-card-footer\">
                            <div class=\"g-infomodal-card-title\"><span>Credit approval </span><span>in seconds</span></div>
                        </div>
                    </div>
                    <div class=\"g-infomodal-card\">
                        <div class=\"g-infomodal-card-content\">
                            <img src=\"{{view url='Latitude_Payment::images/get_it_now_icon.svg'}}\">
                        </div>
                        <div class=\"g-infomodal-card-footer\">
                            <div class=\"g-infomodal-card-title\"><span>Get it now, </span><span>pay over 10 weeks</span></div>
                        </div>
                    </div>
                </div>
                    <p>That's it! We manage automatic weekly payments until you're paid off. Full purchase details can be viewed anytime online.</p>
                    <hr>
                    <p>You will need</p>
                <ul class=\"g-infomodal-list\">
                    <li>To be over 18 years old</li>
                    <li>Visa/Mastercard payment</li>
                    <li>NZ drivers licence or passport</li>
                    <li>First instalment paid today</li>
                </ul>
                <div class=\"g-infomodal-terms\">Learn more about <a href=\"https://www.genoapay.com/how-it-works/\" target=\"_blank\">how it works</a>. Credit criteria applies. Weekly payments will be automatically deducted. Failed instalments incur a $10 charge. See our <a href=\"https://www.genoapay.com/terms-and-conditions/\" target=\"_blank\">Terms & Conditions</a> for more information.</div>
            </div>       
           </div>
          ",
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];

        $cmsBlockData[] = [
            'title' => 'Latitudepay Popup Content',
            'identifier' => 'latitudepay-popup-content',
            'content' => "
                <div class=\"lp-content\">
        <div class=\"lp-header\">
             <button class=\"action-close\" data-role=\"closeBtn\" type=\"button\">
                <span>Close</span>
            </button>
            <img src=\"{{view url='Latitude_Payment::images/lpay_modal_logo.png'}}\" class=\"lp-logo\">
        </div>
        <div class=\"lp-body\">
            <div class=\"lp-heading lp-block\">
                <div>How does this work?</div>
                <div class=\"lp-bold\">Glad you asked!</div>
            </div>
            <ul class=\"lp-steps lp-block\">
                <li>
                    <img src=\"{{view url='Latitude_Payment::images/lp_phone.png'}}\">
                    <div class=\"lp-subheading\">Choose LatitudePay
                        <br class=\"lp-line-break\"> at the checkout</div>
                    <span>There's no extra cost to you - just select it as your<br class=\"lp-line-break\"> payment option.</span>
                </li>
                <li>
                    <img src=\"{{view url='Latitude_Payment::images/lp_timer.png'}}\">
                    <div class=\"lp-subheading\">Approval in
                        <br class=\"lp-line-break\"> minutes</div>
                    <span>Set up your account and we'll tell you straight away<br class=\"lp-line-break\"> if approved.</span>
                </li>
                <li>
                    <img src=\"{{view url='Latitude_Payment::images/lp_calender.png'}}\">
                    <div class=\"lp-subheading\">Get it now, pay
                        <br class=\"lp-line-break\"> over 10 weeks</div>
                    <span>It's the today way to pay, just 10 easy payments.<br class=\"lp-line-break\"> No interest. Ever.</span>
                </li>
            </ul>
        </div>
        <div class=\"lp-requirements lp-block\">
            <div class=\"lp-subheading\">If you're new to LatitudePay, you'll need this stuff:</div>
            <ul class=\"lp-requirements-list\">
                <li>Be over 18 years old</li>
                <li>An Australian driver’s licence or passport
                </li>
                <li>A couple of minutes to sign up,
                    <br class=\"lp-line-break\"> it’s quick and easy
                </li>
                <li>A credit/debit card (Visa or Mastercard)</li>
            </ul>
        </div>
        <div class=\"lp-footer lp-block\">
            Subject to approval. Conditions and late fees apply. Payment Plan provided by LatitudePay Australia Pty Ltd ABN 23 633 528 873. For our complete terms visit <a href=\"https://latitudepay.com/terms\" target=\"_blank\">latitudepay.com/terms</a>
        </div>
    </div>",
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];

        foreach ($cmsBlockData as $data) {
            $model->setData($data);
            $this->saveBlock($model);
        }
    }

    /**
     * saveBlock
     *@param BlockFactory $model
     *@return void
     */
    private function saveBlock($model)
    {
        $model->save();
    }
}