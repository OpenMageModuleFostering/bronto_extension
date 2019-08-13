<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Connector/Discovery/Script.php
 */

class Brontosoftware_Magento_Connector_Discovery_Script
{
    private $_jobs = array();
    private $_registration;

    /**
     * @param Brontosoftware_Magento_Connector_RegistrationInterface $registration
     */
    public function __construct(Brontosoftware_Magento_Connector_RegistrationInterface $registration)
    {
        $this->_registration = $registration;
    }

    /**
     * Adds job info to launch in the Middleware
     *
     * @param array $job
     * @return $this
     */
    public function addJobInfo(array $job)
    {
        $this->_jobs[] = $job;
        return $this;
    }

    /**
     * Add a scheduled task definition to send to the Middleware
     *
     * @param string $jobName
     * @param array $data
     * @return $this
     */
    public function addScheduledTask($jobName, $data = array())
    {
        return $this->addJobInfo(array(
            'id' => 'event',
            'extensionId' => 'advanced',
            'scopeId' => $this->_registration->getScopeHash(),
            'data' => array_merge(array( 'jobName' => $jobName ), $data)
        ));
    }

    /**
     * Gets all of the jobs designated as queue flushers
     *
     * @return array
     */
    public function getJobs()
    {
        return $this->_jobs;
    }

    /**
     * Gets the registration associated with this script
     *
     * @return Brontosoftware_Magento_Connector_RegistrationInterface
     */
    public function getRegistration()
    {
        return $this->_registration;
    }
}
