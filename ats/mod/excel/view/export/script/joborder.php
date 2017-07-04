<?php 

$cc = E::controller('cats');
//->getAlignment()->setIndent(1);
$exc->getActiveSheet()->getStyle('D1:D200')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$exc->getActiveSheet()->getStyle('D1:D200')->getAlignment()->setIndent(1);

$jo = $cc->getJobOrder($id );
$jop = $cc->getJobOrderPipelines($id);
$cdata = $cc->getCompany($jo['companyId']);
$rdata = $cc->getUser($jo['rcUserId']);
$odata = $cc->getUser($jo['mgUserId']);
$cudata = $cc->getUser($cc->getUserId());

$allData['user']=$odata;
$allData['currentUser']=$cudata;
$allData['recruiter']=$rdata;
$allData['company']=$cdata;
$allData['jobOrder']=$jo;
$allData['jobOrderPipelines']=$jop;
