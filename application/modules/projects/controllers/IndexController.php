<?php

class Projects_IndexController extends Zend_Controller_Action
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
            $thisClient = $this->institutionMapper->findById($thisProject->getClient());

            $obj = new C3op_Projects_AreaActivityTypes();
            $areaActivity = $obj->TitleForType($thisProject->getAreaActivity());

            $obj = new C3op_Projects_ProjectStatusTypes();
            $status = $obj->TitleForType($thisProject->getStatus());

            $actionsCount = count($this->projectMapper->GetAllActions($thisProject));

            $projectsList[$id] = array(
                'projectName' => $thisProject->GetTitle(),
                'clientName' => $thisClient->GetShortName(),
                'areaActivity' => $areaActivity,
                'status' => $status,
                'physicalProgress' => '[#12%]',
                'payedPercentage' => '[#10%]',
                'receivedPercentage' => '[#11%]',


                'beginDate' => C3op_Util_DateDisplay::FormatDateToShow($thisProject->GetBeginDate()),
                'value' => C3op_Util_CurrencyDisplay::FormatCurrency($thisProject->GetValue()),
                'linkActionCreate' => '/projects/action/create/?project=' . $id,
                'linkProjectDetail' => '/projects/project/detail/?id=' . $id,
                'linkReceivableCreate' => '/projects/receivable/create/?project=' . $id,
                'linkProjectReceivables' => '/projects/project/receivables/?id=' . $id,
                'actionsCount' => $actionsCount,
            );
        }


        $pageData = array(
                'projectsList' => $projectsList
            );

        $this->view->pageData = $pageData;
        $this->view->projectsList = $projectsList;

        $this->view->createProjectLink = "/projects/project/create";


    }


}