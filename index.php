<?php

class Company
{
    protected $companyListAPI = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';

    public function getCompanyList() {
        $companyListData = json_decode(file_get_contents($this->companyListAPI), true);

        return $companyListData;
    }
}

class Travel
{
    protected $travelListAPI = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';

    public function getTravelList() {
        $travelListData = json_decode(file_get_contents($this->travelListAPI), true);

        return $travelListData;
    }
}

class Cost
{
    public function calculateTravelCost($companyList=[], $travelList=[]) {
        $companyCost = [];

        //Get price in travels api and calculate cost for company
        foreach ($travelList as $travel) {
            $companyId = isset($travel['companyId']) ? strval($travel['companyId']) : '';
            $price = isset($travel['price']) ? floatval($travel['price']) : 0;

            if (!isset($companyCost[$companyId])) {
                $companyCost[$companyId] = 0;
            }

            $companyCost[$companyId] += $price;
        }

        //Add 'cost' into company list
        foreach ($companyList as $key => $company) {
            $travelCost = 0;
            if (array_key_exists($company['id'], $companyCost)) {
                $travelCost =  $companyCost[$company['id']];

                $companyList[$key]['cost'] = $travelCost;
            }
        }
        
        return $this->getChildrenCompany($companyList);
    }

    public function getChildrenCompany($listCompany=[], $parentId='0') {
        $travelCostList = [];

        foreach ($listCompany as $item) {
            if (strval($item['parentId']) == strval($parentId)) {
                $children = $this->getChildrenCompany($listCompany, $item['id']);
                $item['children'] = [];

                if ($children) {
                    $item['children'] = $children;
                    $cost = $item['cost'];

                    foreach ($children as $chd) {
                        $cost += floatval($chd['cost']);
                    }
                    $item['cost'] = $cost;
                }

                array_push($travelCostList, $item);
            }
        }
        
        return $travelCostList;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);

        $company = new Company();
        $companyList = $company->getCompanyList();
        $travel = new Travel();
        $travelList = $travel->getTravelList();
        $travelCost = new Cost();
        $result = json_encode($travelCost->calculateTravelCost($companyList, $travelList));

        echo $result;
        echo 'Total time: '.  (microtime(true) - $start);
    }
}

(new TestScript())->execute();