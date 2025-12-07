<?php

namespace App\Services;

use App\Constants\AppConst;
use App\Constants\Role;

class AppConstantService
{
    public function getConstants()
    {
        return [
            'ROLE_LIST' => Role::ROLE_MAP,
            'USER_APPROVE_STATUS' => AppConst::USER_APPROVE_STATUS,
            'ONLINE_STATUS' => AppConst::ONLINE_STATUS,
            'ACTIVE_STATUS' => AppConst::ACTIVE_STATUS,
            'PHONE_STATUS' => AppConst::PHONE_STATUS,
            'CONSULTATION_MEMO_STATUS' => AppConst::CONSULTATION_MEMO_STATUS,
            'CONTACT_STATUS' => AppConst::CONTACT_STATUS,
            'LEAD_STATUS' => AppConst::LEAD_STATUS,
            'DIVISIONS' => AppConst::DIVISIONS,
            'POSITIONS' => AppConst::POSITIONS,
            'INDUSTRIES' => AppConst::INDUSTRIES,
            'EMPLOYEE_SIZES' => AppConst::EMPLOYEE_SIZES,
            'HOW_FOUND_US_LIST' => AppConst::HOW_FOUND_US_LIST,
            'REJECT_USER_REASONS' => AppConst::REJECT_USER_REASONS,
            'CONSULTATION_CONTACTS' => AppConst::CONSULTATION_CONTACTS,
            'REJECT_SERVICE_REASONS' => AppConst::REJECT_SERVICE_REASONS,
            'SERVICE_STATUS' => AppConst::SERVICE_STATUS,
            'DECISION_LEVELS' => AppConst::DECISION_LEVELS,
            'PREFECTURES' => AppConst::PREFECTURES,
            'COMPANY_ISSUES' => AppConst::COMPANY_ISSUES,
            'BUSINESS_SCOPES' => AppConst::BUSINESS_SCOPES,
            'POST_TYPE_DATA' => AppConst::POST_TYPE_DATA,
            'CONTACT_TYPE' => AppConst::CONTACT_TYPE,
            'SERVICE_COUNT_TYPE' => AppConst::SERVICE_COUNT_TYPE,
            "POINT_TRANSACTION_STATUS" => AppConst::POINT_TRANSACTION_STATUS,
            "POINT_EXCHANGE_METHOD" => AppConst::POINT_EXCHANGE_METHOD,
            "CONSULTATION_STATUS" => AppConst::CONSULTATION_STATUS,
            "CONSULTATION_TYPE" => AppConst::CONSULTATION_TYPE,
            "USER_STATUS" => AppConst::USER_STATUS,
            "ANALYTIC_PERIOD_FILTER" => AppConst::ANALYTIC_PERIOD_FILTER,
            "IMPRESSION_PERIOD_FILTER" => AppConst::IMPRESSION_PERIOD_FILTER,
            "CTR_PAGE_NAME" => AppConst::CTR_PAGE_NAME,
            "CTR_EVENT_TYPE" => AppConst::CTR_EVENT_TYPE
        ];
    }

}
