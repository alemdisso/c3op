<?php

class C3op_Projects_ActionRelatedProduct {

    private $action;
    private $actionMapper;
    private $actionsAbove;


    public function __construct(C3op_Projects_Action $action, C3op_Projects_ActionMapper $actionMapper)
    {
        $this->action = $action;
        $this->actionMapper = $actionMapper;
        $above = new C3op_Projects_ActionsAbove($action, $actionMapper);
        $this->actionsAbove = $above->retrieve();

    }

    public function retrieve()
    {
        $actionsAbove = $this->actionsAbove;
        if (is_array($actionsAbove)) {
            $howManyLevels = count($actionsAbove);
            if ($howManyLevels > 0) {
                $product = $this->actionMapper->findById($actionsAbove[$howManyLevels - 1]);
                return $product;
            } else {
                return $this->action;
            }

        } else {
            throw new C3op_Projects_ActionException('something wrong ocurred');
        }


    }

    public function fetchProductData()
    {

        $productRelated = $this->retrieve();
        $relatedProductId = $productRelated->getId();
        $relatedProductTitle = $productRelated->getTitle();
        $dateFinder = new C3op_Finances_ProductDeliveryDate($productRelated, $this->actionMapper);
        $productDeliveryDate = $dateFinder->retrieve();
        $validator = new C3op_Util_ValidDate();
        if ($validator->isValid($productDeliveryDate)) {
            $productDeliveryDate = C3op_Util_DateDisplay::FormatDateToShow($productDeliveryDate);
        } else {
            $productDeliveryDate = "#(not defined)";
        }

        $data = array();
        if ($productRelated == $this->action) {
            $data['notAProduct'] = false;
            $data['relatedProductTitle'] = $productRelated->getTitle();;
            $data['productDeliveryDate'] = $productDeliveryDate;
            $data['relatedProductId'] = "0";

        } else {
            $data['notAProduct'] = true;
            $data['relatedProductTitle'] = $relatedProductTitle;
            $data['productDeliveryDate'] = $productDeliveryDate;
            $data['relatedProductId'] = $productRelated->getId();

        }

        return $data;




    }



    private function getActionsAbove(C3op_Projects_Action $action)
    {
        if ($action->getSubordinatedTo() > 0) {
            $this->actionsArray[] = $action->getSubordinatedTo();
            $parentAction = $this->actionMapper->findById($action->getSubordinatedTo());
            $this->getActionsAbove($parentAction);
        }
    }



}


