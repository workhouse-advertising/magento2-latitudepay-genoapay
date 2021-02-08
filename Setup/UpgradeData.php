<?php
/**
 * This file is part of the Klarna Kp module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Latitude\Payment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Cms\Model\BlockFactory;

class UpgradeData implements UpgradeDataInterface
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
     * setup latitudepay/genoapay  checkout Payment content
     *
     * @param ModuleDataSetupInterface $installer
     * @param ModuleContextInterface   $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->checkoutPaymentSection();
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->PdpInstallmentSection();
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->PdpInstallmentSectionUpdate();
        }

        $installer->endSetup();
    }

    /**
     * setup latitudepay/genoapay  checkout Payment content
     *
     */
    private function checkoutPaymentSection()
    {
        $model = $this->blockFactory->create();
        $cmsBlockData[] = [
            'title'      => 'Latitude Installment Block',
            'identifier' => 'latitude_installment_block',
            'content' => "<strong>10</strong>  interest free payments from <strong>%s</strong> with <span style=\"display: inline-block;border:2px solid #0a74ff;border-radius:4px;padding:12px;margin-right: 7px;\"><img width=\"180px\"  src=\"{{view url='Latitude_Payment::images/latitude-pay-logo.svg'}}\"></span>",
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];
        $cmsBlockData[] = [
            'title'      => 'Genoapay installment block',
            'identifier' => 'genoapay_installment_block',
            'content' => "<span style=\"vertical-align: middle;\"> <strong>10</strong> interest free payments from <strong>%s</strong> with </span><span style=\"display: inline-block; border: 2px solid #00AB8E; border-radius: 4px; padding: 12px; margin-right: 7px;\"><img src=\"{{view url='Latitude_Payment::images/genoapay_logo.svg'}}\" width=\"180px\"></span>
             <p>Available to NZ residents who are 18 yrs old and over and have a valid debit card or credit card.</p>",
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];
        $cmsBlockData[] = [
            'title'      => 'Product Installment Block',
            'identifier' => 'latitude_product_block',
            'content' => "<span >Starting interest free at <strong>%1 </strong> for <strong>10</strong> payments</span>",
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];
        foreach ($cmsBlockData as $data) {
            $model->setData($data);
            $this->saveBlock($model);
        }
    }

    private function PdpInstallmentSection()
    {
        $blockId =  $this->blockFactory->create()->load(
            'latitude_product_block',
            'identifier'
        );
        $data = "<span ><strong>10</strong> interest free payments starting from <strong>%1 </strong> with</span>";
        $blockId->setContent($data);
        $this->saveBlock($blockId);
    }

    private function PdpInstallmentSectionUpdate()
    {
        $blockId =  $this->blockFactory->create()->load(
            'latitude_product_block',
            'identifier'
        );
        $data = "<span ><strong>10</strong>  weekly payments of <strong>%1 </strong> <span class='text-learnmore'>learn more</span></span>";
        $blockId->setContent($data);
        $this->saveBlock($blockId);
    }

        private function saveBlock($model)
    {
        $model->save();
    }

}
