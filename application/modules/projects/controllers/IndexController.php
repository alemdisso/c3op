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


    public function activeAction() {

       $receivables1 = array();
       $receivables1[0] = array(
           'deliveryDate'     => '10/04/2013',
           'receivableValue'  => 'R$ 20.000,00',
           'differenceInDays' => '(2)',
           'productsList'     => array(
                    '1' => array(
                        'productName' => 'um produto',
                    ),
                    '2' => array(
                        'productName' => 'outro produto',
                    ),
                    '3' => array(
                        'productName' => 'mais um produto',
                    ),

                ),
           );

       $receivables1[1] = array(
           'deliveryDate'     => '06/04/2013',
           'receivableValue'  => 'R$ 120.000,00',
           'differenceInDays' => '(-2)',
           'productsList'     => array(
                    '6' => array(
                        'productName' => 'esse é outro',
                    ),
                    '8' => array(
                        'productName' => 'e mais um',
                    ),
                    '10' => array(
                        'productName' => 'camisa 10 da gávea',
                    ),

                ),
           );

       $receivables2 = array();
       $receivables2[0] = array(
           'deliveryDate'     => '12/12/2013',
           'receivableValue'  => 'R$ 22.000,00',
           'differenceInDays' => '(22)',
           'productsList'     => array(
                    '1' => array(
                        'productName' => 'um produto',
                    ),
                    '2' => array(
                        'productName' => 'outro produto',
                    ),
                    '3' => array(
                        'productName' => 'mais um produto',
                    ),

                ),
           );

       $receivables2[1] = array(
           'deliveryDate'     => '26/04/2013',
           'receivableValue'  => 'R$ 200.000,00',
           'differenceInDays' => '(-12)',
           'productsList'     => array(
                    '6' => array(
                        'productName' => 'esse é outro',
                    ),
                    '8' => array(
                        'productName' => 'e mais um',
                    ),
                    '10' => array(
                        'productName' => 'camisa 10 da gávea',
                    ),

                ),
           );



        $projectsList = array(
            '1' => array(
                'projectName'       => 'Nome do projeto',
                'clientName'       => 'Nome do cliente',
                'receivablesList' => $receivables1,

            ),
            '2' => array(
                'projectName'       => 'Outro projeto',
                'clientName'       => 'Outro cliente',
                'receivablesList' => $receivables2,

            ),

        );



        $this->view->pageData = array(
            'projectsList' => $projectsList,

        );


    }

    public function activeTreeAction() {

       $receivables1 = array();
       $receivables1[0] = array(
           'deliveryDate'     => '10/04/2013',
           'receivableValue'  => 'R$ 20.000,00',
           'differenceInDays' => '(2)',
           'productsList'     => array(
                    '1' => array(
                        'productName' => 'um produto',
                    ),
                    '2' => array(
                        'productName' => 'outro produto',
                    ),
                    '3' => array(
                        'productName' => 'mais um produto',
                    ),

                ),
           );

       $receivables1[1] = array(
           'deliveryDate'     => '06/04/2013',
           'receivableValue'  => 'R$ 120.000,00',
           'differenceInDays' => '(-2)',
           'productsList'     => array(
                    '6' => array(
                        'productName' => 'esse é outro',
                    ),
                    '8' => array(
                        'productName' => 'e mais um',
                    ),
                    '10' => array(
                        'productName' => 'camisa 10 da gávea',
                    ),

                ),
           );

       $receivables2 = array();
       $receivables2[0] = array(
           'deliveryDate'     => '12/12/2013',
           'receivableValue'  => 'R$ 22.000,00',
           'differenceInDays' => '(22)',
           'productsList'     => array(
                    '1' => array(
                        'productName' => 'um produto',
                    ),
                    '2' => array(
                        'productName' => 'outro produto',
                    ),
                    '3' => array(
                        'productName' => 'mais um produto',
                    ),

                ),
           );

       $receivables2[1] = array(
           'deliveryDate'     => '26/04/2013',
           'receivableValue'  => 'R$ 200.000,00',
           'differenceInDays' => '(-12)',
           'productsList'     => array(
                    '6' => array(
                        'productName' => 'esse é outro',
                    ),
                    '8' => array(
                        'productName' => 'e mais um',
                    ),
                    '10' => array(
                        'productName' => 'camisa 10 da gávea',
                    ),

                ),
           );



        $projectsList = array(
            '1' => array(
                'projectName'       => 'Nome do projeto',
                'clientName'       => 'Nome do cliente',
                'receivablesList' => $receivables1,

            ),
            '2' => array(
                'projectName'       => 'Outro projeto',
                'clientName'       => 'Outro cliente',
                'receivablesList' => $receivables2,

            ),

        );



        $this->view->pageData = array(
            'projectsList' => $projectsList,

        );


    }

    private function fillProjectsData() {

        $projects = $this->projectMapper->getAllActiveProjects();
        $data = array();

        foreach ($projects as $projectId) {
            $thisProject = $this->projectMapper->findById($projectId);

            $clientName = $this->view->translate('#(not defined)');
            if ($thisProject->getClient() > 0) {
                $thisClient = $this->institutionMapper->findById($thisProject->getClient());
                $clientName = $thisClient->GetShortName();
            }


            $projectReceivables = $this->receivableMapper->getAllReceivables($thisProject);
            $receivablesData = array();
            foreach ($projectReceivables as $receivableId) {

                $theReceivable = $this->receivableMapper->findById($receivableId);
                $receivableTitle = $theReceivable->getTitle();


                $validator = new C3op_Util_ValidDate();
                if ($validator->isValid($theReceivable->getDeliveryDate())) {
                    $deliveryDate = C3op_Util_DateDisplay::FormatDateToShow($theReceivable->getDeliveryDate());
                } else {
                    $deliveryDate = null;
                }


                $currencyDisplay = new  C3op_Util_CurrencyDisplay();

                $predictedValue = $currencyDisplay->FormatCurrency($theReceivable->getPredictedValue());
                $realValue = $currencyDisplay->FormatCurrency($theReceivable->getRealValue());

                

                $receivableData = array(
                    'deliveryDate'     => $deliveryDate,
                    'receivableValue'  => $predictedValue,
                    'differenceInDays' => '(-12)',

                );
                $receivablesData[$receivableId] = $receivableData;


            }
            $projectData = array(
                'projectName'       => $thisProject->getTitle(),
                'clientName'       => $clientName,
                'receivablesList' => $receivablesData,

            );

            $data[$projectId] = $projectData;

        }


    }



}