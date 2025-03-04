<?php

namespace Modules\Refund\Services;
use Modules\Refund\Repositories\RefundRepository;

class RefundService
{
    protected $refundRepository;
    public function __construct(RefundRepository $refundRepository){
        $this->refundRepository = $refundRepository;
    }
    public function getRequestAll()
    {
        return $this->refundRepository->getRequestAll();
    }
    public function getRequestSeller()
    {
        return $this->refundRepository->getRequestSeller();
    }
    public function getRequestForCustomer()
    {
        return $this->refundRepository->getRequestForCustomer();
    }
    public function findByID($id)
    {
        return $this->refundRepository->findByID($id);
    }
    public function findDetailByID($id)
    {
        return $this->refundRepository->findDetailByID($id);
    }
    public function store($data, $user)
    {
        return $this->refundRepository->store($data, $user);
    }

    public function appStore($data, $user)
    {
        return $this->refundRepository->appStore($data, $user);
    }

    public function updateRefundRequestByAdmin($data, $id)
    {
        return $this->refundRepository->updateRefundRequestByAdmin($data, $id);
    }
    public function updateRefundStateBySeller($data, $id)
    {
        return $this->refundRepository->updateRefundStateBySeller($data, $id);
    }
    public function getActiveShippingMethod()
    {
        return $this->refundRepository->getActiveShippingRate();
    }
    public function getRefundCommision($id){
        return $this->refundRepository->getRefundCommision($id);
    }
}
