<?php

class Finances_IndexController extends Zend_Controller_Action
{
    private $db;
    private $projectMapper;
    private $institutionMapper;

    public function init()
    {

        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);

        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                    ->addActionContext('modify', 'html')
                    ->initContext();

   }

    public function indexAction()
    {

        // projectsList
        //   * id =>
        //      projectName
        //      projectId
        //      clientName
        //      areaActivity
        //      status
        //      physicalProgress
        //      payedPercentage
        //      receivedPercentage

        $list = $this->projectMapper->getAllIds();
        $projectsList = array();
        reset ($list);
        $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
        foreach ($list as $id) {
            $thisProject = $this->projectMapper->findById($id);

            $clientName = $this->view->translate('#(not defined)');
            if ($thisProject->getClient() > 0) {
                $thisClient = $this->institutionMapper->findById($thisProject->getClient());
                $clientName = $thisClient->GetShortName();
            }

            $obj = new C3op_Projects_AreaActivityTypes();
            $areaActivity = $obj->TitleForType($thisProject->getAreaActivity());

            $obj = new C3op_Projects_ProjectStatusTypes();
            $status = $obj->TitleForType($thisProject->getStatus());

            $actionsCount = count($this->projectMapper->GetAllActions($thisProject));

            $contracts = $this->projectMapper->getAllContracts($thisProject);
            if (count($contracts)) {
                $hasContract = true;
            } else {
                $hasContract = false;
            }


            $projectsList[$id] = array(
                'projectName'        => $thisProject->GetShortTitle(),
                'clientName'         => $clientName,
                'areaActivity'       => $areaActivity,
                'status'             => $status,
                'physicalProgress'   => '[#12%]',
                'payedPercentage'    => '[#10%]',
                'receivedPercentage' => '[#11%]',
                'hasContract'       => $hasContract,
            );

            /* temp */

//            $doesIt = new C3op_Projects_ProjectSeemsToBeInExecution($thisProject);
//            if ($doesIt->seemsToBeInExecution()) {
//                $thisProject->setStatus(C3op_Projects_ProjectStatusConstants::STATUS_EXECUTION);
//                $this->projectMapper->update($thisProject);
//            } else {
//                $doesIt = new C3op_Projects_ProjectSeemsToBeFinished($thisProject);
//                if ($doesIt->seemsToBeFinished()) {
//                    $thisProject->setStatus(C3op_Projects_ProjectStatusConstants::STATUS_FINISHED);
//                    $this->projectMapper->update($thisProject);
//                } else {
//                    $thisProject->setStatus(C3op_Projects_ProjectStatusConstants::STATUS_PLANNING);
//                    $this->projectMapper->update($thisProject);
//                }
//            }
//
//            if ($hasContract) {
////                $projectsList[$id]['projectName'] = $thisProject->GetShortTitle() . "!!!";
//            } else {
//                $doesIt = new C3op_Projects_ProjectSeemsToBeContracted($thisProject);
//                if ($doesIt->seemsToBeContracted()) {
//                    $this->projectMapper->createContract($thisProject);
//                } else {
////                    $projectsList[$id]['projectName'] = $thisProject->GetShortTitle() . "!?!";
//                }
//            }



            }


        $pageData = array(
                'projectsList' => $projectsList
            );

        $this->view->pageData = $pageData;
        $this->view->projectsList = $projectsList;

        $this->view->createProjectLink = "/projects/project/create";


    }


}