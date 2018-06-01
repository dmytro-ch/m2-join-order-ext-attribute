<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2018 Atwix (https://www.atwix.com/)
 * @package Atwix_JoinOrderExtAttribute
 */

namespace Atwix\JoinOrderExtAttribute\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryAddExtensionAttributePlugin
 */
class OrderRepositoryAddExtensionAttributePlugin
{
    /**
     * Customer Date of Birthday field name
     */
    const FIELD_NAME_CUSTOMER_DOB = 'dob';

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * Customer Repository
     *
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Customer Collection Factory
     *
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * OrderRepositoryAddExtensionAttributePlugin constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerCollectionFactory $customerCollectionFactory,
        OrderExtensionFactory $extensionFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Add Customer Date of Birthday extension attribute to order data object in order to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface
    {
        $customerId = (int) $order->getCustomerId();
        $customerDob = $this->getCustomerDateOfBirthday($customerId);
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setCustomerDob($customerDob);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * Get Customer Date of Birthday by Entity ID
     *
     * @param int
     *
     * @return string|null
     */
    protected function getCustomerDateOfBirthday(int $customerId): ?string
    {
        /** @var CustomerCollection $customerCollection */
        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->addFilter('entity_id', $customerId);
        $customer = $customerCollection->getFirstItem();
        $incrementId = $customer->getData(self::FIELD_NAME_CUSTOMER_DOB);

        return $incrementId;
    }
}
