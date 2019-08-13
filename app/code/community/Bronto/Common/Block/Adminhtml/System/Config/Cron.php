<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Block_Adminhtml_System_Config_Cron extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Job code
     * @var string
     */
    protected $_jobCode;

    /**
     * Button widgets
     * @var array
     */
    protected $_buttons = array();

    /**
     * Progress bar
     * @var boolean
     */
    protected $_hasProgressBar = false;

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('bronto/common/cron.phtml');
    }

    /**
     * Prepare the layout
     *
     * @return Bronto_Common_Block_Adminhtml_System_Config_Cron
     */
    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addCss('bronto/cron.css');
        }

        return parent::_prepareLayout();
    }

    /**
     * Render the block
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    /**
     * Get a job schedule collection
     *
     * @return Mage_Cron_Model_Mysql4_Schedule_Collection
     */
    public function getJobSchedule()
    {
        return Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('job_code', $this->_jobCode)
            ->setPageSize(6)
            ->setCurPage(1)
            ->setOrder('scheduled_at', 'DESC');
    }

    /**
     * Get cron job message
     * Note: Limits to 100 characters
     *
     * @param Mage_Cron_Model_Schedule $job
     * @return string
     */
    public function getTruncatedJobMessages($job)
    {
        return Mage::helper('core/string')->truncate($job->getMessages(), 100);
    }

    /**
     * Get the HTML markup for the button widgets
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        $html = null;
        if ($buttons = $this->getButtons()) {
            foreach ($buttons as $_button) {
                $html .= $_button->toHtml();
            }
        }

        if (!empty($html)) {
            $html = "<p class=\"form-buttons\">{$html}</p>";
        }

        return $html;
    }

    /**
     * Get the HTML markup for the progress bar
     *
     * @return string
     */
    public function getProgressBarHtml()
    {
        $percent  = 0;
        $pending  = (int) $this->getProgressBarPending();
        $total    = (int) $this->getProgressBarTotal();

        $complete = $total - $pending;
        if ($complete > 0) {
            $percent = round(($complete / $total) * 100);
        }

        $message = "{$percent}% ({$complete}/{$total})";
        $html    = "<div class=\"bronto-progress-bar\"><div style=\"width: {$percent}%\">";
        if ($percent < 25) {
            $html .= "</div>{$message}";
        } else {
            $html .= "{$message}</div>";
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Color code the job status
     *
     * @param string $status
     * @return string
     */
    public function decorateJobStatus($status)
    {
        switch ($status) {
            case Mage_Cron_Model_Schedule::STATUS_SUCCESS:
                $color = 'green';
                break;
            case Mage_Cron_Model_Schedule::STATUS_RUNNING:
                $color = 'yellow';
                break;
            case Mage_Cron_Model_Schedule::STATUS_MISSED:
                $color = 'orange';
                break;
            case Mage_Cron_Model_Schedule::STATUS_ERROR:
                $color = 'red';
                break;
            case Mage_Cron_Model_Schedule::STATUS_PENDING:
            default:
                $color = 'lightgray';
                break;
        }

        return "<span class=\"bar-{$color}\"><span>{$status}</span></span>";
    }

    /**
     * Add button widget
     *
     * @param Mage_Adminhtml_Block_Widget_Button               $button
     * @return Bronto_Common_Block_Adminhtml_System_Config_Cron
     */
    public function addButton(Mage_Adminhtml_Block_Widget_Button $button)
    {
        $this->_buttons[] = $button;
        return $this;
    }

    /**
     * Get button widgets
     *
     * @return array
     */
    public function getButtons()
    {
        return $this->_buttons;
    }

    /**
     * Set if we're using a progress bar
     *
     * @param bool                                             $hasProgressBar
     * @return Bronto_Common_Block_Adminhtml_System_Config_Cron
     */
    public function setHasProgressBar($hasProgressBar)
    {
        $this->_hasProgressBar = $hasProgressBar;
        return $this;
    }

    /**
     * Get if we have a progress bar
     *
     * @return boolean
     */
    public function hasProgressBar()
    {
        return (bool) $this->_hasProgressBar;
    }

    /**
     * @return int
     */
    protected function getProgressBarTotal()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getProgressBarPending()
    {
        return 0;
    }
}
