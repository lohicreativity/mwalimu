<?php

return [
   'SITE_NAME'=>'Mwalimu Nyerere Memorial Academy',

   'SITE_SHORT_NAME'=>'MNMA',

   'SITE_DOMAIN'=>'mnma.ac.tz',

   'SITE_WEB_DOMAIN'=>'www.mnma.ac.tz',

   'SITE_URL'=>'https://mnma.ac.tz',

   'SITE_BASE_URL'=>'https://mnma.ac.tz',
   
   'VERSION'=>1.0,

    'GePG_SERVER' => 'http://154.118.230.202:80/',
    'POST_BILL_PATH' => 'api/bill/qrequest',
    'POST_SIGNED_BILL_PATH'=>'api/bill/sigqrequest',
    'CANCEL_BILL_PATH' => 'api/bill/sigcancel_request',
    'RECONCILE_PATH' => 'api/reconciliations/sp_qrequest',
    'SIGNED_RECONCILE_PATH' => 'api/reconciliations/sig_sp_qrequest',
    'SPCODE' => 'SP412',
    'SPSYSID' => 'LMNMA002',
    'SUBSPCODE' => '1001',

    'SP_SERVER' => 'http://127.0.0.1:80/',
    'SP_BILL_PATH' => 'payment/bills',
    'SP_RECEIPT_PATH' => 'payment/receipt',
    'SP_RECON_PATH' => 'payment/reconciliation',



    'RETRY_INTERVAL' => 15000,
    'CN_DATA_TAG' => 'gepgBillSubResp',
    'RECPT_DATA_TAG' => 'gepgPmtSpInfo',
    'CANCEL_DATA_TAG' => 'gepgBillCanclResp',
    'RECON_DATA_TAG' => 'gepgSpReconcResp',
    'SIGN_TAG' => 'gepgSignature',
    'PUBLIC_CERT_PASSWORD' => 'S3R1KAL1',
    'CERT_PASSWORD' => 'MnmaPassword',

    'TCU_USERNAME' => 'MN',
    'TCU_TOKEN' => 'M6RmtwhsWbVTGycZHxPL',

    'NACTE_API_KEY' => 'WXbac1b41cfa665a.e1ddf713e28c51d83a9131e4382d9624e737652bd5c6ff8db581c3afc97af0fe.2be03678590fe7eb0863c02d301c61b19a1795ee',

    'NECTA_API_KEY' => 'MjAxNzA4MDQxMDA1MzRPOUVFYlZFLkRHLi5keGhtLnZtc1RDLiR2TjJUc2x5TWJtcDhUQlQuR3VlRVkwJDBNUy51NlRURCR4czVuNVlkdllsRXBtRVRqR2ZPVUNuMm5DTnVoMENTVlR5T2xFc2pUcy5RSjIuVEoyLi5zcFhOLkREJHZKd1hBeW5BbHVwaDJjRUpFLi54VWtlVE91dVhtMnhKJCQ1QWhlREpBcHZUVjZUQzUyNTJZVDJWNlVsT2NlJFU=',

];